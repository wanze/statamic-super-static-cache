# Super Static Cache

[![Build Status](https://travis-ci.org/wanze/SuperStaticCache.svg?branch=master)](https://travis-ci.org/wanze/SuperStaticCache)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Statamic Addon to prevent static file caching for authenticated users. Useful if you want to cache pages only
for anonymous users, e.g. if they contain some sort of tracking javascript that should not be included
for authenticated users.

More background information can be found in [this issue](https://github.com/statamic/v2-hub/issues/2267).

This addon extends Statamic's `FileCacher` service to exclude the generation of static cache files for
authenticated users.

> Note: Currently only works if `static_caching_type` is set to `file`.

## Installation

1. Clone this repo or download the zip
2. Move the `SuperStaticCache` folder to `site/addons`

## Configuration

The addon offers the following settings:

* **`Cookie name`** Name of the cookie used by the reverse proxy to skip static file cache for authenticated users. 
* **`User roles`** Skip static file cache only for some user roles.
* **`User groups`** Skip static file cache only for some user groups.

Next, you need to configure the reverse proxy to skip the static file cache if the configured cookie exists.

### Apache

Add the following rewrite condition in your `.htaccess` below the _Static Caching Proxy_ section:

```RewriteCond %{HTTP_COOKIE} !^.*statamic_static_cache_skip.*$```

> Replace `statamic_static_cache_skip` with the name of your cookie.

### Nginx

ℹ️ TODO! If you know how to configure Nginx, please let me know or send a pull request. Thanks :)

### IIS

ℹ️ TODO! If you know how to configure IIS, please let me know or send a pull request. Thanks :)
