<?php

namespace KloutSimple\Test;

use \Guzzle\Plugin\Mock\MockPlugin;
use \Guzzle\Http\Message\Response;

class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Fixture directory relative to this file.
     *
     * @var string
     */
    public static $FIXTURE_DIR = "/../Fixtures/";

    /**
     * Get a mock response object.
     *
     * @param string $path Mock file's path relative to FIXTURE_DIR
     *
     * @return Guzzle\Http\Response
     */
    public function getMockResponse($path)
    {
        // Compile complete path
        $path = __DIR__ . static::$FIXTURE_DIR . '/' . $path;

        return MockPlugin::getMockFile($path);
    }

    public function getMockRequest(Response $response)
    {
        // Mock the request, removing all of the important bits.
        $request = $this->getMockBuilder("Guzzle\Http\Message\Request")
            ->disableOriginalConstructor()
            ->setMethods(array('send'))
            ->getMock();

        // Ensure that it returns $response
        $request->expects($this->any())
            ->method('send')
            ->will($this->returnValue($response));

        return $request;
    }
}
