<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Lead;
use ArrayAccess;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class AstroService
{
    /**
     * @var string|array|ArrayAccess|mixed
     */
    protected string $domain = '';

    /**
     * @var string|array|ArrayAccess|mixed
     */
    protected string $token = '';

    /**
     * @var array
     */
    protected array $config = [];

    /**
     *
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
     * @return mixed
     */
    public function getCountryByISO2(string $iso2): mixed
    {
        $url = "{$this->domain}countries";
        $endpoint = $this->setToken($url);
        $response = $this->getResponse($endpoint);
        return $this->findCountryInResponse($response, $iso2);
    }

    /**
     * @return Collection
     */
    public function getAvailablePorts(): Collection
    {
        $url = "{$this->domain}ports";
        $endpoint = $this->setToken($url);
        $response = $this->getResponse("$endpoint&status=active");
        return Collection::make(Arr::get($response, 'data.ports', []));
    }

    /**
     * @param int $portId
     *
     * @return mixed
     */
    public function newIp(int $portId): mixed
    {
        $url = "{$this->domain}ports/$portId/newip";
        $endpoint = $this->setToken($url);
        $response = $this->getResponse($endpoint);
        return Arr::get($response, 'data.ip');
    }

    /**
     * @param array $proxy
     *
     * @return string
     */
    public function checkMyIp(array $proxy): string
    {
        $url = "https://bitcoin-adw.com/api/v1/checkMyIP";
        $endpoint = $this->setToken($url);
        $response = $this->getResponseWithProxy($endpoint, $proxy);
        return Arr::get($response, 'ip');
    }

    /**
     * @param array $port
     *
     * @return array
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
     * @param Lead $lead
     *
     * @return array
     */
    public function createPortByLead(string $country, Lead $lead): array
    {
        $url = "{$this->domain}ports";
        $endpoint = $this->setToken($url);
        $data = $this->getPortData($country, $lead);
        $response = $this->postResponse($endpoint, $data);
        return Arr::get($response, 'data.0', []);
    }

    /**
     * @param string|int $portId
     *
     * @return bool
     */
    public function deletePort(string|int $portId): bool
    {
        $url = "{$this->domain}ports/{$portId}";
        $endpoint = $this->setToken($url);
        $response = Http::delete($endpoint);
        if ($response->failed()) {
            Log::error($portId . ' deletePort Proxy returns error.');
        }

        return $response->successful();
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

    /**
     * @param string $endpoint
     *
     * @return array
     */
    private function getResponse(string $endpoint): array
    {
        $response = Http::get($endpoint);
        if ($response->failed()) {
            Log::error('AstroService: Proxy returns error.');
        }
        return $response->json();
    }

    /**
     * @param string $endpoint
     * @param array $proxy
     *
     * @return array
     */
    private function getResponseWithProxy(string $endpoint, array $proxy): array
    {
        $response = Http::withHeaders(
            [
                'proxy' => Arr::get($proxy, 'host') . ':' . Arr::get($proxy, 'port'),
                'proxy-auth' => Arr::get($proxy, 'username') . ':' . Arr::get($proxy, 'password'),
            ]
        )
            ->get($endpoint);
        if ($response->failed()) {
            Log::error('AstroService: Proxy returns error with status ' . $response->status());
        }
        return $response->json();
    }

    /**
     * @param string $endpoint
     * @param array $data
     *
     * @return array
     */
    private function postResponse(string $endpoint, array $data): array
    {
        $response = Http::post($endpoint, $data);
        if ($response->failed()) {
            Log::error('AstroService: Proxy returns error.');
        }
        return $response->json();
    }

    /**
     * @param array $response
     * @param string $iso2
     *
     * @return mixed
     */
    private function findCountryInResponse(array $response, string $iso2): mixed
    {
        foreach (Arr::get($response, 'data') as $c) {
            if (strtoupper(Arr::get($c, 'iso2')) === strtoupper($iso2)) {
                return Arr::get($c, 'name');
            }
        }

        return false;
    }

    /**
     * @param string $country
     * @param Lead $lead
     *
     * @return array
     */
    private function getPortData(string $country, Lead $lead): array
    {
        return [
            "name" => "API",
            "network" => "Mobile",
            "country" => $country,
            "rotation_by" => "link",
            "is_unlimited" => "0",
            "volume" => "0.1",
            "username" => $lead->first_name,
            "password" => $lead->password,
        ];
    }
}
