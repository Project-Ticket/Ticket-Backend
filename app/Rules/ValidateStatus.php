<?php

namespace App\Rules;

use App\Services\Status;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateStatus implements ValidationRule
{
    protected string $group;

    public function __construct(string $group)
    {
        $this->group = $group;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, string): void  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $validStatusIds = array_keys(Status::getAll($this->group));

        if (!in_array((int)$value, $validStatusIds, true)) {
            $fail("The selected {$attribute} is invalid.");
        }
    }
}
