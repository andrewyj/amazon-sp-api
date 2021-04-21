<?php

namespace AmazonSellingPartnerAPI;

class Schemas
{
    protected static $container = [
        'containerType'        => 'in:PACKAGE',
        'containerReferenceId' => 'required|string|max:40',
        'value'                => '@currency',
        'dimensions'           => '@dimensions',
        'items.*'              => '@containerItem',
        'weight'               => '@weight'
    ];

    protected static $containerItem = [
        'quantity'   => 'required|integer',
        'unitPrice'  => '@currency',
        'unitWeight' => '@weight',
        'title'      => 'required|string|max:30'
    ];

    protected static $containerSpecification = [
        'weight'     => '@weight',
        'dimensions' => '@dimensions',
    ];

    protected static $weight = [
        'unit'  => 'required|in:g,kg,oz,lb',
        'value' => 'required|integer',
    ];

    protected static $currency = [
        'value' => 'required|integer',
        'unit'  => 'required|string|max:3|min:3',
    ];

    protected static $dimensions = [
        'length' => 'required|integer',
        'width'  => 'required|integer',
        'height' => 'required|integer',
        'unit'   => 'required|in:IN,CM',
    ];

    protected static $address = [
        'name'          => 'required|string',
        'addressLine1'  => 'required|string',
        'addressLine2'  => 'string',
        'addressLine3'  => 'string',
        'stateOrRegion' => 'required|string',
        'city'          => 'required|string|min:1|max:50',
        'countryCode'   => 'required|string|min:2|max:2',
        'postalCode'    => 'required|string|min:1|max:20',
        'email'         => 'string|max:64',
        'copyEmails'    => 'array',
        'copyEmails.*'  => 'string',
        'phoneNumber.*' => 'string',
    ];

    protected static $labelSpecification = [
        'labelFormat'    => 'required|in:PNG',
        'labelStockSize' => 'required|in:4x6'
    ];

    protected static function resolve($schema, $prefix = ''): array
    {
        $res = [];
        $prefix = empty($prefix) ? '' : $prefix. '.';
        foreach ($schema as $key => $val) {
            if (strpos($val, '@') !== false) {
                $link = substr($val, 1);
                $res = array_merge($res, self::resolve(self::${$link}, $prefix. $key));
            } else {
                $res[$prefix.$key] = $val;
            }
        }

        return $res;
    }

    public static function __callStatic($name, $prefix): array
    {
        $prefix = $prefix[0] ?? '';
        if (!is_string($prefix)) {
            return [];
        }
        $schema = self::${$name} ?? [];

        return self::resolve($schema, $prefix);
    }
}
