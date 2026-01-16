<?php

namespace App\Rules;

use Closure;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;

class MaxWords implements Rule
{
    public function __construct(private int $max) {}

    public function passes($attribute, $value)
    {
        return str_word_count(strip_tags($value)) <= $this->max;
    }

    public function message()
    {
        return 'Isi maksimal :max kata.';
    }
}
