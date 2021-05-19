<?php

declare(strict_types=1);

namespace AmazonSellingPartnerAPI;

class Validator
{
    public function __construct() {
        $this->registerValidators();
    }

    /**
     * @var array array of validated data.
     */
    protected $validated = [];

    protected $errors = [];

    protected $rules = [];

    protected $values = [];

    protected $validators = [];

    public function validate(array $rules, array $values): bool
    {
        $this->errors = [];
        $this->validated = [];
        $this->values = $values;
        $this->tidyRules($rules);
        $this->doValidate($this->rules, $values, $this->validated);

        return count($this->errors) === 0;
    }

    /**
     * return The validated data.
     *
     * @return array
     */
    public function validated(): array
    {
        return $this->validated;
    }

    /**
     * return all validation errors.
     *
     * @return array
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * get last validation error.
     *
     * @return string
     */
    public function lastError(): string
    {
        return array_values($this->errors)[0] ?? '';
    }

    protected function doValidate($rules, $data, &$validated, $keyName = [])
    {
        foreach ($rules as $key => $rule) {
            if (is_array($rule)) {
                $keyName[] = $key;
            }
            $hasData = true;
            if ($key !== '*') {
                if (isset($data[$key])) {
                    $dataItems = [$key => $data[$key]];
                } else {
                    $dataItems = [$key => null];
                    $hasData = false;
                }
            } else {
                if (empty($data[$key])) {
                    $dataItems = [null];
                    $hasData = false;
                } else {
                    $dataItems = $data[$key];
                }
            }
            foreach ($dataItems as $index => $dataItem) {
                $validated[$index] = null;
                if (is_array($rule)) {
                    $this->doValidate($rule, $dataItem, $validated[$index], $keyName);
                    if ($hasData === false) {
                        unset($validated[$index]);
                    }
                } else {
                    $res = $this->singleValidate($dataItem, $rule, trim(implode('.', $keyName). '.'. $index, '.')) && $hasData;
                    if ($res === false) {
                        unset($validated[$index]);
                    } else {
                        $validated[$index] = $dataItem;
                    }
                }
            }
        }
    }

    protected function tidyRules($rules)
    {
        $callback = function (array $indexes, &$res, $value, \Closure $callback) {
            if (count($indexes) == 0) {
                $res = $value;
                return;
            }
            $index = array_shift($indexes);
            $res[$index] = null;
            $callback($indexes, $res[$index], $value, $callback);
        };
        foreach ($rules as $index => $rule) {
            $indexes = explode('.', $index);
            $ruleItem = [];
            $callback($indexes, $ruleItem, $rule, $callback);
            $this->rules = array_merge_recursive($this->rules, $ruleItem);
        }
    }

    protected function registerValidators()
    {
        $this->validators['required'] = function ($value, $key, $ruleValues): bool {
            if (is_null($value)) {
                $this->errors[$key] = "{$key} is required";
                return false;
            }
            return true;
        };
        $this->validators['time'] = function ($value, $key, $ruleValues): bool {
            $timestamp = strtotime($value);
            if ($timestamp === false || gmdate("Y-m-d\TH:i:s\Z", $timestamp) != $value) {
                $this->errors[$key] = "{$key} must be in ISO 8601 date format";
                return false;
            }
            return true;
        };
        $this->validators['array'] = function ($value, $key, $ruleValues): bool {
            if (!is_array($value)) {
                $this->errors[$key] = "{$key} is not an array";
                return false;
            }
            return true;
        };
        $this->validators['string'] = function ($value, $key, $ruleValues): bool {
            if (!is_string($value)) {
                $this->errors[$key] = "{$key} is not a string";
                return false;
            }
            return true;
        };
        $this->validators['integer'] = function ($value, $key, $ruleValues): bool {
            if (!is_integer($value)) {
                $this->errors[$key] = "{$key} is not an integer";
                return false;
            }
            return true;
        };
        $this->validators['min'] = function ($value, $key, $ruleValues): bool {
            $min = $ruleValues;
            $len = is_string($value) ? strlen($value) : (is_array($value) ? count($value) : $value);
            if ($len < $min) {
                $this->errors[$key] = "length of {$key} is less than {$min}";
                return false;
            }
            return true;
        };
        $this->validators['max'] = function ($value, $key, $ruleValues): bool {
            $max = $ruleValues;
            $len = is_string($value) ? strlen($value) : (is_array($value) ? count($value) : $value);
            if ($len > $max) {
                $this->errors[$key] = "length of {$key} is greater than {$max}";
                return false;
            }
            return true;
        };
        $this->validators['in'] = function ($value, $key, $ruleValues): bool {
            $enum = explode(',', $ruleValues);
            if (!in_array($value, $enum, true)) {
                $this->errors[$key] = "value '{$value}' of {$key} not valid";
                return false;
            }
            return true;
        };
        $this->validators['required_without'] = function ($value, $key, $ruleValues): bool {
            if (!empty($value)) {
                return true;
            }
            $withoutFields = explode(',', $ruleValues);
            $fieldsExist = function (array $nestedFields, $data, \Closure $callback): bool {
                $index = array_shift($nestedFields);
                if (isset($data[$index])) {
                    if (count($nestedFields) > 0) {
                        return $callback($nestedFields, $data[$index], $callback);
                    }
                    return true;
                }
                return false;
            };
            foreach ($withoutFields as $withoutField) {
                if ($fieldsExist(explode('.', $withoutField), $this->values, $fieldsExist)) {
                    return true;
                }
            }
            $this->errors[$key] = "{$key} is required without {$ruleValues}";

            return false;
        };
    }

    protected function singleValidate($value, string $rules, $key): bool
    {
        foreach (explode('|', $rules) as $rule) {
            $rule = explode(':', $rule);
            if (empty($value) && strpos($rule[0], 'required') === false ) {
                return true;
            }
            $validator = $this->validators[$rule[0]] ?? null;
            if (is_null($validator)) {
                $this->errors['rule'] = "unknown rule: '{$rule[0]}'";
                return false;
            }
            if ($validator($value, $key, $rule[1] ?? null)) {
                continue;
            }

            return false;
        }

        return true;
    }
}
