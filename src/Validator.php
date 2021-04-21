<?php

declare(strict_types=1);

namespace AmazonSellingPartnerAPI;

class Validator
{
    protected $validated = [];

    protected $validatedArray = [];

    protected $errors = [];

    public function validate(array $rules, array $values): bool
    {
        $this->errors = [];
        $this->validated = [];
        $this->validatedArray = [];
        foreach ($rules as $key => $rule) {
            if (strpos($key, '.') > 0) {
                $keys = explode('.', $key);
                $index = $keys[0];
                $value = $values[$index] ?? null;
                unset($this->validated[$index]);
                if (!isset($this->validatedArray[$index])) {
                    $this->validatedArray[$index] = [];
                }
                $this->arrayValidate($value, $rule, $keys, 0, count($keys) -1, $this->validatedArray[$index]);
            } else {
                if (strpos($rule, 'required') === false && !isset($values[$key])) {
                    continue;
                }
                if ($this->singleValidate($values[$key] ?? null, $rule, $key) === false) {
                    break;
                }
                if (isset($values[$key])) {
                    $this->validated[$key] = $values[$key];
                }
            }
        }

        return count($this->errors) == 0;
    }

    public function validated(): array
    {
        return array_merge($this->validated, $this->validatedArray);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function lastError(): string
    {
        return array_values($this->errors)[0] ?? '';
    }

    protected function singleValidate($value, string $rules, $key): bool
    {
        foreach (explode('|', $rules) as $rule) {
            $ruleValue = explode(':', $rule);
            if ($ruleValue[0] === 'required') {
                if (is_null($value)) {
                    $this->errors[$key] = "{$key} is required";
                    return false;
                }
            } elseif ($ruleValue[0] === 'array') {
                if (!is_array($value)) {
                    $this->errors[$key] = "{$key} is not an array";
                    return false;
                }
            } elseif ($ruleValue[0] === 'string') {
                if (!is_string($value)) {
                    $this->errors[$key] = "{$key} is not string";
                    return false;
                }
            } elseif ($ruleValue[0] === 'integer') {
                if (!is_integer($value)) {
                    $this->errors[$key] = "{$key} is not integer";
                    return false;
                }
            } elseif ($ruleValue[0] === 'min') {
                $min = $ruleValue[1] ?? 0;
                $len = is_string($value) ? strlen($value) : (is_array($value) ? count($value) : $value);
                if ($len < $min) {
                    $this->errors[$key] = "length of {$key} is less than {$min}";
                    return false;
                }
            } elseif ($ruleValue[0] === 'max') {
                $max = $ruleValue[1] ?? 0;
                $len = is_string($value) ? strlen($value) : (is_array($value) ? count($value) : $value);
                if ($len > $max) {
                    $this->errors[$key] = "length of {$key} is greater than {$max}";
                    return false;
                }
            } elseif ($ruleValue[0] === 'in') {
                $enum = explode(',', $ruleValue[1] ?? '');
                if (!in_array($value, $enum, true)) {
                    $this->errors[$key] = "value '{$value}' of {$key} not valid";
                    return false;
                }
            } else {
                $this->errors['rule'] = "unknown rule: '{$ruleValue[0]}'";
                return false;
            }
        }

        return true;
    }

    protected function arrayValidate($value, string $rules, array $keys, int $currentLevel,int $totalLevel, &$validated)
    {
        if (empty($value)) {
            if (strpos($rules, 'required') !== false) {
                $key = implode('.', array_slice($keys, 0, ($currentLevel+1)));
                $this->errors[$key] = "{$key} is required";
            }

            return false;
        }
        if ($totalLevel == $currentLevel) {
            if ($this->singleValidate($value, $rules, implode('.', $keys)) === false) {
                return false;
            }
            $validated = $value;

            return true;
        }
        ++$currentLevel;
        $index = $keys[$currentLevel];

        if ($index !== '*') {
            $val = $value[$index];
            $value = [$index => $val ?? null];
        }
        foreach ($value as $index => $item) {
            if (!isset($validated[$index])) {
                $validated[$index] = [];
            }
            $res = $this->arrayValidate($item, $rules, $keys, $currentLevel, $totalLevel, $validated[$index]);
            if ($res === false) {
                unset($validated[$index]);
                return false;
            }
        }

        return true;
    }
}
