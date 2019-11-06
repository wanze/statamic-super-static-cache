# Changelog

## [Unreleased]

## [1.4.0] - 2019-11-06

### Added

* Add the possibility to use the static cache with forms by injecting CSRF tokens via ajax calls

## [1.3.0] - 2019-10-14

### Added

* Easier debugging: Flag pages served by the static cache with a customizable comment string in the source code ([#4](https://github.com/wanze/SuperStaticCache/issues/4))

## [1.2.1] - 2019-09-28

### Fixed

* Fix whitelisted query strings not working correctly in combination with paths using `*` as wildcard

## [1.2.0] - 2019-09-22

### Added

* Add the possibility to generate the static cache via `super_static_cache:warmup` command 🤓

## [1.1.1] - 2018-12-12

### Fixed

* Fix missing array initialisation for `whitelisted_query_strings` setting ([#2](https://github.com/wanze/SuperStaticCache/issues/2))

## [1.1.0] - 2018-12-11

### Added

* Add setting `cache_disabled_authenticated` to toggle disabling caching for authenticated users 
* Add setting `whitelisted_query_strings` to whitelist query strings per paths

### Changed

* Some settings were renamed, please update accordingly:
  * `user_roles` ➡ `cache_disabled_user_roles` 
  * `user_groups` ➡ `cache_disabled_user_groups`
  * `cookie_name` ➡ `cache_disabled_cookie_name`

## [1.0.0] - 2018-12-03

Initial release of the addon 🐣

[Unreleased]: https://github.com/wanze/SuperStaticCache/compare/v1.4.0...HEAD
[1.0.0]: https://github.com/wanze/SuperStaticCache/releases/tag/v1.0.0
[1.1.0]: https://github.com/wanze/SuperStaticCache/releases/tag/v1.1.0
[1.1.1]: https://github.com/wanze/SuperStaticCache/releases/tag/v1.1.1
[1.2.0]: https://github.com/wanze/SuperStaticCache/releases/tag/v1.2.0
[1.2.1]: https://github.com/wanze/SuperStaticCache/releases/tag/v1.2.1
[1.3.0]: https://github.com/wanze/SuperStaticCache/releases/tag/v1.3.0
[1.4.0]: https://github.com/wanze/SuperStaticCache/releases/tag/v1.4.0
