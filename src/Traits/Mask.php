<?php

namespace SSOfy\Laravel\Traits;

trait Mask
{
    protected function hideEmailAddress($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }

        list($first, $last) = explode('@', $email);

        $first      = str_repeat('*', strlen(substr($first, 0, -2))) . substr($first, -2, 2);
        $last       = explode('.', $last);
        $lastDomain = str_replace(substr($last['0'], '1'), str_repeat('*', strlen($last['0']) - 1), $last['0']);

        return $first . '@' . $lastDomain . '.' . $last['1'];
    }

    protected function hidePhoneNumber($phone)
    {
        return str_repeat('*', strlen(substr($phone, 0, -2))) . substr($phone, -2, 2);
    }
}
