# CHANGELOG

## 2.1.0 - 2023-06-08

* Bug Fixes.

## 2.0.0 - 2023-06-06

* Upgraded to `ssofy/php-sk:2.0.0`.
* Split of the config file into `sso.php` and `sso-config.php`.
* The `OTP` class is renamed to `UserTokenManager` for readability.
* Added the route for logout (`/sso/logout?everywhere=0`).
* Added the route for social login (`/sso//social/google`).
* Bug Fixes.

## 1.0.4 - 2023-06-02

* Fixed the issue with event payload validation.
* Fixed the issue with `user_add` and `user_update`.
* Fixed the issue with empty `name` attribute.

## 1.0.3 - 2023-06-02

* Improved user search functionality using login, email, and phone.

## 1.0.2 - 2023-06-01

* Fixed the issue with `email_verified` and `phone_verified` claims.

## 1.0.1 - 2023-06-01

* Handled `user_updated` (profile updates) and `user_added` (registration).
* Added config options for authentication methods.
* Fixed the issue with passwordless authentication.

## 1.0.0 - 2023-03-24

* First Release.
