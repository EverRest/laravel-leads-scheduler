<?php
declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Config;

class Sdk
{
    /**
     * @var Client
     */
    protected Client $httpClient;

    /**
     * @var string $domain
     */
    protected string $domain = '';

    /**
     * @var string $token
     */
    protected string $token = '';

    /**
     * @var array
     */
    protected array$config = [];

    public function __construct(Client $httpClient, string $config = '')
    {
        $this->config = Config::get($config);
        $this->httpClient = $httpClient;
    }

    /**
     * @param string $endpoint
     * @param string $method
     * @param array $data
     * @param array $options
     *
     * @return mixed
     * @throws GuzzleException
     */
    protected function sendRequest(string $endpoint, string $method, array $data = [], array $options = []): mixed
    {
        $url = $this->domain . $endpoint;
        $options['query'] = $data;
        if ($method === 'GET') {
            $options['query'] = $data;
        } else {
            $options['json'] = $data;
        }
        $response = $this->httpClient->request($method, $url, $options);

        return $response->getBody()->getContents();
    }
}
