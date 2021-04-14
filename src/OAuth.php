<?php

namespace AmazonSellingPartnerAPI;

use AmazonSellingPartnerAPI\Exception\OAuthException;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class OAuth
{
    protected $client;
    protected $clientId;
    protected $clientSecret;

    public function __construct(string $clientId, string $clientSecret)
    {
        $this->client = new Client(['base_uri' => 'https://api.amazon.com/auth/o2/']);
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function setClientId(string $clientId): OAuth
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function setClientSecret(string $clientSecret): OAuth
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    /**
     * @param $refreshToken
     * @return object
     * @throws OAuthException
     */
    public function getAccessToken($refreshToken): object
    {
        return $this->send([
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);
    }

    /**
     * @param string $lwaAuthorizationCode
     * @param string $redirectUri
     * @return string|null
     * @throws OAuthException
     */
    public function getRefreshToken(string $lwaAuthorizationCode, string $redirectUri): ?string
    {
        $res = $this->send([
            [
                'grant_type'   => 'authorization_code',
                'code'         => $lwaAuthorizationCode,
                'redirect_uri' => $redirectUri,
            ]
        ]);

        return $res->refresh_token;
    }

    protected function send(array $extraOptions = []): object
    {
        $options = [
            RequestOptions::HEADERS => ['Accept' => 'application/json'],
            RequestOptions::HTTP_ERRORS => false,
            'curl' => [
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            ],
            RequestOptions::FORM_PARAMS => array_merge($extraOptions, [
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
            ])
        ];
        $response = $this->client->post('token', $options);
        $body = $response->getBody()->getContents();
        $res  = json_decode($body);
        if (isset($res->error)) {
            throw new OAuthException($res->error. ':'. $res->error_description);
        }

        return $res;
    }
}
