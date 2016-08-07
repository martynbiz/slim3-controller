<?php

namespace MartynBiz\Slim3Controller;

use MartynBiz\Slim3Controller\Http\Request;
use MartynBiz\Slim3Controller\Http\Response;
use Psr\Http\Message\UriInterface;

/**
 * This class provides a base for all controllers, allowing actions to be dispatched to the appropriate
 * methods. Additionally this class gives access to Slim and the current Request and Response contexts.
 */
abstract class Controller
{
    /**
     * @var \Slim\App
     */
    protected $app;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @param \Slim\App $app
     */
    public function __construct(\Slim\App $app)
    {
        $this->app = $app;
    }

    /**
     * This method allows use to return a callable that calls the action for
     * the route.
     *
     * @param string $actionName Name of the action method to call
     * @return \Closure
     */
    public function __invoke($actionName)
    {
        $app = $this->app;
        $controller = $this;

        $callable = function ($request, $response, $args) use ($app, $controller, $actionName) {

            $container = $app->getContainer();

            if (method_exists($controller, 'setRequest')) {
                $controller->setRequest($request);
            }

            if (method_exists($controller, 'setResponse')) {
                $controller->setResponse($response);

            }

            if (method_exists($controller, 'init')) {
                $controller->init();
            }

            // Store the name of the controller and action so we can assert during tests
            $controllerName = get_class($controller); // eg. CrSrc\Controller\Admin\ArticlesController
            $controllerName = strtolower($controllerName); // eg. crsrc\controller\admin\articlescontroller
            $controllerNameParts = explode('\\', $controllerName);
            $controllerName = array_pop($controllerNameParts); // eg. articlescontroller
            preg_match('/(.*)controller$/', $controllerName, $result); // eg. articles?
            $controllerName = $result[1];

            // These values will be useful when testing, but not included with the
            // Slim\Http\Response. Instead use SlimMvc\Http\Response
            if (method_exists($response, 'setControllerName')) {
                $response->setControllerName($controllerName);
            }

            if (method_exists($response, 'setControllerClass')) {
                $response->setControllerClass(get_class($controller));
            }

            if (method_exists($response, 'setActionName')) {
                $response->setActionName($actionName);
            }

            return call_user_func_array(array($controller, $actionName), $args);
        };

        return $callable;
    }

    // Optional setters

    /**
     * Sets the current request for the controller
     *
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * Sets the current response for the controller
     *
     * @param Response $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * Render the view from within the controller
     *
     * @todo Should this be here?
     *
     * @param string $file Name of the template/ view to render
     * @param array $args Additional variables to pass to the view
     * @param Response
     */
    protected function render($file, $args = array())
    {
        $container = $this->app->getContainer();

        return $container->view->render($this->response, $file, $args);
    }

    /**
     * Return true if XHR request
     */
    protected function isXhr()
    {
        return $this->request->isXhr();
    }

    /**
     * Get the POST params
     *
     * @return array
     */
    protected function getPost()
    {
        $post = array_diff_key($this->request->getParams(), array_flip(array(
            '_METHOD',
        )));

        return $post;
    }

    /**
     * Get the POST params
     *
     * @param string $name
     * @param array|null $default
     * @return array
     */
    protected function getQueryParam($name, $default = null)
    {
        return $this->request->getQueryParam($name, $default);
    }

    /**
     * Get the POST params
     *
     * @return array
     */
    protected function getQueryParams()
    {
        return $this->request->getQueryParams();
    }

    /**
     * Shorthand method to get dependency from container
     *
     * @param string $name
     * @return mixed
     */
    protected function get($name)
    {
        return $this->app->getContainer()->get($name);
    }

    /**
     * Redirect.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param  string|UriInterface $url The redirect destination.
     * @param  int $status The redirect HTTP status code.
     * @return Response
     */
    protected function redirect($url, $status = 302)
    {
        return $this->response->withRedirect($url, $status);
    }

    /**
     * Pass on the control to another action. Of the same class (for now)
     *
     * @param string $actionName The redirect destination.
     * @param array $data
     * @return Controller
     */
    public function forward($actionName, $data = array())
    {
        // Update the action name that was last used
        if (method_exists($this->response, 'setActionName')) {
            $this->response->setActionName($actionName);
        }

        return call_user_func_array(array($this, $actionName), $data);
    }
}
