<?php

declare(strict_types=1);

namespace AmazonSellingPartnerAPI;

class Validator
{
    protected $validated = [];

    protected $errors = [];

    public function validate(array $rules,array $values): bool
    {
        $this->errors = [];
        $this->validated = [];
        foreach ($rules as $key => $rule) {
            $ruleItems = explode('|', $rule);
            if (empty($ruleItems)) {
                continue;
            }
            foreach ($ruleItems as $ruleItem) {
                $value = $values[$key] ?? null;
                $ruleValue = explode(':', $ruleItem);
                if (is_null($value) && !in_array('required', $ruleValue, true)) {
                    continue;
                }
                if ($ruleValue[0] === 'required') {
                    if (is_null($value)) {
                        $this->errors[$key] = "{$key} is required.";
                        continue;
                    }
                } elseif ($ruleValue[0] === 'array') {
                    if (!is_array($value)) {
                        $this->errors[$key] = "{$key} is not an array.";
                        continue;
                    }
                } elseif ($ruleValue[0] === 'string') {
                    if (!is_string($value)) {
                        $this->errors[$key] = "{$key} is not string.";
                        break;
                    }
                } elseif ($ruleValue[0] === 'integer') {
                    if (!is_integer($value)) {
                        $this->errors[$key] = "{$key} is not integer.";
                        break;
                    }
                } elseif ($ruleValue[0] === 'min') {
                    $min = $ruleValue[1] ?? 0;
                    $len = is_string($value) ? strlen($value) : (is_array($value) ? count($value) : $value);
                    if ($len < $min) {
                        $this->errors[$key] = "{$key} is less than {$min}.";
                        break;
                    }
                } elseif ($ruleValue[0] === 'max') {
                    $max = $ruleValue[1] ?? 0;
                    $len = is_string($value) ? strlen($value) : (is_array($value) ? count($value) : $value);
                    if ($len > $max) {
                        $this->errors[$key] = "{$key} is greater than {$max}.";
                        break;
                    }
                } elseif ($ruleValue[0] === 'in') {
                    $enum = explode(',', $ruleValue[1] ?? '');
                    if (!in_array($value, $enum, true)) {
                        $this->errors[$key] = "value {$value} of {$key} not valid.";
                        break;
                    }
                } else {
                    $this->errors['rule'] = "unknown rule: {$ruleValue[0]}.";
                    return false;
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
}
