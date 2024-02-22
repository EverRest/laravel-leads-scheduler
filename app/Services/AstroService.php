<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Lead;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            Log::error($iso2 . ' getCountryByISO2: Proxy returns error.');
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
            Log::error('getAvailablePorts:Proxy returns error.');
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
    public function newIp(int $portId): mixed
    {
        $url = "{$this->domain}ports/$portId/newip";
        $endpoint = $this->setToken($url);
        $response = Http::get($endpoint);
        if ($response->failed()) {
            Log::error('newIp: Proxy returns error.');
        }
        $json = $response->json();

        return Arr::get($json, 'data.ip');
    }

    /**
     * @param array $proxy
     *
     * @return string
     * @throws Exception
     */
    public function checkMyIp(array $proxy): string
    {
        $url = "https://bitcoin-adw.com/api/v1/checkMyIP";
        $endpoint = $this->setToken($url);
        $response = Http::withHeaders(
            [
                'proxy' => Arr::get($proxy, 'host') . ':' . Arr::get($proxy, 'port'),
                'proxy-auth' => Arr::get($proxy, 'username') . ':' . Arr::get($proxy, 'password'),
            ]
        )
            ->get($endpoint);
        if ($response->failed()) {
            Log::error('checkMyIp: Proxy returns error with status ' . $response->status());
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
     * @param Lead $lead
     *
     * @return array
     * @throws Exception
     */
    public function createPortByLead(string $country, Lead $lead): array
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
            "username" => $lead->first_name,
            "password" => $lead->password,
        ];
        $response = Http::post($endpoint, $data);
        if ($response->failed()) {
            Log::error($lead->id . ' createPortByLead Proxy returns error.');
        }
        $json = $response->json();

        return Arr::get($json, 'data.0', []);
    }

    /**
     * @param string $country
     * @param Lead $lead
     *
     * @return array
     * @throws Exception
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
}
