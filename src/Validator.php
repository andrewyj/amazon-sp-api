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
            $ruleItems = explode('|', $rule);
            if (empty($ruleItems)) {
                continue;
            }
            if (strpos($key, '.') > 0) {
                $keys = explode('.', $key);
                $value = $values[$keys[0]] ?? null;
                unset($this->validated[$keys[0]]);
                $res = $this->arrayValidate($value, $ruleItems, $keys, 0, count($keys) -1);
                if ($res !== false) {
                    $this->validatedArray[$keys[0]] = array_merge($this->validatedArray[$keys[0]] ?? [], $res);
                }
            } else {
                $shouldPassed = true;
                foreach ($ruleItems as $ruleItem) {
                    if (strpos($ruleItem, 'required') === false && !isset($values[$key])) {
                        continue;
                    }
                    $res = $this->doValidate($values[$key] ?? null, $ruleItem, $key);
                    if ($res === false) {
                        $shouldPassed = false;
                        break;
                    }
                }
                if ($shouldPassed === true && isset($values[$key])) {
                    $this->validated[$key] = $values[$key];
                }
            }
        }

        return count($this->errors) <= 0;
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

    protected function doValidate($value, string $rule, $key): bool
    {
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
                $this->errors[$key] = "{$key} is less than {$min}";
                return false;
            }
        } elseif ($ruleValue[0] === 'max') {
            $max = $ruleValue[1] ?? 0;
            $len = is_string($value) ? strlen($value) : (is_array($value) ? count($value) : $value);
            if ($len > $max) {
                $this->errors[$key] = "{$key} is greater than {$max}";
                return false;
            }
        } elseif ($ruleValue[0] === 'in') {
            $enum = explode(',', $ruleValue[1] ?? '');
            if (!in_array($value, $enum, true)) {
                $this->errors[$key] = "value {$value} of {$key} not valid";
                return false;
            }
        } else {
            $this->errors['rule'] = "unknown rule: {$ruleValue[0]}";
            return false;
        }

        return true;
    }

    protected function arrayValidate($value, array $rules, array $keys, int $currentLevel,int $totalLevel)
    {
        if (is_null($value)) {
            if (!in_array('required', $rules, true)) {
                return false;
            } else {
                $key = implode('.', array_slice($keys, 0, ($currentLevel+1)));
                $this->errors[$key] = "{$key} can not be null";
            }
        }
        if ($totalLevel == $currentLevel) {
            foreach ($rules as $rule) {
                if ($this->doValidate($value, $rule, implode('.', $keys)) === false) {
                    return false;
                }
            }

            return $value;
        }
        $validated = [];
        ++$currentLevel;
        $index = $keys[$currentLevel];
        if ($index === '*') {
            $value = (array)$value;
            foreach ($value as $index => $item) {
                $res = $this->arrayValidate($item, $rules, $keys, $currentLevel, $totalLevel);
                if ($res === false) {
                    return false;
                }
                $validated[$index] = $res;
            }
        } else {
            $res = $this->arrayValidate($value[$index] ?? null, $rules, $keys, $currentLevel, $totalLevel);
            if ($res === false) {
                return false;
            }
            if (isset($value[$index])) {
                $validated[$index] = $value[$index];
            }
        }

        return $validated;
    }
}
