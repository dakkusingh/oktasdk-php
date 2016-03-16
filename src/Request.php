<?php

namespace Okta;

use GuzzleHttp\Client as GuzzleClient;
use Okta\Exception as OktaException;
use Exception;

/**
 * Okta\Request class
 *
 * @author Chris Kankiewicz <ckankiewicz@io.com>
 */
class Request
{

    protected $client = null;

    protected $method   = null;
    protected $endpoint = null;
    protected $options  = [];
    protected $assoc    = false;

    /**
     * Okta\Request contructor method
     *
     * @param string $org     Your organization's subdomain (tenant)
     * @param string $apiKey  Okta API key
     * @param array  $headers Array of headers in header_name => value format
     */
    public function __construct(GuzzleClient $client) {
        $this->client = $client;
    }

    /**
     * Set request method
     *
     * @param  string $method HTTP method (GET|POST|PUT|DELETE)
     * @return object         This request object
     */
    public function method($method) {

        if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE'])) {
            throw new Exception('Method parameter not an acceptable HTTP method (GET, POST, PUT, DELETE)');
        }

        $this->method = $method;

        return $this;

    }

    /**
     * Set request endpoint
     *
     * @param  string $endpoint Request endpoint (absolute path or relative to
     *                          the base URI)
     * @return object           This request object
     */
    public function endpoint($endpoint) {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * Convenience function for making an HTTP GET request
     *
     * @param  string $endpoint Request endpoint
     * @return object           OktaRequest object
     */
    public function get($endpoint) {
        $this->method('GET')->endpoint($endpoint);
        return $this;
    }

    /**
     * Convenience function for making an HTTP POST request
     *
     * @param  string $endpoint Request endpoint
     * @return object           OktaRequest object
     */
    public function post($endpoint) {
        $this->method('POST')->endpoint($endpoint);
        return $this;
    }

    /**
     * Convenience function for making an HTTP PUT request
     *
     * @param  string $endpoint Request endpoint
     * @return object           OktaRequest object
     */
    public function put($endpoint) {
        $this->method('PUT')->endpoint($endpoint);
        return $this;
    }

    /**
     * Convenience function for making an HTTP DELETE request
     *
     * @param  string $endpoint Request endpoint
     * @return object           OktaRequest object
     */
    public function delete($endpoint) {
        $this->method('DELETE')->endpoint($endpoint);
        return $this;
    }

    /**
     * Set an arbitrary request option
     *
     * @param  string $key   Option key
     * @param  strgin $value Option value
     * @return object        This request object
     */
    public function option($key, $value) {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * Associative array of query string values to add to the request.
     *
     * @param  array  $query    Associative array of query string values
     * @param  bool   $override If true override all currently stored query
     *                          values with the new values being provided
     * @return object           This request object
     */
    public function query(array $query, $override = false) {

        if (!$override && !empty($this->option['query'])) {
            $query = array_merge($this->option['query'], $query);
        }

        $this->option('query', $query);

        return $this;

    }

    /**
     * The json option is used to easily upload JSON encoded data as the body of
     * a request. A Content-Type header of application/json will be added if no
     * Content-Type header is already present on the message.
     *
     * @param  array  $data     Any PHP type that can be operated on by PHP's
     *                          json_encode() function.
     * @param  bool   $override If true override all currently stored json data
     *                          with the new data being provided
     * @return object           This request object
     */
    public function json(array $data, $override = false) {

        if (!$override && !empty($this->option['json'])) {
            $data = array_merge($this->option['json'], $data);
        }

        $this->option('json', $data);

        return $this;

    }

    /**
     * Alias of $this->json()
     */
    public function data(...$args) {
        $this->json(...$args);
        return $this;
    }

    /**
     * Float describing the timeout of the request in seconds. Use 0 to wait
     * indefinitely (the default behavior).
     *
     * @param  float  $seconds Seconds to wait before request times out
     * @return object          This request object
     */
    public function timeout($seconds) {
        $this->option('timeout', $seconds);
        return $this;
    }

    /**
     * When true, returned objects will be converted into associative arrays.
     *
     * @param  bool   $assoc Wether or not to return associative arrays
     * @return object        This request object
     */
    public function assoc($assoc) {
        $this->assoc = $assoc;
        return $this;
    }

    /**
     * Sends an Okta API request using this request object.
     *
     * @return object Decoded API response object
     */
    public function send() {

        $response = $this->client->request($this->method, $this->endpoint, $this->options);

        $bodyContents = $response->getBody()->getContents();

        if (!in_array($response->getStatusCode(), [200, 201, 202, 203, 204, 205, 206])) {
            throw new OktaException(null, null, 0, json_decode($bodyContents));
        }

        return json_decode($bodyContents, $this->assoc);

    }

}