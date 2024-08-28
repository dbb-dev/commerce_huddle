<?php

namespace DBB\CommerceHuddle;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

class Client
{
    private $httpClient;

    private $webhook = '';

    public function __construct($webhook)
    {
        $this->httpClient = new GuzzleClient();
        $this->webhook = $webhook;
    }

    /**
     * 
     * @param array $data
     * @throws GuzzleException|JsonException
     * @return ResponseInterface
     */
    public function send(array $data): ResponseInterface
    {
        return $this->httpClient->post($this->webhook, [
            'body' => json_encode($data, JSON_THROW_ON_ERROR),
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);
    }
}
