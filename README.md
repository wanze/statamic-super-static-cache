# Super Static Cache

[![Build Status](https://travis-ci.org/wanze/statamic-super-static-cache.svg?branch=master)](https://travis-ci.org/wanze/statamic-super-static-cache)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![Statamic 2](https://img.shields.io/badge/Statamic-2-orange.svg)](https://statamic.com)

## Features

* Allows to disable static caching for authenticated users. This is important if you serve different markup for anonymous
and authenticated users.
* Enhanced security: Restrict the static cache to only cache whitelisted query strings per path (when using `static_caching_ignore_query_strings=false`).  
* Generate the static cache via command line by using the provided `super_static_cache:warmup` command.

## Installation

1. Download the addon and rename the folder to `SuperStaticCache`
2. Move the `SuperStaticCache` folder to `site/addons`

## Configuration

* **`Disable static caching for authenticated users`** Check to disable static caching for authenticated users.
* **`User roles`** Enter role slugs to disable the cache only for users having a role defined here.
* **`User groups`** Enter group slugs to disable the cache only for users belonging to a group defined here.
* **`Cookie name`** Cookie used to skip static file cache from the reverse proxy.
* **`Whitelisted query strings`** Restrict the static cache to only cache whitelisted query strings per path. 
The value of each query string is validated against a regex pattern.
* **`Warmup collections`** Warmup the static cache for the selected collections.
* **`Warmup taxonomies`** Warmup the static cache for the selected taxonomies.
* **`Warmup request timeout`** Timeout of the requests in seconds. Use `0` to wait indefinitely.

**Whitelisted query strings examples**

```yaml
/products:
  page: '[0-9]+'
```

Cache the `page` query string on the `/products` page, but only if it contains numbers.

```yaml
/categories*:
  page: '[0-9]+'
  sort: '^(desc|asc)$'
```

Cache the `page` and `sort` query string of any page under `/categories` (using `*` as wildcard). Only create a cache
file if `page` contains numbers and `sort` is equal to `desc` or `asc`.

### Configure the [Full Measure](https://docs.statamic.com/caching#full-measure) strategy

This strategy serves a cached file directly from the reverse proxy. We need to extend the configuration of the reverse 
proxy to skip the redirect if the "skip cache" cookie is present.

**Apache**

Add the following rewrite condition in your `.htaccess` below the _Static Caching Proxy_ section:

```RewriteCond %{HTTP_COOKIE} !^.*statamic_static_cache_skip.*$```

> Replace `statamic_static_cache_skip` with the name of your cookie defined in the addon configuration.

**Nginx**

🛠 If you know how to configure Nginx, please let me know or send a pull request.

**IIS**

🛠️ If you know how to configure IIS, please let me know or send a pull request.

### Configure the [Half Measure](https://docs.statamic.com/caching#half-measure) strategy 

No additional configuration necessary.

⚠️ **Attention!** There is currently an issue with this strategy that authenticated users receive cached pages:
The authenticated user is not yet available when the `\Statamic\StaticCaching\Middleware\Retrieve` 
middleware gets executed, returning a cached response. The addon fails to check if the current user is anonymous or 
authenticated at this point in the request lifecycle. This is not a problem if you use the _Full Measure_ strategy.

## Warmup the static cache

The addon provides a handy command `super_static_cache:warmup` to pre-generate the static cache from the command line.
By default, the command creates the cache for all pages. Make sure to specify which collections and taxonomies should
get "warmed up" additionally in the addon's configuration.
