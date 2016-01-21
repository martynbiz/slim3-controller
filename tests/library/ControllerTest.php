<?php

use MartynBiz\Slim3Controller\Controller;

/**
 * We need a test class to extend the abstract Controller class
 */
class UsersController extends Controller
{

}

class ControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * This is passed into controller, and can be mocked
     */
    protected $appStub;

    public function setUp()
    {
        // Create a stub for the Slim\App class.
        $this->appStub = $this->getMockBuilder('Slim\App')
             ->disableOriginalConstructor()
             ->getMock();
    }

    public function testInitialization()
    {
        $controller = new UsersController($this->appStub);

        $this->assertTrue($controller instanceof UsersController); // yey!
    }
}
