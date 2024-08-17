# CHANGELOG

## 2.1.5 - 2024-08-17

* Fixed the Social Auth issue.

## 2.1.4 - 2024-06-17

* Fixed the PaginatedResponseEntity import.

## 2.1.3 - 2024-06-16

* Fixed the bug with UserRepository::find().

## 2.1.2 - 2024-06-16

* Alignments with latest php-sdk `UserRepositoryInterface::find()` signature updates.
* Implemented `UserRepository::findAll()`.
* Removed user caching in UserRepository.
* Enhanced validation rules by taking advantages of php-sdk model validation.
* Added `/users` endpoint.

## 2.1.1 - 2024-01-30

* Fixed the message in blade view.

## 2.1.0 - 2024-01-29

* Added config for otp options' enabled methods.
* Updated Blade views. OTP Action included.
* Config file revamp with compatibility.

## 2.0.6 - 2023-10-01

* Renamed `settings` to `vars` in default otp templates while preserving the old functionality.

## 2.0.5 - 2023-09-16

* Fixed OTP verification issue with non-exising otp option or user.
* Fixed wrong column mapping in config file.
* Config property rename `otp.notification.settings` to `otp.notification.vars` for compatibility with the new draft.

## 2.0.4 - 2023-08-15

* Validations fix.

## 2.0.3 - 2023-07-22

* Hotfix: OTPRepository fixed.

## 2.0.2 - 2023-07-22

* Fixed the SMS OTP error.
* OTPRepository refinements.
* Adapted repositories with the contracts provided in php-sdk.

## 2.0.1 - 2023-07-01

* Hotfix: fixed the otp options endpoint validation error.
* Hotfix: fixed the otp request issue.

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
