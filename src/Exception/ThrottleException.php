<?php

namespace AmazonSellingPartnerAPI\Exception;

use Throwable;

class ThrottleException extends AmazonSellingPartnerAPIException
{
    protected $throttlingDuration;
    public function __construct(int $throttlingDuration, Throwable $previous = null)
    {
        $message = "Throttling in {$throttlingDuration} seconds.";
        parent::__construct($message, $this->code, $previous);
    }

    public function getThrottlingDuration(): int
    {
        return $this->throttlingDuration;
    }
}
