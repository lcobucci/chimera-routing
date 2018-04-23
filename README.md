# Chimera - routing

[![Total Downloads](https://img.shields.io/packagist/dt/lcobucci/chimera-routing.svg?style=flat-square)](https://packagist.org/packages/lcobucci/chimera-routing)
[![Latest Stable Version](https://img.shields.io/packagist/v/lcobucci/chimera-routing.svg?style=flat-square)](https://packagist.org/packages/lcobucci/chimera-routing)
[![Unstable Version](https://img.shields.io/packagist/vpre/lcobucci/chimera-routing.svg?style=flat-square)](https://packagist.org/packages/lcobucci/chimera-routing)

![Branch master](https://img.shields.io/badge/branch-master-brightgreen.svg?style=flat-square)
[![Build Status](https://img.shields.io/travis/lcobucci/chimera-routing/master.svg?style=flat-square)](http://travis-ci.org/#!/lcobucci/chimera-routing)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/lcobucci/chimera-routing/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/lcobucci/chimera-routing/?branch=master)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/lcobucci/chimera-routing/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/lcobucci/chimera-routing/?branch=master)

> The term Chimera (_/kɪˈmɪərə/_ or _/kaɪˈmɪərə/_) has come to describe any
mythical or fictional animal with parts taken from various animals, or to
describe anything composed of very disparate parts, or perceived as wildly
imaginative, implausible, or dazzling.

There are many many amazing libraries in the PHP community and with the creation
and adoption of the PSRs we don't necessarily need to rely on full stack
frameworks to create a complex and well designed software. Choosing which
components to use and plugging them together can sometimes be a little
challenging.

The goal of this set of packages is to make it easier to do that (without
compromising the quality), allowing you to focus on the behaviour of your
software.

This particular package provides PSR-15 **middleware** and reusable **request
handlers** that help you to expose command and query handlers using HTTP as the
web mechanism.

## Installation

You probably won't depend directly on this package, but it is available on [Packagist](http://packagist.org/packages/lcobucci/chimera-routing),
and can be installed it using [Composer](http://getcomposer.org):

```shell
composer require lcobucci/chimera-routing
```

### PHP Configuration

In order to make sure that we're dealing with the correct data, we're using `assert()`,
which is a very interesting feature in PHP but not often used. The nice thing
about `assert()` is that we can (and should) disable it in production mode so
that we don't have useless statements.

So, for production mode, we recommend you to set `zend.assertions` to `-1` in your `php.ini`.
For development you should leave `zend.assertions` as `1` and set `assert.exception` to `1`, which
will make PHP throw an [`AssertionError`](https://secure.php.net/manual/en/class.assertionerror.php)
when things go wrong.

Check the documentation for more information: https://secure.php.net/manual/en/function.assert.php

## Components

### Extension points

The packages that extend this library should implement two basic interfaces, they're
used to abstract how each routing library works:

* `Lcobucci\Chimera\Routing\RouteParamsExtractor`: returns the list of parameters
of the matched route
* `Lcobucci\Chimera\Routing\UriGenerator`: generate routes based on the given
arguments

### Route parameters extraction middleware

This middleware uses an implementation of `Lcobucci\Chimera\Routing\RouteParamsExtractor`
to put the parameters of the matched route in a standard attribute, so that other components
can retrieve them.

### Request handlers

* `Lcobucci\Chimera\Handler\CreateAndFetch`: executes a command to create a
resource and immediately a query, returning an unformatted response with the
query result and location header - intended to be used to handle **POST**
requests
* `Lcobucci\Chimera\Handler\CreateOnly`: executes a command to create a resource,
returning an empty response with the location header - intended to be used to
handle **POST** requests (variation of the previous one but can also be used
in asynchronous APIs)
* `Lcobucci\Chimera\Handler\ExecuteAndFetch`: executes a command to modify a
resource and immediately a query, returning an unformatted response with the
query result - intended to be used to handle **PUT** or **PATCH** requests
* `Lcobucci\Chimera\Handler\ExecuteOnly`: executes a command to modify or remove
a resource, returning an empty response - intended to be used to
handle **PUT**, **PATCH**, or **DELETE** requests (can also be used in
asynchronous APIs)
* `Lcobucci\Chimera\Handler\FetchOnly`: executes a query to fetch a resource,
returning an unformatted response with the query result - intended to be used
to handle **GET** requests

## Usage

### Middleware pipeline

As mentioned above content negotiation is not a responsibility of the request
handlers. It's expected that you configure [`lcobucci/content-negotiation-middleware`](https://github.com/lcobucci/content-negotiation-middleware)
to process such task and it should be put in the very beginning of the pipeline,
so that it can process **any** unformatted response.

The `Lcobucci\Chimera\Routing\RouteParamsExtractor` middleware should be put
right after the middleware responsible for matching routes (which changes for
each implementation).

So a middleware pipeline in a [Zend Expressive v3 application](https://github.com/zendframework/zend-expressive-skeleton/blob/3.0.6/config/pipeline.php)
would look like this - considering that all services are properly configured in
the DI container:

```php
<?php
declare(strict_types=1);

use Lcobucci\Chimera\Routing\RouteParamsExtraction;
use Lcobucci\ContentNegotiation\ContentTypeMiddleware;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\Handler\NotFoundHandler;
use Zend\Expressive\Helper\ServerUrlMiddleware;
use Zend\Expressive\Helper\UrlHelperMiddleware;
use Zend\Expressive\MiddlewareFactory;
use Zend\Expressive\Router\Middleware\DispatchMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitHeadMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitOptionsMiddleware;
use Zend\Expressive\Router\Middleware\MethodNotAllowedMiddleware;
use Zend\Expressive\Router\Middleware\RouteMiddleware;
use Zend\Stratigility\Middleware\ErrorHandler;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container) : void {
    $app->pipe(ErrorHandler::class);
    $app->pipe(ServerUrlMiddleware::class);

    // Handles content negotiation, ensuring that the response format is the best one
    // according to what was requested via the `Accept` header
    $app->pipe(ContentTypeMiddleware::class);

    $app->pipe(RouteMiddleware::class);

    // Puts the matched arguments in a standard place (must be executed after the
    // `Zend\Expressive\Router\Middleware\RouteMiddleware` middleware, otherwise
    // matched routed info is not available)
    $app->pipe(RouteParamsExtraction::class);

    $app->pipe(ImplicitHeadMiddleware::class);
    $app->pipe(ImplicitOptionsMiddleware::class);
    $app->pipe(MethodNotAllowedMiddleware::class);
    $app->pipe(UrlHelperMiddleware::class);
    $app->pipe(DispatchMiddleware::class);
    $app->pipe(NotFoundHandler::class);
};
```

### Routes

The main idea of this package is to move your application's behaviour to the
command and query handlers, which allows them to be reused by different delivery
mechanism. This means that the logic of the request handlers are pretty much
reusable, therefore you end up having less code to maintain.

Considering that you have configured the command and query buses with the correct
handlers and also that you have mapped the instances of the request handlers in your
dependency injection container, you just need to configure the PSR-15 router to add
the handlers to the correct endpoint.

In a [Zend Expressive v3 application](https://github.com/zendframework/zend-expressive-skeleton)
the `config/routes.php` would look like this:

```php
<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareFactory;

/**
 * Considering you have the following services in your DI container:
 *
 * - `album.list` => new FetchOnly(
 *     new ExecuteQuery(**query bus**, **message creation strategy**, MyApi\FetchAlbumList::class),
 *     ResponseInterface::class
 * );
 * - `album.find_one` => new FetchOnly(
 *     new ExecuteQuery(**query bus**, **message creation strategy**, MyApi\FindAlbum::class),
 *     ResponseInterface::class
 * );
 * - `album.create` => new CreateOnly(
 *     new ExecuteCommand(**command bus**, **message creation strategy**, MyApi\CreateAlbum::class),
 *     ResponseInterface::class,
 *     'album.find_one',
 *     UriGenerator::class,
 *     IdentifierGenerator::class,
 *     201
 * );
 * - `album.update_album` => new ExecuteAndFetch(
 *     new ExecuteCommand(**command bus**, **message creation strategy**, MyApi\UpdateAlbum::class),
 *     new ExecuteQuery(**query bus**, **message creation strategy**, MyApi\FindAlbum::class),
 *     ResponseInterface::class
 * );
 */
return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container) : void {
    $app->get('/album', 'album.list', 'album.list');
    $app->post('/album', 'album.create', 'album.create');
    $app->get('/album/{id}', 'album.find_one', 'album.find_one');
    $app->patch('/album/{id}', 'album.update_album', 'album.update_album');
};
```

## License

MIT, see [LICENSE file](https://github.com/lcobucci/chimera-routing/blob/master/LICENSE).
