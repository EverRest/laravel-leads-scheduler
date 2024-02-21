<?php
declare(strict_types=1);

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

final class AstroProxyService extends Sdk
{
    public function __construct(Client $httpClient, array $config = [])
    {
        $config = Config::get('services.astro');
        parent::__construct($httpClient, $config);
    }

    /**
     * @param string $country
     * @param $black_list
     *
     * @return array|null
     * @throws GuzzleException
     * @throws Exception
     */
    public function checkNeededProxy(string $country, $black_list): array|null
    {
        $test = false;
        $country = $this->getCountryByISO2($country);
        if (!$country) {
            exit(json_encode(["status" => 400, "message" => "Немає достпуних проксі для цієї країни"]));
        }

        $needProxy = false;
        $response = $this->sendRequest('ports', 'GET', ['token' => $this->token]);
        $json = json_decode($response, true);

        if ($test) {
            var_dump($json);
        }

        if (isset($json["status"]) && $json["status"] === "ok") {
            $needProxy = $this->setNeedProxy($json["data"]["ports"], $country);

            if ($test) {
                var_dump($needProxy);
                var_dump($black_list);
            }

            if (!$needProxy) {
                $createProxy = $this->createPort($country);

                if ($test) {
                    var_dump($createProxy, $country);
                }

                if (isset($createProxy["status"]) && $createProxy["status"] === "error") {
                    $message = isset($createProxy["errors"]) ? json_encode($createProxy["errors"]) :
                        (isset($createProxy['message']) ? $createProxy['message'] : json_encode($createProxy));

                    exit(json_encode(["status" => 400, "message" => $message, "proxy_id" => json_encode($createProxy)]));
                }

                if (isset($createProxy["status"]) && $createProxy["status"] === "ok") {
                    sleep(1);
                    $needProxy = $this->checkNeededProxy($country, $black_list);
                }
            }

            if (isset($needProxy["ip"]) && in_array($needProxy["ip"], $black_list)) {
                if ($test) {
                    var_dump($needProxy["ip"]);
                }

                $res = $this->changeIpOnPort($needProxy["id"]);

                if ($test) {
                    var_dump($res);
                }

                if (isset($res['status']) && $res['status'] === "error") {
                    $re = $this->removePort($needProxy["id"]);
                    $createProxy = $this->createPort($country);

                    if ($test) {
                        var_dump($createProxy, $country);
                    }

                    if (isset($createProxy["status"]) && $createProxy["status"] === "error") {
                        $message = isset($createProxy["errors"]) ? json_encode($createProxy["errors"]) :
                            (isset($createProxy['message']) ? $createProxy['message'] : json_encode($createProxy));

                        exit(json_encode(["status" => 400, "message" => $message]));
                    }

                    if (isset($createProxy["status"]) && $createProxy["status"] === "ok") {
                        sleep(1);
                        $needProxy = $this->checkNeededProxy($country, $black_list);
                    }
                } else {
                    sleep(40);
                    $needProxy["ip"] = $this->checkMyIP($needProxy);

                    if ($test) {
                        var_dump($needProxy["ip"]);
                    }
                }
            }
        }

        return $needProxy;
    }

    /**
     * @param $list
     * @param $country
     *
     * @return array
     * @throws GuzzleException
     */
    function setNeedProxy($list, $country): array
    {
        $needProxy = false;
        foreach ($list as $item) {
            if ($item['country'] === $country) {
                $needProxy = [
                    "protocol" => "http",
                    "country" => $item['country'],
                    "id" => $item['id'],
                    "ip" => $item['node']['ip'],
                    "host" => $item['node']['ip'],
                    "port" => $item['ports']['http'],
                    "username" => $item['access']['login'],
                    "password" => $item['access']['password'],
                ];
            }
        }

        if ($needProxy) {
            $ip = $this->checkMyIP($needProxy);
            if ($ip) {
                $needProxy['ip'] = $ip;
            }
        }

        return $needProxy;
    }

    /**
     * @param $proxy
     *
     * @return false|mixed
     * @throws GuzzleException
     */
    function checkMyIP($proxy): mixed
    {
        $response = $this->sendRequest('checkMyIP', 'GET', [], [
            'proxy' => "{$proxy['host']}:{$proxy['port']}",
            'proxy-auth' => "{$proxy['username']}:{$proxy['password']}",
        ]);

        $json = json_decode($response, true);

        if (isset($json['status']) && $json['status']) {
            return $json['ip'];
        }

        return false;
    }

    /**
     * @param $id
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function changeIpOnPort($id): mixed
    {
        $response = $this->sendRequest("ports/{$id}/newip", 'GET', ['token' => $this->token, 'id' => $id]);
        return json_decode($response, true);
    }

    /**
     * @param mixed $id
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function removePort(mixed $id): mixed
    {
        $response = $this->sendRequest("ports/{$id}", 'DELETE', ['token' => $this->token, 'id' => $id]);
        return json_decode($response, true);
    }

    /**
     * @param string $iso2
     *
     * @return false|mixed
     * @throws Exception
     */
    public function getCountryByISO2(string $iso2): mixed
    {
        $response = Http::get($this->domain . 'countries', [
            'token' => $this->token,
        ]);
        if ($response->status() !== 200) {
            throw new Exception('Service is not available.');
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
     * @param string $country
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function createPort(string $country): mixed
    {
        $postFields = [
            "name" => "API",
            "network" => "Mobile",
            "country" => $country,
            "rotation_by" => "link",
            "is_unlimited" => "0",
            "volume" => "0.1",
            "username" => "api_username",
            "password" => "api_password",
        ];
        $response = $this->sendRequest('ports', 'POST', array_merge($postFields, ['token' => $this->token]));

        return json_decode($response, true);
    }
}
