<?php
/**
 * This file is part of KloutSimple.
 *
 * PHP Version 5+
 *
 * @category API
 * @package  KloutSimple
 * @author   Clark Fischer <clark.fischer@gmail.com>
 * @license  MIT http://opensource.org/licenses/MIT
 * @link     https://github.com/clarkf/KloutSimple
 */

namespace KloutSimple;

use \Guzzle\Http\Client;
use \Guzzle\Http\Message\Response;

/**
 * The `KloutClient` class is responsible for low-level communication with the
 * actual Klout API.
 *
 * @category API
 * @package  KloutSimple
 * @author   Clark Fischer <clark.fischer@gmail.com>
 * @license  MIT http://opensource.org/licenses/MIT
 * @link     https://github.com/clarkf/KloutSimple
 */
abstract class KloutClient
{
    /**
     * The base URL for the Klout API.  Override this to specify the version.
     *
     * @var string
     */
    public static $kloutApi;

    /**
     * The API key used to communicate with the Klout API for this instance.
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Create a new KloutClient instance.
     *
     * @param string $apiKey The API Key to communicate with.
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = new Client(static::$kloutApi);
    }

    /**
     * Build a HTTP Request to the API endpoint.
     *
     * @param string $method     HTTP Verb (GET, POST, etc)
     * @param string $url        Endpoint
     * @param array  $parameters Parameters to pass to the API
     *
     * @return Guzzle\Http\Request A request object
     */
    public function buildRequest($method, $url, array $parameters = array())
    {
        $headers = array();
        $options = array();

        if ($method == 'get') {
            $options['query'] = $parameters;
        } else {
            $options['params'] = $parameters;
        }

        if (!isset($options['query'])) {
            $options['query'] = array();
        }

        $options['query']['key'] = $this->apiKey;
        $options['exceptions'] = false;

        return $this->client->createRequest(
            $method,
            $url,
            $headers,
            null,
            $options
        );
    }

    /**
     * Perform a Klout API query.
     *
     * @param string $method     The HTTP verb to use when performing this
     *                           request (i.e. 'GET', 'POST', etc).
     * @param string $resource   The Path to the requested resource.
     * @param array  $parameters The paramter hash. For GET requests, this will
     *                           be converted to a querystring.
     *
     * @return mixed The API result.
     */
    public function query($method, $resource, array $parameters)
    {
        $request = $this->buildRequest($method, $resource, $parameters);

        $response = $request->send();

        if ($this->isError($response)) {
            $this->handleError($response);
        }

        return $this->handleResponse($response);
    }

    /**
     * Determine whether or not a `Response` is an error.
     *
     * @param Response $response The response to check
     *
     * @return boolean `true` if it is an error
     */
    public function isError(Response $response)
    {
        return !$response->isSuccessful();
    }

    /**
     * Handle an error.  The current, unsophisticated method is to just throw
     * a plain old exception.
     *
     * @param Response $response The erroring response to handle
     *
     * @return void
     * @throws Exception
     */
    public function handleError(Response $response)
    {
        $data = json_decode($response->getBody());

        throw new \Exception($data->description);
    }

    /**
     * Handle a response.  The default implementation is to simply parse its
     * body from JSON into a `stdObject` and return that.
     *
     * @param Response $response The response object to handle
     *
     * @return mixed JSON Response
     */
    public function handleResponse(Response $response)
    {
        $body = json_decode($response->getBody());
        return $body;
    }
}
