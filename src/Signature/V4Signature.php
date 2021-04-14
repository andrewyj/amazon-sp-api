<?php

declare(strict_types=1);

namespace AmazonSellingPartnerAPI\Signature;

use AmazonSellingPartnerAPI\Contract\SignInterface;
use AmazonSellingPartnerAPI\Validator;

class V4Signature implements SignInterface
{
    protected $signRules = [
        'host'              => 'required|string',
        'method'            => 'required|string',
        'uri'               => 'required|string',
        'access_key'        => 'required|string',
        'secret_key'        => 'required|string',
        'region'            => 'required|string',
        'user_agent'        => 'string',
        'access_token'      => 'string',
        'security_token'    => 'string',
        'service'           => 'string',
        'query_string'      => 'string',
        'form_params'       => 'string',
    ];

    protected $lastSignError = '';

    public function sign(array $params)
    {
        $this->lastSignError = '';
        $validator = new Validator();
        $res = $validator->validate($this->signRules, $params);
        if ($res !== true) {
            $this->lastSignError = $validator->lastError();
            return false;
        }
        $params = $validator->validated();
        $terminationString = 'aws4_request';
        $algorithm         = 'AWS4-HMAC-SHA256';
        $amzdate           = gmdate('Ymd\THis\Z');
        $date              = substr($amzdate, 0, 8);
        $service           = $params['service'] ?? 'execute-api';

        // Hashed payload
        $hashedPayload = hash('sha256', $params['form_params'] ?? '');

        //Compute Canonical Headers
        $canonicalHeaders = [
            'host' => $params['host'],
            'user-agent' => $params['user_agent'] ?? 'cs-php-sp-api-client/2.1',
        ];
        if (!empty($params['access_token'])) {
            $canonicalHeaders['x-amz-access-token'] = $params['access_token'];
        }
        $canonicalHeaders['x-amz-date'] = $amzdate;
        if (!empty($params['security_token'])) {
            $canonicalHeaders['x-amz-security-token'] = $params['security_token'];
        }

        $canonicalHeadersStr = '';
        foreach ($canonicalHeaders as $h => $v) {
            $canonicalHeadersStr .= $h.':'.$v."\n";
        }
        $signedHeadersStr = join(';', array_keys($canonicalHeaders));

        //Prepare credentials scope
        $credentialScope = $date.'/'.$params['region'].'/'.$service.'/'.$terminationString;

        //prepare canonical request
        $queryString = $params['query_string'] ?? '';
        $canonicalRequest = "{$params['method']}\n{$params['uri']}\n{$queryString}\n{$canonicalHeadersStr}\n{$signedHeadersStr}\n{$hashedPayload}";

        //Prepare the string to sign
        $stringToSign = "{$algorithm}\n{$amzdate}\n{$credentialScope}\n". hash('sha256', $canonicalRequest);

        //Start signing locker process
        //Reference : https://docs.aws.amazon.com/general/latest/gr/signature-version-4.html
        $kSecret  = 'AWS4'.$params['secret_key'];
        $kDate    = hash_hmac('sha256', $date, $kSecret, true);
        $kRegion  = hash_hmac('sha256', $params['region'], $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', $terminationString, $kService, true);

        //Compute the signature
        $signature = trim(hash_hmac('sha256', $stringToSign, $kSigning));

        //Finalize the authorization structure
        $authorizationHeader = $algorithm." Credential={$params['access_key']}/{$credentialScope}, SignedHeaders={$signedHeadersStr}, Signature={$signature}";

        return array_merge($canonicalHeaders, [
            'Authorization' => $authorizationHeader,
        ]);
    }

    public function getLastError(): string
    {
        return $this->lastSignError;
    }
}
