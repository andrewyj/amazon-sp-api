<?php

declare(strict_types=1);

namespace AmazonSellingPartnerAPI;

class Validator
{
    protected $validated = [];

    protected $errors = [];

    public function validate(array $rules, array $values): bool
    {
        $this->errors = [];
        $this->validated = [];
        foreach ($rules as $key => $rule) {
            $ruleItems = explode('|', $rule);
            if (empty($ruleItems)) {
                continue;
            }
            $value = $values[$key] ?? null;
            if (strpos($key, '.') > 0) {
                $keys = explode('.', $key);
                $value = $values[$keys[0]] ?? null;
                foreach ($ruleItems as $ruleItem) {
                    $this->arrayValidate($value, $ruleItem, $keys, 0, count($keys) -1);
                }
            } else {
                foreach ($ruleItems as $ruleItem) {
                    $this->doValidate($value, $ruleItem, $key);
                }
            }
            if (!isset($this->errors[$key]) && isset($values[$key])) {
                $this->validated[$key] = $values[$key];
            }
        }

        return count($this->errors) <= 0;
    }

    public function validated(): array
    {
        return $this->validated;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function lastError(): string
    {
        return array_values($this->errors)[0] ?? '';
    }

    protected function doValidate($value, string $rule, $key): bool
    {
        $ruleValue = explode(':', $rule);
        if (is_null($value) && !in_array('required', $ruleValue, true)) {
            return true;
        }
        if ($ruleValue[0] === 'required') {
            if (is_null($value)) {
                $this->errors[$key] = "{$key} is required.";
                return false;
            }
        } elseif ($ruleValue[0] === 'array') {
            if (!is_array($value)) {
                $this->errors[$key] = "{$key} is not an array.";
                return false;
            }
        } elseif ($ruleValue[0] === 'string') {
            if (!is_string($value)) {
                $this->errors[$key] = "{$key} is not string.";
                return false;
            }
        } elseif ($ruleValue[0] === 'integer') {
            if (!is_integer($value)) {
                $this->errors[$key] = "{$key} is not integer.";
                return false;
            }
        } elseif ($ruleValue[0] === 'min') {
            $min = $ruleValue[1] ?? 0;
            $len = is_string($value) ? strlen($value) : (is_array($value) ? count($value) : $value);
            if ($len < $min) {
                $this->errors[$key] = "{$key} is less than {$min}.";
                return false;
            }
        } elseif ($ruleValue[0] === 'max') {
            $max = $ruleValue[1] ?? 0;
            $len = is_string($value) ? strlen($value) : (is_array($value) ? count($value) : $value);
            if ($len > $max) {
                $this->errors[$key] = "{$key} is greater than {$max}.";
                return false;
            }
        } elseif ($ruleValue[0] === 'in') {
            $enum = explode(',', $ruleValue[1] ?? '');
            if (!in_array($value, $enum, true)) {
                $this->errors[$key] = "value {$value} of {$key} not valid.";
                return false;
            }
        } else {
            $this->errors['rule'] = "unknown rule: {$ruleValue[0]}.";
            return false;
        }

        return true;
    }

    protected function arrayValidate($value, string $rule, array $keys, int $currentLevel,int $totalLevel)
    {
        if ($totalLevel == $currentLevel) {
            $this->doValidate($value, $rule, implode('.', $keys));
            return;
        }
        ++$currentLevel;
        $index = $keys[$currentLevel];
        $key = implode('.', array_slice($keys, 0, $currentLevel));
        if ($index === '*') {
            if (!is_array($value)) {
                $keys[$currentLevel] = $currentLevel;
                $this->errors[$key] = "{$key} must be an array.";
                return;
            }
            foreach ($value as $item) {
                $this->arrayValidate($item, $rule, $keys, $currentLevel, $totalLevel);
            }
        } else {
            if (!isset($value[$index])) {
                $this->errors[$key] = "Undefined index '{$index}' of '{$key}'.";
                return;
            }
            $this->arrayValidate($value[$index], $rule, $keys, $currentLevel, $totalLevel);
        }
    }
}
