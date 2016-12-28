<?php

namespace Jeylabs\ApiAi;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\RequestOptions as GuzzleRequestOptions;

class ApiAi
{
    const API_AI_BASE_URI = 'https://api.api.ai/v1/';
    protected $client;
    protected $access_token;
    protected $isAsyncRequest = false;
    protected $headers = [];
    protected $promises = [];
    protected $lastResponse;

    public function __construct($access_token, $isAsyncRequest = false, $httpClient = null)
    {
        $this->access_token = $access_token;
        $this->isAsyncRequest = $isAsyncRequest;
        $this->client = $httpClient ?: new Client([
            'base_uri' => self::API_AI_BASE_URI
        ]);
    }

    public function isAsyncRequests()
    {
        return $this->isAsyncRequest;
    }

    public function setAsyncRequests($isAsyncRequest)
    {
        $this->isAsyncRequest = $isAsyncRequest;

        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaders($headers = [])
    {
        $this->headers = $headers;

        return $this;
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    public function query($q)
    {
        if (!session()->has('api_ai')) {
            session()->put('api_ai', str_random());
        }
        $sessionId = session()->get('api_ai');
        $query = [
            "v" => "20150910",
            "lang" => "en",
            "query" => $q,
            "sessionId" => $sessionId,
        ];
        return $this->makeRequest('GET', 'query', $query);
    }

    protected function makeRequest($method, $uri, $query = [])
    {
        $options[GuzzleRequestOptions::QUERY] = $query;
        $options[GuzzleRequestOptions::HEADERS] = $this->getDefaultHeaders();
        if ($this->isAsyncRequest) {
            return $this->promises[] = $this->client->requestAsync($method, $uri, $options);
        }
        $this->lastResponse = $this->client->request($method, $uri, $options);
        return json_decode((string)$this->lastResponse->getBody(), true);
    }

    protected function getDefaultHeaders()
    {
        return array_merge([
            'Authorization' => 'Bearer ' . $this->access_token,
            'Content-Type' => 'application/json; charset=utf-8',
            'api-request-source' => 'php',
        ], $this->headers);
    }

    public function __destruct()
    {
        Promise\unwrap($this->promises);
    }
}
