<?php
namespace MartynBiz\Slim3Controller;

use Interop\Container\ContainerInterface;

abstract class Controller {
    protected $ci;

    //Constructor
    public function __construct(ContainerInterface $ci) {
       $this->ci = $ci;
    }

    public function __call($name, $arguments) { // $arguments = [$req, $res, $args]
        call_user_func_array ([$this, $name], $arguments[2]);
    }

    /**
     * Shorthand method to get dependency from container
     * @param $name
     * @return mixed
     */
    protected function getContainer()
    {
        return $this->ci;
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
        return $this->response->withRedirect($url, $status);
    }

    /**
     * Pass on the control to another action. Of the same class (for now)
     * @param  string $actionName The redirect destination.
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
