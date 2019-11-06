# Super Static Cache

[![Build Status](https://travis-ci.org/wanze/statamic-super-static-cache.svg?branch=master)](https://travis-ci.org/wanze/statamic-super-static-cache)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![Statamic 2](https://img.shields.io/badge/Statamic-2-orange.svg)](https://statamic.com)

## Features

* Allows to disable static caching for authenticated users. This is important if you serve different markup for anonymous
and authenticated users.
* Enhanced security: Restrict the static cache to only cache whitelisted query strings per path (when using `static_caching_ignore_query_strings=false`).  
* Generate the static cache via command line by using the provided `super_static_cache:warmup` command.
* Easier debugging: Flag pages served by the static cache with a customizable comment string in the source code. Default: `<!-- Served by static cache -->`.
* Use the static cache with forms by injecting CSRF tokens via ajax call.

## Installation

1. Download the addon and rename the folder to `SuperStaticCache`
2. Move the `SuperStaticCache` folder to `site/addons`

## Usage

### Disable static caching for authenticated users

First, enable this feature in the addon configuration. You may configure to disable the cache only for specific user roles
or groups. If you are using the [Full Measure](https://docs.statamic.com/caching#full-measure) strategy, you need
to configure the reverse proxy to skip serving the cache if the user is logged in and thus the *skip cache cookie* is present.

**Apache**

Add the following rewrite condition in your `.htaccess` below the _Static Caching Proxy_ section:

```RewriteCond %{HTTP_COOKIE} !^.*statamic_static_cache_skip.*$```

**Nginx**

🛠 If you know how to configure Nginx, please let me know or send a pull request.

**IIS**

🛠️ If you know how to configure IIS, please let me know or send a pull request.

> Replace `statamic_static_cache_skip` with the name of your cookie defined in the addon configuration.

> ⚠️ When using the [Half Measure](https://docs.statamic.com/caching#half-measure) strategy, authenticated users still receive
cached responses. The authenticated user is not yet available when the `\Statamic\StaticCaching\Middleware\Retrieve` 
middleware gets executed, which already returns a cached response. The addon fails to check if the current user is anonymous or 
authenticated at this point in the request lifecycle. This is not a problem if you use the _Full Measure_ strategy.

### Whitelisted query strings

This feature extends the static cache to only cache whitelisted query strings when `static_caching_ignore_query_string`
is set to `false`. You can define valid query strings per path, where the values of the query strings are validated
against a regex pattern.

**Examples**

```yaml
/products:
  page: '[0-9]+'
```

Cache the `page` query string on the `/products` page, but only if it contains numbers.

```yaml
'/categories*':
  page: '[0-9]+'
  sort: '^(desc|asc)$'
```

Cache the `page` and `sort` query string of any page under `/categories` (using `*` as wildcard). Only create a cache
file if `page` contains numbers and `sort` is equal to `desc` or `asc`.

### Warmup the static cache

The addon provides a handy command `super_static_cache:warmup` to pre-generate the static cache from the command line.
By default, the command creates the cache for all pages for each locale. Make sure to specify which collections and
taxonomies should get "warmed up" additionally in the configuration.

### Debug Mode

Enable debugging in the configuration to inject a customizable debug string in source code of cached responses (at the very beginning).
This allows to quickly check if a response has been sent by the static cache.

### Dynamic CSRF tokens

Using forms with static caching is not possible because the CSRF tokens get cached as well. If your form exists only on
a single page, you can workaround this issue by excluding this page from the static cache. This does not work for global
forms (e.g. a newsletter signup in the footer) or if you do not know where forms are rendered. For this case, the addon
offers an API to fetch CSRF tokens and a tag to inject the token in each form's `_token` field.

**Configuration**

1. Enable the feature in the configuration. This will expose an API to fetch CSRF tokens via `/!/SuperStaticCache/token`.
2. Configure the *HTTP referer base url* to verify that API requests origin from your website.
3. Place the `{{ super_static_cache:populate_csrf_tokens }}` tag before the closing `</body>` tag. You may optionally pass a 
`form_selector` parameter which is used to find the Statamic forms in the DOM, e.g. `form_selector="[data-statamic-form]"`.

Finally, check that the ajax calls succeed and the CSRF tokens are injected into the forms.

> ⚠️ Using the HTTP referer base url does not guarantee that the API request origins from your website, because the
HTTP referer header can be faked by the client. 


