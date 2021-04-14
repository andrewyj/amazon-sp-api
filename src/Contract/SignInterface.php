<?php

namespace AmazonSellingPartnerAPI\Contract;

interface SignInterface {
    public function sign(array $params);

    public function getLastError();
}
