<?php

namespace SSOfy\Laravel\Rules;

use Illuminate\Contracts\Validation\Rule;
use SSOfy\Models\Sort;

class SortValidation implements Rule
{
    /**
     * @var string
     */
    protected $message = '';

    /**
     * @return self
     */
    public static function make()
    {
        return app(static::class);
    }

    /**
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        try {
            $sort = new Sort($value);
            $sort->toArray(); // Triggers validation in the Sort class
            return true;
        } catch (\Exception $e) {
            $this->message = $e->getMessage();
            return false;
        }
    }

    /**
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
