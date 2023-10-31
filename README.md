# Yii Middleware
The package provides middleware classes that implement [PSR-15](https://www.php-fig.org/psr/psr-15/#12-middleware):

- [`AccessChecker`](#accesschecker).
- [`GoBack`](#goback).

For more information on how to use middleware in the [Yii Framework](https://www.yiiframework.com/), see the
[Yii middleware guide](https://github.com/yiisoft/docs/blob/master/guide/en/structure/middleware.md).

## Requirements
- PHP 8.0 or higher.

## Installation
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist beastbytes/yii-middleware
```

or add

```json
"beastbytes/yii-middleware": "^1.0.0"
```

to the `require` section of your composer.json.


## General usage

All classes are separate implementations of [PSR 15](https://github.com/php-fig/http-server-middleware)
middleware and don't interact with each other in any way.

### `AccessChecker`

Checks that the current user has permission to access the current route; use in the route configuration.

```php

return [
    Route::get('/') // no checking
         ->action([AppController::class, 'index'])
         ->name('app.index'),
         
    Route::methods([Method::GET, Method::POST], '/login') // only allow if user is not logged in
         ->middleware(
             fn (AccessChecker $accessChecker) => $accessChecker->withPermission('isGuest')
         )
        ->action([AuthController::class, 'login'])
        ->name('auth.login'),
        
    Route::get('/my-account') // only allow if user is logged in
         ->middleware(
             fn (AccessChecker $accessChecker) => !$accessChecker->withPermission('isGuest')
         )
        ->action([UserController::class, 'view'])
        ->name('user.view'),
        
    Route::get('/post/create') // only allow if user create posts
         ->middleware(
             fn (AccessChecker $accessChecker) => !$accessChecker->withPermission('createPost')
         )
        ->action([PostController::class, 'create'])
        ->name('post.create'),
];
```

### `GoBack`
Stores the current route in the session so that it can be returned to; typically used after a user logs in.

Routes to be ignored - i.e. not stored in the session - can be added so that they do not overwrite the route to go 
back to; typically the login route is added.

In the router dependency injection configuration:

```php
use BeastBytes\Yii\Middleware\GoBack;
use Yiisoft\Config\Config;
use Yiisoft\DataResponse\Middleware\FormatDataResponse;
use Yiisoft\Router\Group;
use Yiisoft\Router\RouteCollection;
use Yiisoft\Router\RouteCollectionInterface;
use Yiisoft\Router\RouteCollectorInterface;

/** @var Config $config */
/** @var array $params */

return [
    RouteCollectionInterface::class => static function (RouteCollectorInterface $collector) use ($config, $params) {
        $collector
            ->middleware(FormatDataResponse::class)
            ->middleware([
                'class' => GoBack::class,
                'addIgnoredRoutes()' => [
                    $params['user']['loginRoute']
                ]
            ])
            ->addGroup(
                Group::create(null)
                    ->routes(...$config->get('routes'))
            );

        return new RouteCollection($collector);
    },
];
```

In the controller where you want to return to a previous URL

```php
use BeastBytes\Yii\Middleware\GoBackMiddleware;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Status;

    public function __construct(
        private ResponseFactoryInterface $responseFactory
    ) {
    }
    
    // Actions

    private function goBack(): ResponseInterface
    {
        return $this
            ->responseFactory
            ->createResponse(Status::SEE_OTHER)
            ->withAddedHeader('Location', 
                $this
                    ->session
                    ->get(GoBack::URL_PARAM)
            )
        ;
    }
```

Call goBack() from an action.

## Testing
### Unit testing
The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
./vendor/bin/phpunit
```

### Mutation testing
The package tests are checked with [Infection](https://infection.github.io/) mutation framework with
[Infection Static Analysis Plugin](https://github.com/Roave/infection-static-analysis-plugin). To run it:

```shell
./vendor/bin/roave-infection-static-analysis-plugin
```

### Static analysis
The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```shell
./vendor/bin/psalm
```

For license information check the [LICENSE](LICENSE.md) file.
