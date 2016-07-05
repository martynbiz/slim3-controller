<?php
namespace MartynBiz\Slim3Controller;

use Interop\Container\ContainerInterface;

abstract class Controller {

    /**
     * @var Interop\Container\ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container) {
       $this->container = $container;
    }

    /**
     * Shorthand method to get dependency from container
     * @param $name
     * @return mixed
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     * @param  string|UriInterface $url    The redirect destination.
     * @param  int                 $status The redirect HTTP status code.
     * @return Psr\Http\Message\ResponseInterface
     */
    protected function redirect($url, $status = 302)
    {
        $container = $this->getContainer();
        return $container->response->withRedirect($url, $status);
    }

    /**
     * Pass on the control to another action. Of the same class (for now)
     * @param  string $actionName The forward destination.
     * @param array $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function forward($actionName, $data=array())
    {
        // update the action name that was last used
        if (method_exists($this->response, 'setActionName')) {
            $this->response->setActionName($actionName);
        }

        return call_user_func_array(array($this, $actionName), $data);
    }
}
