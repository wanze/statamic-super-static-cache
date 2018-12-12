# Changelog

## [Unreleased]

## v1.1.1 - 2018-12-12

**Fixed**

* Fix missing array initialisation for `whitelisted_query_strings` setting ([#2](https://github.com/wanze/SuperStaticCache/issues/2))

## v1.1.0 - 2018-12-11

**Added**

* Add setting `cache_disabled_authenticated` to toggle disabling caching for authenticated users 
* Add setting `whitelisted_query_strings` to whitelist query strings per paths

**Changed**

* Some settings were renamed, please update accordingly:
  * `user_roles` ➡ `cache_disabled_user_roles` 
  * `user_groups` ➡ `cache_disabled_user_groups`
  * `cookie_name` ➡ `cache_disabled_cookie_name`

## v1.0.0 - 2018-12-03

Initial release of the addon.

[Unreleased]: https://github.com/wanze/SuperStaticCache/compare/v1.1.0...HEAD
