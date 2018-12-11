# Super Static Cache

[![Build Status](https://travis-ci.org/wanze/SuperStaticCache.svg?branch=master)](https://travis-ci.org/wanze/SuperStaticCache)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Statamic Addon extending the static cache with additional features such as disabling caching for authenticated
users.

## Features

* Do you generate different markup for anonymous and authenticated users and want to make sure that a page gets cached by 
anonymous users only? With this Addon you may disable the static cache for authenticated users, optionally restricted 
to user roles or groups. Or in other words, enable static caching for anonymous users only.
* Increase security when caching query strings (`static_caching_ignore_query_strings=false`) by whitelisting allowed
query strings per path.

## Installation

1. Clone this repository or download and unpack the zip file
2. Move the `SuperStaticCache` folder to `site/addons`

## Configuration

Super Static Cache offers the following settings:

* **`Disable static caching for authenticated users`** Check to disable static caching for authenticated users
* **`User roles`** Enter role slugs to disable the cache only for users having a role defined here
* **`User groups`** Enter group slugs to disable the cache only for users belonging to a group defined here
* **`Cookie name`** Cookie used to skip static file cache from the reverse proxy 
* **`Whitelisted query strings`** Restrict the static cache to only cache whitelisted query strings per path. 
The value of each query string is validated against a regex pattern.
 
**Whitelisted query strings examples**

```yaml
/products:
  page: [0-9]+
```

Cache the `page` query string on the `/products` page, but only if it contains at least one number.

```yaml
/categories*
  page: [0-9]+
  sort: ^(desc|asc)$
```

Cache the `page` and `sort` query string of any page under `/categories` (using `*` as wildcard). Only create a cache
file if `page` contains at least one number and `sort` is equal to `desc` or `asc`.

Lastly, we need to adjust some configuration based on the active static caching type. 

### Full Measure

This strategy serves a cached file directly from the reverse proxy. We need to adjust the configuration of the reverse 
proxy to skip the cache if the "skip cache" cookie is present.

**Apache**

Add the following rewrite condition in your `.htaccess` below the _Static Caching Proxy_ section:

```RewriteCond %{HTTP_COOKIE} !^.*statamic_static_cache_skip.*$```

> Replace `statamic_static_cache_skip` with the name of your cookie.

**Nginx**

ℹ️ TODO! If you know how to configure Nginx, please let me know or send a pull request. Thanks :)

**IIS**

ℹ️ TODO! If you know how to configure IIS, please let me know or send a pull request. Thanks :)

### Half Measure 

No additional configuration necessary.

⚠️ **Attention!** There is currently an issue that an authenticated user gets a cached page, if available.
This is due to the fact that the authenticated user is not yet available when the `\Statamic\StaticCaching\Middleware\Retrieve` 
middleware gets executed, returning the cached response. We fail to check if the current user is anonymous or authenticated
at this point in the request lifecycle. If you know how to solve this problem, please let me know!
