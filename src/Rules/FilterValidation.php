<?php

namespace SSOfy\Laravel\Rules;

use Illuminate\Contracts\Validation\Rule;
use SSOfy\Models\Filter;

class FilterValidation implements Rule
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
     * Validates a single filter or an array of nested filters.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($this->appearsAsFilter($value)) {
            return $this->validateFilter($attribute, $value);
        } elseif (is_array($value)) {
            foreach ($value as $key => $subvalue) {
                if (!$this->passes($attribute . '.' . $key, $subvalue)) {
                    return false;
                }
            }
            return true;
        }

        $this->message = 'The ' . $attribute . ' is neither a valid filter nor a valid group of filters.';

        return false;
    }

    /**
     * Validate a single filter using the Filter class.
     *
     * @param string $attribute
     * @param array $filterData
     * @return bool
     */
    protected function validateFilter($attribute, $filterData)
    {
        try {
            $filter = new Filter($filterData);
            $filter->toArray(); // Triggers validation in the Filter class
            return true;
        } catch (\Exception $e) {
            $this->message = $e->getMessage();
            return false;
        }
    }

    /**
     * Check if the provided array appears to be a filter.
     *
     * @param mixed $value
     * @return bool
     */
    protected function appearsAsFilter($value)
    {
        return is_array($value) && isset($value['key']);
    }

    /**
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
