<?php

namespace AmazonSellingPartnerAPI;

use AmazonSellingPartnerAPI\Contract\SignInterface;
use AmazonSellingPartnerAPI\Exception\AmazonSellingPartnerAPIException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Query;

class AssumeRole
{
    /** @var string */
    protected $accessKeyId;

    /** @var string */
    protected $secretAccessKey;

    /**
     * @var string
     */
    protected $region;

    /**
     * @var int SessionToken duration seconds.
     */
    protected $duration = 3600;

    protected $signer;

    /**
     * AssumeRole constructor.
     * @param string $region
     * @param string $accessKeyId
     * @param string $secretAccessKey
     * @param SignInterface $signer
     */
    public function __construct(string $region, string $accessKeyId, string $secretAccessKey, SignInterface $signer)
    {
        $this->region = $region;
        $this->accessKeyId = $accessKeyId;
        $this->secretAccessKey = $secretAccessKey;
        $this->signer = $signer;
    }

    public function setAccessKeyId(string $accessKeyId): AssumeRole
    {
        $this->accessKeyId = $accessKeyId;
        return $this;
    }

    public function setSecretAccessKey(string $secretAccessKey): AssumeRole
    {
        $this->secretAccessKey = $secretAccessKey;
        return $this;
    }

    public function setRegion(string $region): AssumeRole
    {
        $this->region = $region;
        return $this;
    }

    public function setDuration(int $duration): AssumeRole
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @param string $roleArn
     * @return array
     * @throws AmazonSellingPartnerAPIException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function assume(string $roleArn): array
    {
        $requestOptions = [
            'headers' => [
                'accept' => 'application/json',
            ],
            'form_params' => [
                'Action' => 'AssumeRole',
                'DurationSeconds' => $this->duration,
                'RoleArn' => $roleArn,
                'RoleSessionName' => 'amazon-sp-api-php',
                'Version' => '2011-06-15',
            ],
        ];

        $host = 'sts.amazonaws.com';
        $uri = '/';
        $signedHeader = $this->signer->sign([
            'host'        => $host,
            'method'      => 'POST',
            'uri'         => $uri,
            'region'      => $this->region,
            'service'     => 'sts',
            'access_key'  => $this->accessKeyId,
            'secret_key'  => $this->secretAccessKey,
            'body'        => Query::build($requestOptions['form_params']),
        ]);
        if ($signedHeader === false) {
            throw new AmazonSellingPartnerAPIException($this->signer->getLastError());
        }
        $client = new Client([
            'base_uri' => 'https://'.$host,
        ]);
        $requestOptions['headers'] = array_merge($requestOptions['headers'], $signedHeader);
        try {
            $response = $client->post($uri, $requestOptions);
        } catch (ClientException $e) {
            $response = $e->getResponse();
        } catch (RequestException $e) {
            $response = $e->getResponse();
        } catch (\Exception $e) {
            throw new AmazonSellingPartnerAPIException($e->getMessage(), $e->getCode());
        }
        $res = json_decode($response->getBody(), true);
        $credentials = $res['AssumeRoleResponse']['AssumeRoleResult']['Credentials'] ?? null;
        if (is_null($credentials)) {
            throw new AmazonSellingPartnerAPIException('Assume role error: '. $res['Error']['Message']);
        }

        return $credentials;
    }
}
