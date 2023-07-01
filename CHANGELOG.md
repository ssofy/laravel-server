# CHANGELOG

## 2.0.1 - 2023-07-01

* Hotfix: fixed the otp options endpoint validation error.

## 2.0.0 - 2023-06-18

* Upgraded to `ssofy/php-sk:2.0.0`.
* Removed SSO Client functionality.
* Renamed the config file to `sso-server.php`.
* The `OTP` class is renamed to `UserTokenManager` to avoid confusions.
* Repositories manipulate database directly instead of relying on User Provider.
* Dispatching Events for various operations done.
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
