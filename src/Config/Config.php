<?php

namespace AmazonSellingPartnerAPI\Config;

use AmazonSellingPartnerAPI\Exception\AmazonSellingPartnerAPIException;

class Config
{
    protected static $configs = [];

    public static function get($name, $default = null)
    {
        $fields = explode('.', $name);
        $module = array_shift($fields);
        if (!isset(self::$configs[$module])) {
            self::set($module);
        }
        $val = self::$configs[$module];
        foreach ($fields as $field) {
            $val = $val[$field] ?? null;
            if (is_null($val)) {
                return $default;
            }
        }

        return $val;
    }

    protected static function set($name)
    {
        $filePath = dirname(__DIR__). "/config/{$name}.php";
        if (!file_exists($filePath)) {
            throw new AmazonSellingPartnerAPIException('Config file not found');
        }
        self::$configs[$name] = include_once $filePath;
    }
}
