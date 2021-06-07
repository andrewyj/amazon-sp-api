<?php

declare(strict_types=1);

namespace AmazonSellingPartnerAPI;

use AmazonSellingPartnerAPI\Contract\SignInterface;
use AmazonSellingPartnerAPI\Exception\ClientException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Utils;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Psr7\Request;

class Client
{
    const REGION_NORTH_AMERICA = 'us-east-1';
    const REGION_EUROPE        = 'eu-west-1';
    const REGION_FAR_EAST      = 'us-west-2';

    /**
     * @var GuzzleClient Guzzule client.
     */
    protected $client;

    /**
     * @var string[] Region endpoint map.
     */
    protected $regionEndpoint = [
        self::REGION_NORTH_AMERICA => 'sellingpartnerapi-na.amazon.com',
        self::REGION_EUROPE        => 'sellingpartnerapi-eu.amazon.com',
        self::REGION_FAR_EAST      => 'sellingpartnerapi-fe.amazon.com',
    ];

    /**
     * @var string[] Auth rules.
     */
    protected $authRules = [
        'client_id'         => 'required|string',
        'client_secret'     => 'required|string',
        'access_key'        => 'required|string',
        'secret_key'        => 'required|string',
        'region'            => 'required|string',
        'role_arn'          => 'string',
        'refresh_token'     => 'required|string',
        'access_token'      => 'required|string',
        'session_token'     => 'string',
    ];

    /**
     * @var string[] Validated auth info.
     */
    protected $auth;

    /**
     * @var SignInterface
     */
    protected $signer;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * Last request rateLimit.
     * @var null
     */
    protected $rateLimit = null;

    /**
     * Client constructor.
     *
     * @param SignInterface $signer
     */
    public function __construct(SignInterface $signer)
    {
        $this->validator = new Validator();
        $this->signer = $signer;
        $this->client = new GuzzleClient();
    }

    /**
     * @param array $auth
     * [
     *     'client_id'         => 'required|string',   App ID from Seller Central, amzn1.sellerapps.app.cfbfac4a-......
     *     'client_secret'     => 'required|string',   The corresponding App Client Secret
     *     'access_key'        => 'required|string',   User role credentials access_key
     *     'secret_key'        => 'required|string',   User role credentials secret_key
     *     'region'            => 'required|string',   Region of seller.
     *     'role_arn'          => 'string',            AWS IAM Role ARN for example: arn:aws:iam::123456789:role/Your-Role-Name
     *     'refresh_token'     => 'required|string',
     *     'access_token'      => 'required|string',   Access Key of AWS IAM User, for example AKIAABCDJKEHFJDS
     *     'session_token'     => 'string',   Secret Key of AWS IAM User
     * ]
     * @return $this
     * @throws ClientException
     */
    public function setAuth(array $auth): Client
    {
        $res = $this->validator->validate($this->authRules, $auth);
        if ($res !== true) {
            throw new ClientException($this->validator->lastError());
        }
        if (!isset($this->regionEndpoint[$auth['region']])) {
            throw new ClientException("Invalid region: {$auth['region']}");
        }
        $this->auth = $this->validator->validated();

        return $this;
    }

    /**
     * If debug = true.we'll use sandbox endpoint.
     *
     * @param bool $debug
     * @return $this
     */
    public function debug(bool $debug): Client
    {
        $this->debug = $debug;
        return $this;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function post(string $uri, array $queryParams = [], array $formParams = [])
    {
        return $this->request('POST', $uri, $formParams, $queryParams);
    }

    public function get(string $uri,array $queryParams = [])
    {
        return $this->request('GET', $uri, [], $queryParams);
    }

    public function put(string $uri, array $queryParams = [], array $formParams = [])
    {
        return $this->request('PUT', $uri, $formParams, $queryParams);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $formParams
     * @param array $queryParams
     * @param string $body
     * @return mixed
     * @throws ClientException
     */
    public function request(
        string $method,
        string $uri,
        array $formParams = [],
        array $queryParams = [],
        string $body = ''
    ): array {
        if (empty($this->auth)) {
            throw new ClientException('Not auth info has set');
        }
        $this->resetRateLimit();
        try {
            if (!empty($formParams)) {
                ksort($formParams);
                $body = Utils::jsonEncode($formParams);
            }
            if (!empty($queryParams)) {
                ksort($queryParams);
            }
            $queryString = Query::build($queryParams);
            $request = new Request(
                $method,
                $this->getHost(). '/'. trim($uri, '/'). ($queryString ? '?'. $queryString : ''),
                $this->getSignedHeaders($method, $uri, $queryString, $body),
                $body
            );
            $response = $this->client->send($request);
            $contents = $response->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\ClientException | RequestException $e) {
            $response = $e->getResponse();
            $contents = str_replace(["\r\n", "\r", "\n"], '', $response->getBody()->getContents());
        } catch (\Exception $e) {
            throw new ClientException($e->getMessage(), $e->getCode());
        }
        $this->rateLimit = $response->getHeaders()['x-amzn-RateLimit-Limit'][0] ?? null;

        return Utils::jsonDecode($contents, true);
    }

    public function getRateLimit()
    {
        return $this->rateLimit;
    }

    protected function resetRateLimit()
    {
        $this->rateLimit = null;
    }

    protected function getSignedHeaders(string $method, string $uri, string $queryString, string $body): array
    {
        $signBody = [
            'host'              => $this->getHost(false),
            'method'            => $method,
            'uri'               => $uri,
            'query_string'      => $queryString,
            'body'              => $body,
            'access_key'        => $this->auth['access_key'],
            'secret_key'        => $this->auth['secret_key'],
            'region'            => $this->auth['region'],
            'access_token'      => $this->auth['access_token'],
            'security_token'    => $this->auth['session_token'] ?? '',
        ];
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
        $signedHeaders = $this->signer->sign($signBody);
        if ($signedHeaders === false) {
            throw new ClientException('Sign error: '. $this->signer->getLastError());
        }

        return array_merge($headers, $this->signer->sign($signBody));
    }

    /**
     * @param bool $withScheme
     * @return string
     */
    protected function getHost(bool $withScheme = true): string
    {
        $host = ($this->debug ? 'sandbox.' : ''). $this->regionEndpoint[$this->auth['region']];
        if ($withScheme === false) {
            return $host;
        }

        return 'https://'. $host;
    }
}
