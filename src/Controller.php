<?php
namespace MartynBiz\Slim3Controller;

// use Psr\Http\Message\UriInterface;
use Slim\Container;

abstract class Controller
{
    /**
     * @var Slim\Container
     */
    protected $container;

    /**
     * @param Slim\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * This method allows use to return a callable that calls the action for
     * the route.
     * @param $actionName
     * @return \Closure
     * @internal param string $actionName Name of the action method to call
     */
    public function __invoke($actionName)
    {
        $container = $this->container;
        $controller = $this;

        $callable = function ($request, $response, $args) use ($container, $controller, $actionName) {

            // if (method_exists($controller, 'setRequest')) {
            //     $controller->setRequest($request);
            // }
            // if (method_exists($controller, 'setResponse')) {
            //     $controller->setResponse($response);
            // }
            // if (method_exists($controller, 'init')) {
            //     $controller->init();
            // }

            // store the name of the controller and action so we can assert during tests
            $controllerName = get_class($controller); // eg. CrSrc\Controller\Admin\ArticlesController
            $controllerName = strtolower($controllerName); // eg. crsrc\controller\admin\articlescontroller
            $controllerNameParts = explode('\\', $controllerName);
            $controllerName = array_pop($controllerNameParts); // eg. articlescontroller
            preg_match('/(.*)controller$/', $controllerName, $result); // eg. articles?
            $controllerName = $result[1];

            // these values will be useful when testing, but not included with the
            // Slim\Http\Response. Instead use Slim3Controller\Http\Response
            if (method_exists($response, 'setControllerClass')) {
                $response->setControllerClass( get_class($controller) );
            }
            if (method_exists($response, 'setControllerName')) {
                $response->setControllerName($controllerName);
            }
            if (method_exists($response, 'setActionName')) {
                $response->setActionName($actionName);
            }

            return call_user_func_array(array($controller, $actionName), $args);
        };

        return $callable;
    }

    // public function setRequest($request)
    // {
    //     $this->get('request') = $request;
    // }
    //
    // public function setResponse($response)
    // {
    //     $this->response = $response;
    // }

    // /**
    //  * Render the view from within the controller
    //  * @param string $file Name of the template/ view to render
    //  * @param array $args Additional variables to pass to the view
    //  * @param Response?
    //  * TODO should this be here?
    //  */
    // protected function render($file, $args=array())
    // {
    //     // $container = $this->app->getContainer();
    //
    //     // return $container->renderer->render($this->response, $file, $args);
    //     return $this->container->view->render($this->response, $file, $args);
    // }

    // /**
    //  * Return true if XHR request
    //  */
    // protected function isXhr()
    // {
    //     return $this->get('request')->isXhr();
    // }

    /**
     * Get the POST params
     */
    protected function getPost()
    {
        // we don't require _METHOD
        $post = array_diff_key($this->get('request')->getParams(), array_flip(array(
            '_METHOD',
        )));

        return $post;
    }

    /**
     * Get the POST params
     */
    protected function getQueryParams()
    {
        return $this->get('request')->getQueryParams();
    }

    /**
     * Get a single param
     */
    protected function getQueryParam($name, $default=null)
    {
        $params = $this->get('request')->getQueryParams();

        return (isset($params[$name])) ? $params[$name] : $default;
    }

    /**
     * Shorthand method to get dependency from container
     * @param $name
     * @return mixed
     */
    protected function get($name)
    {
        return $this->container->get($name);
    }

    /**
     * Redirect.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param  string|UriInterface $url    The redirect destination.
     * @param  int                 $status The redirect HTTP status code.
     * @return Response
     */
    protected function redirect($url, $status = 302)
    {
        return $this->get('response')->withRedirect($url, $status);
    }

    /**
     * Pass on the control to another action. Of the same class (for now)
     *
     * @param  string $actionName The redirect destination.
     * @param array $data
     * @return Response
     * @internal param string $status The redirect HTTP status code.
     */
    public function forward($actionName, $data=array())
    {
        // update the action name that was last used
        if (method_exists($this->get('response'), 'setActionName')) {
            $this->get('response')->setActionName($actionName);
        }

        return call_user_func_array(array($this, $actionName), $data);
    }
}
