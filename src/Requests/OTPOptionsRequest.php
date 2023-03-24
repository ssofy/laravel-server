<?php

namespace SSOfy\Laravel\Requests;

use Illuminate\Foundation\Http\FormRequest;
use SSOfy\Laravel\Traits\Rules;

class OTPOptionsRequest extends FormRequest
{
    use Rules;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return $this->otpOptionsRules();
    }
}
