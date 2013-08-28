<?php

namespace KloutSimple\Test;

use \Guzzle\Http\Message\Response;

class KloutClientTest extends TestCase
{
    function getInstance(array $methods = null)
    {
        $client =  $this->getMockBuilder('KloutSimple\KloutClient')
            ->setConstructorArgs(array('apikeyhere'))
            ->setMethods($methods)
            ->getMock();

        $class = get_class($client);

        $class::$kloutApi = "http://api.klout.com/v2/";

        return $client;
    }

    function testIsError()
    {
        $client = $this->getInstance();

        // The api doc lists the following as error codes:
        $this->assertTrue($client->isError(new Response(403)));
        $this->assertTrue($client->isError(new Response(503)));
        $this->assertTrue($client->isError(new Response(504)));
        $this->assertTrue($client->isError(new Response(404)));

        // Presumably the only valid response?
        $this->assertFalse($client->isError(new Response(200)));
    }

    function testHandleError()
    {
        $response = $this->getMockResponse("unauthorized.json");

        try {
            $this->getInstance()->handleError($response);
        } catch (\Exception $e) {
            $this->assertEquals(
                "Invalid or unprovided API key. All calls must have a key.",
                $e->getMessage()
            );
            return;
        }
        $this->assertFalse(true, "Should have thrown an exception!");
    }

    function testHandleResponse()
    {
        $response = $this->getMockResponse("identity.json");

        $result = $this->getInstance()->handleResponse($response);

        $this->assertEquals("23244", $result->id);
    }

    function testBuildRequest()
    {
        $url = "http://api.klout.com/v2/identity.json/twitter?screenName=Klout&key=apikeyhere";
        $request = $this->getInstance()->buildRequest(
            'get',
            'identity.json/twitter',
            array(
                'screenName' => 'Klout'
            )
        );

        $this->assertEquals($url, $request->getUrl());
    }

    function testBuildRequestPost()
    {
        $url = "http://api.klout.com/v2/identity.json/twitter?key=apikeyhere";
        $request = $this->getInstance()->buildRequest(
            'post',
            'identity.json/twitter',
            array(
                'screenName' => 'Klout'
            )
        );

        $this->assertEquals($url, $request->getUrl());
    }

    function testBuildRequestApiKey()
    {
        $url = "http://api.klout.com/v2/identity.json/twitter";
        $request = $this->getInstance()->buildRequest(
            'post',
            'identity.json/twitter',
            array(
                'screenName' => 'Klout'
            )
        );

        $query = $request->getQuery();

        $this->assertEquals('apikeyhere', $query->get('key'));
    }

    function testQuery()
    {
        $client = $this->getInstance(array('buildRequest'));
        $response = $this->getMockResponse('identity.json');

        $client->expects($this->once())
            ->method('buildRequest')
            ->will($this->returnValue($this->getMockRequest($response)));

        $response = $client->query('get', 'identity.json/twitter',
                                 array('screenName' => 'Klout'));

        $this->assertEquals('23244', $response->id);
    }

    function testQueryError()
    {
        $client = $this->getInstance(array('buildRequest', 'handleError'));
        $response = $this->getMockResponse('unauthorized.json');

        $client->expects($this->once())
            ->method('buildRequest')
            ->will($this->returnValue($this->getMockRequest($response)));

        $client->expects($this->once())
            ->method('handleError');

        $client->query('get', 'identity.json/twitter',
                       array('screenName' => 'Klout'));

    }
}
