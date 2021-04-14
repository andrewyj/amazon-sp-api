<?php

namespace AmazonSellingPartnerAPI\Module;

use AmazonSellingPartnerAPI\AssumeRole;
use AmazonSellingPartnerAPI\Client;
use AmazonSellingPartnerAPI\Contract\SignInterface;
use AmazonSellingPartnerAPI\Exception\ModuleException;
use AmazonSellingPartnerAPI\OAuth;
use AmazonSellingPartnerAPI\Validator;
use Psr\SimpleCache\CacheInterface;

class Requester
{
    /**
     * @var CacheInterface
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
     * @var array
     */
    protected $config;

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
     * @param CacheInterface $cache
     * @param SignInterface $signer
     * @throws ModuleException
     * @throws \AmazonSellingPartnerAPI\Exception\ClientException
     */
    public function __construct(array $auth, CacheInterface $cache, SignInterface $signer)
    {
        $this->validator = new Validator();
        $this->cache     = $cache;
        $this->signer    = $signer;
        $this->setConfig();
        $this->setAuth($auth);
        $this->client = new Client($signer);
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

    /**
     * Set operationId config
     *
     * @throws ModuleException
     */
    protected function setConfig()
    {
        if (!isset($this->moduleName)) {
            throw new ModuleException('Module name undefined.');
        }
        $filePath = dirname(__DIR__). "/config/{$this->moduleName}.php";
        if (!file_exists($filePath)) {
            throw new ModuleException('Config file not found');
        }
        $this->config = include_once $filePath;
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
     * Make a call by given operationId.
     *
     * @param string $method
     * @param string $uri
     * @param array $queryParams
     * @param array $formParams
     * @return mixed
     * @throws \AmazonSellingPartnerAPI\Exception\AmazonSellingPartnerAPIException
     * @throws \AmazonSellingPartnerAPI\Exception\ClientException
     * @throws \AmazonSellingPartnerAPI\Exception\OAuthException
     */
    protected function doCall(string $method, string $uri, array $queryParams, array $formParams)
    {
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

        return $client->request($method, $uri, $formParams, $queryParams);
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
     */
    protected function resolveUri(string $uri, array $pathParams): string
    {
        foreach ($pathParams as $key => $param) {
            $uri = str_replace("{{$key}}", $param, $uri);
        }

        return $uri;
    }

    public function __call($name, $arguments)
    {
        if (empty($this->auth)) {
            throw new ModuleException('Not auth info has set');
        }
        if (!isset($this->config[$name])) {
            throw new ModuleException("invalid operationId: {$name}");
        }
        if (!isset($arguments[0])) {
            throw new ModuleException('Missing params');
        }
        $config = $this->config[$name];
        $data = $arguments[0];
        $pathParams  = $this->validate($config['path_params'] ?? [], $data['path_params'] ?? []);
        $queryParams = $this->validate($config['query_params'] ?? [], $data['query_params'] ?? []);
        foreach ($queryParams as $k => $v) {
            if (is_array($v)) {
                $queryParams[$k] = implode(',', $v);
            }
        }

        return $this->doCall(
            $config['method'],
            $this->resolveUri($config['path'], $pathParams),
            $queryParams,
            $this->validate($config['form_params'] ?? [], $data['form_params'] ?? [])
        );
    }
}
