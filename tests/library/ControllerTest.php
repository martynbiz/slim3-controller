<?php

use MartynBiz\Slim3Controller\Controller;

class ControllerTest extends PHPUnit_Framework_TestCase
{
    protected $controller;

    public function setUp()
    {
        $this->controller = new Validator();
    }

    public function testInitialization()
    {
        $controller = new Controller();

        $this->assertTrue($controller instanceof Controller); // yey!
    }
}
