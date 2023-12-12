<?php

namespace OpenAgendaSdk;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * Class HttpClient
 * @package OpenAgendaSdk
 */
class HttpClient
{

  /**
   * @var object
   */
  private $config;

  /**
   * @var string
   */
  private $publicKey;

  /**
   * @var Client
   */
  private $client;

  /**
   * @var array
   */
  private $clientOptions;

  /**
   * HttpClient constructor.
   *
   * @param string $publicKey
   *   OpenAgenda API publicKey.
   * @param array|null $clientOptions
   *  HttpClient options.
   *
   * @throws \Exception
   * @see \OpenAgendaSdk\RequestOptions for a list of available client options.
   */
  public function __construct(string $publicKey, ?array $clientOptions = [])
  {
    $this->config = Config::getConfig();
    $this->publicKey = $publicKey;
    $this->clientOptions = $clientOptions + [RequestOptions::BASE_URI => $this->config->base_uri];
    $this->client = new Client($this->clientOptions);
  }

  /**
   * @param int $status
   * @param string|null $body
   */
  public function setMock(int $status, ?string $body = null)
  {
    if (Config::getEnv() != Config::TEST_ENV) {
      return;
    }
    $mock = new MockHandler([new Response($status, [], $body)]);
    $handler = HandlerStack::create($mock);
    $this->clientOptions += ['handler' => $handler];
    $this->client = new Client($this->clientOptions);
  }

  /**
   * @param string $endpoint
   * @param array|null $placeholders
   * @param array|null $params
   *
   * @return string
   */
  public function request(string $endpoint, ?array $placeholders = [], ?array $params = []): string
  {
    $endpointConfig = $this->config->endpoints->{$endpoint};
    $endpointConfig->headers->key = $this->publicKey;
    $path = $this->makePath($endpointConfig, $placeholders, $params);

    try {
      $request = new Request($endpointConfig->method, $path);
      $response = $this->client->send($request, ['headers' => (array)$endpointConfig->headers]);
    } catch (\Throwable $ex) {
      return \json_encode([]);
    }

    return $response->getBody();
  }

  /**
   * @param \stdClass $endpointConfig
   *   Endpoint config object.
   * @param array|null $placeholders
   *   Endpoint path placeholders as key, value.
   * @param array|null $params
   *   Request url query parameters.
   *
   * @return string
   */
  private function makePath(object $endpointConfig, ?array $placeholders = [], ?array $params = []): string
  {
    $path = $endpointConfig->path;

    // Replace path placeholders.
    foreach ($placeholders as $key => $value) {
      $path = \preg_replace("/{{$key}}/", $value, $path);
    }

    // Add url query.
    if (count($params)) {
      $query = \http_build_query($params);
      $path .= '?' . $query;
    }

    return $path;
  }

}
