# Slim v3 controller #

## Introduction ##

Provides controller functionality to Slim Framework v3. Also includes PHPUnit TestCase for testing controllers.

## Installation ##

Composer

```php
"require-dev": {
    "martynbiz/slim3-controller": "dev-master"
}
```

## Usage ##

/app/routes.php

```php
<?php

// index routes (homepage, about, etc)
$app->group('', function () use ($app) {

    $controller = new App\Controller\IndexController($app);

    $app->get('/', $controller('index'));
    $app->get('/contact', $controller('contact'));
});

// create resource method for Slim::resource($route, $name)
$app->group('/articles', function () use ($app) {

    $controller = new App\Controller\ExampleController($app);

    $app->get('', $controller('index'));
    $app->get('/create', $controller('create'));
    $app->post('', $controller('post'));
    $app->get('/{id:[0-9]+}', $controller('show'));
    $app->get('/{id:[0-9]+}/edit', $controller('edit'));
    $app->put('/{id:[0-9]+}', $controller('put'));
    $app->delete('/{id:[0-9]+}', $controller('delete'));
});
```

/app/controllers/ExampleController.php

```php
<?php

namespace App\Controller;

use MartynBiz\Slim3Controller\Controller;

class ExampleController extends Controller
{
    public function index()
    {
        return $this->render('admin/example/index.html', array(
            // data to pass to the view
        ));
    }

    public function show($id)
    {
        return $this->render('admin/example/show.html', array(
            // data to pass to the view
        ));
    }

    public function create()
    {
        return $this->render('admin/example/create.html');
    }

    public function post()
    {
        // handle create

        return $this->redirect('/admin/example');
    }

    public function edit($id)
    {
        return $this->render('admin/example/edit.html', array(
            // data to pass to the view
        ));
    }

    public function update($id)
    {
        // handle update

        return $this->redirect('/admin/example/' . $id);
    }
}
```

## Get method ##

The get() method within controllers is used to get dependencies defined in $app:

/app/dependencies.php

```php
$container['model.example'] = function ($container) {
    return new App\Model\Example();
};
```

/app/controllers/ExampleController.php

```php
.
.
.
class ExampleController extends Controller
{
    public function index()
    {
        // the "get" method is used to retrieve items stored in the Slim container
        $examples = $this->get('model.example')->find();

        // the "render" provides a neat means to pass template and data to $container['view']
        return $this->render('admin/example/index.html', array(
            'examples' => $examples,
        ));
    }
    .
    .
    .
```

## Request ##

getCookie

```
$request->getCookie($name, $defaultValue);
```est->getCookie($name, $defaultValue);
```

## Response ##

### HTTP response codes ###

There is a full list of HTTP response code enums which can be used in controllers
when returning a response:

```
return $this->response->withStatus(Response::HTTP_BAD_REQUEST);
```

See full list here - https://github.com/martynbiz/slim3-controller/blob/master/src/Http/Response.php


## Testing controllers ##

/phpunit.xml

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit backupGlobals="false"
         bootstrap="tests/bootstrap.php">
    <testsuites>
        <testsuite name="Application Test Suite">
            <directory>./tests/</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

/tests/bootstrap.php

```php
// coming soon, still a little bloated
```

/tests/application/controllers/ExampleControllerTest.php

```php
<?php

use SlimMvc\Test\PHPUnit\TestCase;

class ExampleControllerTest extends TestCase
{
    /**
     * @var Slim\Container
     */
    protected $container;

    public function setUp()
    {
        // =========================
        // Instantiate the app and container

        $settings = require APPLICATION_PATH . '/settings.php';
        $app = new \Slim\App($settings);


        // =========================
        // Set up dependencies

        require APPLICATION_PATH . '/dependencies.php';


        // =========================
        // Create test stubs (optional)

        // In some cases, where services have become "frozen", we need to define
        // mocks before they are loaded, so immediately after including dependencies.php is best

        //...
        $this->container['my_dependency'] = ...


        // =========================
        // Register middleware

        require APPLICATION_PATH . '/middleware.php';


        // =========================
        // Register routes

        require APPLICATION_PATH . '/routes.php';

        // store $app for access in test* methods
        $this->app = $app;

        //... fixtures, etc
    }

    public function test_example_route()
    {
        $this->dispatch('/example');

        // mock methods (optional)
        $container = $this->app->getContainer();
        $container['my_dependency']->expects(...

        $this->assertController('articles');
        $this->assertAction('index');
        $this->assertStatusCode(200);
        $this->assertQuery('table#examples');
        $this->assertQueryCount('div.errors', 0);
        // $this->assertRedirects();
        // $this->assertRedirectsTo('...');
    }
}
```

TODO
doc - getCookie
tests - cookies not available, why?
body missing in tests - why??
