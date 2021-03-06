<?php

namespace AmazonSellingPartnerAPI\Module;

use AmazonSellingPartnerAPI\AssumeRole;
use AmazonSellingPartnerAPI\Client;
use AmazonSellingPartnerAPI\Config\Config;
use AmazonSellingPartnerAPI\Contract\SignInterface;
use AmazonSellingPartnerAPI\Exception\ModuleException;
use AmazonSellingPartnerAPI\Exception\ThrottleException;
use AmazonSellingPartnerAPI\OAuth;
use AmazonSellingPartnerAPI\RateLimiter;
use AmazonSellingPartnerAPI\Validator;

class Requester
{
    /**
     * @var Object A cache driver which implements psr cache interface.
     */
    protected $cache;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $auth;

    /**
     * @var OAuth
     */
    protected $oAuth;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var AssumeRole
     */
    protected $assumeRole;

    /**
     * @var SignInterface
     */
    protected $signer;

    /**
     * @var array A request context
     * [
     *     uri:string Request uri
     *     query_params:array query params
     *     form_params:array form params
     *     name:string  Current operationId
     * ]
     */
    protected $context = [];

    protected $rateLimitCacheKey;

    protected $rateLimiter;

    protected $version = 'v0';

    /**
     * Requester constructor.
     * @param array $auth
     * [
     *     'client_id'         => 'required|string',
     *     'client_secret'     => 'required|string',
     *     'region'            => 'required|string',
     *     'role_arn'          => 'string',
     *     'refresh_token'     => 'required|string',
     *     'access_key'        => 'required|string',  Access Key of AWS IAM User, for example AKIAABCDJKEHFJDS
     *     'secret_key'        => 'required|string',  Secret Key of AWS IAM User
     * ]
     * @param $cache
     * @param SignInterface $signer
     * @throws ModuleException
     * @throws \AmazonSellingPartnerAPI\Exception\ClientException
     */
    public function __construct(array $auth, $cache, SignInterface $signer)
    {
        if (!isset($this->moduleName)) {
            throw new ModuleException('Undefined module name.');
        }
        $this->validator = new Validator();
        $this->cache     = $cache;
        $this->signer    = $signer;
        $this->setAuth($auth);
        $this->client = new Client($signer);
        $this->rateLimiter = new RateLimiter($cache);
    }

    /**
     * Set client auth info.
     *
     * @param $auth
     * @return $this
     * @throws ModuleException
     */
    public function setAuth($auth)
    {
        $res = $this->validator->validate([
            'client_id'        => 'required|string',
            'client_secret'    => 'required|string',
            'region'           => 'required|string',
            'role_arn'         => 'string',
            'refresh_token'    => 'required|string',
            'secret_key'       => 'required|string',
            'access_key'       => 'required|string',
        ], $auth);
        if ($res === false) {
            throw new ModuleException('Unauthorized: '.$this->validator->lastError());
        }
        $this->auth = $this->validator->validated();
        $this->oAuth = $this->getOAuth()
            ->setClientSecret($this->auth['client_secret'])
            ->setClientId($this->auth['client_id']);
        $this->assumeRole = $this->getAssumeRole()
            ->setRegion($this->auth['region'])
            ->setAccessKeyId($this->auth['access_key'])
            ->setSecretAccessKey($this->auth['secret_key']);

        return $this;
    }

    public function debug(bool $debug): self
    {
        $this->client->debug($debug);
        return $this;
    }

    public function withQuery(array $queryParams): self
    {
        $this->context['query_params'] = $queryParams;
        return $this;
    }

    public function withForm(array $formParams): self
    {
        $this->context['form_params'] = $formParams;
        return $this;
    }

    public function withBody(string $body): self
    {
        $this->context['body'] = $body;

        return $this;
    }

    /**
     * Call it before calling a operation.
     *
     * @param string $version
     * @return $this
     */
    public function setVersion(string $version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Sending a request.
     *
     * @return array|mixed
     * @throws ModuleException
     * @throws \AmazonSellingPartnerAPI\Exception\AmazonSellingPartnerAPIException
     * @throws \AmazonSellingPartnerAPI\Exception\ClientException
     * @throws \AmazonSellingPartnerAPI\Exception\OAuthException
     */
    public function send()
    {
        if (empty($this->auth)) {
            throw new ModuleException('Not auth info has set');
        }
        $this->auth['access_token'] = $this->getAccessToken();
        if (!empty($this->auth['role_arn'])) {
            $roleCredentials = $this->getRoleAssumeCredentials();
            $this->auth = array_merge($this->auth, [
                'access_key'        => $roleCredentials['AccessKeyId'],
                'secret_key'        => $roleCredentials['SecretAccessKey'],
                'session_token'     => $roleCredentials['SessionToken']
            ]);
        }
        $client = $this->client->setAuth($this->auth);
        $context = $this->context;
        if ($this->client->isDebug() === false && $this->attempt() === false) {
            throw new ThrottleException($this->rateLimiter->nextAttemptDuration($context['name']));
        }

        $res = $client->request(
            $this->configGet('method'),
            $context['uri'],
            $this->getFormParams(),
            $this->getQueryParams(),
            $context['body'] ?? ''
        );
        $this->cache->set($this->rateLimitCacheKey, $client->getRateLimit());

        return $res;
    }

    protected function getFormParams(): array
    {
        return $this->validate(
            $this->configGet('form_params', []),
            $this->context['form_params'] ?? []
        );
    }

    protected function getQueryParams(): array
    {
        $validated = $this->validate(
            $this->configGet('query_params', []),
            $this->context['query_params'] ?? []
        );
        foreach ($validated as $k => $v) {
            if (is_array($v)) {
                $validated[$k] = implode(',', $v);
            }
        }

        return $validated;
    }

    /**
     * Get client access_token
     *
     * @return string
     * @throws \AmazonSellingPartnerAPI\Exception\OAuthException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function getAccessToken(): string
    {
        $cacheKey = 'amazon_sp_api:access_token.'. md5($this->auth['refresh_token']);
        if ($accessToken = $this->cache->get($cacheKey)) {
            return $accessToken;
        }
        $res = $this->getOAuth()->getAccessToken($this->auth['refresh_token']);
        $accessToken = $res->access_token;
        $this->cache->set($cacheKey, $accessToken, $res->expires_in);

        return $accessToken;
    }

    /**
     * Get role assume credentials.
     *
     * @return array
     * @throws \AmazonSellingPartnerAPI\Exception\AmazonSellingPartnerAPIException
     */
    protected function getRoleAssumeCredentials(): array
    {
        $cacheKey = 'amazon_sp_api:role_assume_credentials.'. md5($this->auth['region'].$this->auth['access_key'].$this->auth['secret_key']);
        if ($credentials = $this->cache->get($cacheKey)) {
            return $credentials;
        }
        $credentials = $this->assumeRole->assume($this->auth['role_arn']);
        $this->cache->set($cacheKey, $credentials, $credentials['Expiration'] - time());

        return $credentials;
    }

    /**
     * Get OAuth object.
     *
     * @return OAuth
     */
    protected function getOAuth(): OAuth
    {
        if (is_null($this->oAuth)) {
            return new OAuth($this->auth['client_id'], $this->auth['client_secret']);
        }

        return $this->oAuth;
    }

    /**
     * Get assume role object.
     *
     * @return AssumeRole
     */
    protected function getAssumeRole(): AssumeRole
    {
        if (is_null($this->assumeRole)) {
            return new AssumeRole(
                $this->auth['region'],
                $this->auth['access_key'],
                $this->auth['secret_key'],
                $this->signer
            );
        }

        return $this->assumeRole;
    }

    /**
     * Validating an array of arguments and gets the validated data.
     *
     * @param $rules
     * @param $arguments
     * @return array
     * @throws ModuleException
     */
    protected function validate($rules, $arguments): array
    {
        if (empty($rules)) {
            return [];
        }
        $res = $this->validator->validate($rules, $arguments);
        if ($res === false) {
            throw new ModuleException('Validate error: '. $this->validator->lastError());
        }

        return $this->validator->validated();
    }

    /**
     * Resolve uri: /orders/v0/orders/{orderId} => /orders/v0/orders/123XXXXXX
     *
     * @param string $uri
     * @param array $pathParams
     * @return string
     * @throws ModuleException
     */
    protected function resolveUri(string $uri, array $pathParams): string
    {
        preg_match_all('/\{(\w+)\}/', $uri, $matched);
        if (!empty($matched[1])) {
            foreach ($matched[1] as $index => $key) {

                //optional params
                if (strpos($key, '?') !== false) {
                    if (empty($pathParams[$index])) {
                        $uri = str_replace("/{{$key}}", '', $uri);
                    }
                } elseif (empty($pathParams[$index])) {
                    throw new ModuleException("Missing params???{$key}");
                }
                $uri = str_replace("{{$key}}", $pathParams[$index], $uri);
            }
        }

        return $uri;
    }

    protected function configGet($name, $default = null)
    {
        return Config::get("{$this->moduleName}.{$this->version}.{$this->context['name']}.{$name}", $default);
    }

    protected function getRateLimit()
    {
        return $this->cache->get($this->rateLimitCacheKey) ? : $this->configGet('rate_limit.rate');
    }

    protected function attempt(): bool
    {
        $name = $this->context['name'];
        $this->rateLimiter->for(
            $name,
            $this->configGet('rate_limit.burst'),
            $this->getRateLimit()
        );

        return $this->rateLimiter->attempt($name);
    }

    /**
     * @param $name string operationId.
     * @param $pathParams array Path params.
     * @return $this
     * @throws ModuleException
     */
    public function __call($name, $pathParams): self
    {
        $this->context = [
            'name'   => $name,
        ];
        $this->context['uri'] = $this->resolveUri($this->configGet('path'), $pathParams);
        $this->rateLimitCacheKey = 'amazon_sp_api:rate_limit.'. md5($this->auth['refresh_token'].$name);

        return $this;
    }
}
