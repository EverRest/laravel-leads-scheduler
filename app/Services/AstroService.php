<?php
declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class AstroService
{

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
    protected array $config = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = Config::get('services.astro');
        $this->domain = Arr::get($this->config, 'url');
        $this->token = Arr::get($this->config, 'token');
    }

    /**
     * @param string $iso2
     *
     * @return false|mixed
     * @throws Exception
     */
    public function getCountryByISO2(string $iso2): mixed
    {
        $url = "{$this->domain}countries";
        $endpoint = $this->setToken($url);
        $response = Http::get($endpoint);
        if ($response->failed()) {
            throw new Exception('Proxy returns error.');
        }
        $json = $response->json();
        foreach (Arr::get($json, 'data') as $c) {
            if (strtoupper(Arr::get($c, 'iso2')) === strtoupper($iso2)) {
                return Arr::get($c, 'name');
            }
        }

        return false;
    }

    /**
     * @return Collection
     * @throws Exception
     */
    public function getAvailablePorts(): Collection
    {
        $url = "{$this->domain}ports";
        $endpoint = $this->setToken($url);
        $response = Http::get("$endpoint&status=active");
        if ($response->failed()) {
            throw new Exception('Proxy returns error.');
        }
        $json = $response->json();

        return Collection::make(Arr::get($json, 'data.ports', []));
    }

    /**
     * @param int $portId
     *
     * @return mixed
     * @throws Exception
     */
    public function changeIpOnPort(int $portId): mixed
    {
        $url = "{$this->domain}ports/$portId/newip";
        $endpoint = $this->setToken($url);
        $response = Http::get($endpoint);
        if ($response->failed()) {
            throw new Exception('Proxy returns error.');
        }
        $json = $response->json();

        return Arr::get($json, 'ip');
    }

    /**
     * @param array $proxy
     *
     * @return string
     * @throws Exception
     */
    public function checkMyIp(array $proxy): string
    {
        $url = "{$this->domain}checkMyIp";
        $endpoint = $this->setToken($url);
        $response = Http::withHeaders(
            [
                'proxy' => Arr::get($proxy, 'host') . ':' . Arr::get($proxy, 'port'),
                'proxy-auth' => Arr::get($proxy, 'username') . ':' . Arr::get($proxy, 'password'),
            ]
        )
            ->get($endpoint);
        if ($response->failed()) {
            throw new Exception('Proxy returns error.');
        }
        $json = $response->json();

        return Arr::get($json, 'ip');
    }

    /**
     * @param array $port
     *
     * @return array
     * @throws Exception
     */
    public function setProxy(array $port): array
    {
        return [
            'protocol' => 'http',
            'country' => Arr::get($port, 'country'),
            'id' => Arr::get($port, 'id'),
            'ip' => Arr::get($port, 'node.ip'),
            'host' => Arr::get($port, 'node.ip'),
            'port' => Arr::get($port, 'ports.http'),
            'username' => Arr::get($port, 'access.login'),
            'password' => Arr::get($port, 'access.password'),
        ];
    }

    /**
     * @param string $country
     *
     * @return Collection
     * @throws Exception
     */
    public function createPortByCountry(string $country): Collection
    {
        $url = "{$this->domain}ports";
        $endpoint = $this->setToken($url);
        $data = [
            "name" => "API",
            "network" => "Mobile",
            "country" => $country,
            "rotation_by" => "link",
            "is_unlimited" => "0",
            "volume" => "0.1",
            "username" => "api_username",
            "password" => "api_password",
        ];
        $response = Http::post($endpoint, $data);
        if ($response->failed()) {
            throw new Exception('Proxy returns error.');
        }
        $json = $response->json();

        return Collection::make(Arr::get($json, 'data', []));
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function setToken(string $url): string
    {
        return "$url?token=$this->token";
    }
}
