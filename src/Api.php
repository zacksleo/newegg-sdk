<?php

namespace Zacksleo\NeweggSdk;

use Hanson\Foundation\Log;
use Hanson\Foundation\AbstractAPI;
use Hanson\Foundation\Exception\HttpException;

class Api extends AbstractAPI
{
    private $gateway = 'https://api.newegg.com/';
    private $key;
    private $secret;
    private $sellerId;
    private $map = [];

    public function __construct($key, $secret, $sellerId)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->sellerId = $sellerId;
        $this->map = include __DIR__.'/map.php';
    }

    /**
     * 获取网关地址
     *
     * @return string
     */
    private function getGateway()
    {
        return $this->gateway;
    }

    /**
     * 发起请求
     *
     * @param string|array $method
     * @param array $params
     * @param array $files
     * @return void
     */
    public function request($method, $params = null, $files = [])
    {
        [$operationType, $appMethod] = $this->resolveOperationType($method);
        $params = $this->resolveParams($operationType, $params);

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => $this->key,
            'SecretKey'     => $this->secret,
            'Accept'        => 'application/json',
        ];
        try {
            $http = $this->getHttp();
            $response = $http->request(empty($params) ? 'GET' : 'PUT', $this->getGateway().str_replace('.', '/', $appMethod), [
                'headers' => $headers,
                'json'    => $params,
                'query'   => [
                    'sellerid'=> $this->sellerId,
                ],
            ]);
        } catch (\Exception  $e) {
            Log::error($e->getMessage(), $e->getTrace());
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }

        return json_decode((string) $response->getBody(), true);
    }

    private function resolveOperationType($method)
    {
        if (is_array($method)) {
            $appMethod = $this->autoCompleteAppMethod(key($method));

            return [current($method), $appMethod];
        }
        if (! is_string($method)) {
            Log::error('不支持的参数格式');
            throw new \InvalidArgumentException('不支持的参数格式');
        }
        $appMethod = $this->autoCompleteAppMethod($method);
        if (! isset($this->map[$appMethod])) {
            $message = "map 文件中未设置 $appMethod 对应的 OperationType，请使用 [appMethod=>OperationType] 形式传递参数";
            Log::error($message);
            throw new \InvalidArgumentException($message);
        }

        return [$this->map[$appMethod], $appMethod];
    }

    private function resolveParams($operationType, $params)
    {
        if ($params == null) {
            return [];
        }
        if ($operationType != null) {
            return [
                'OperationType' => $operationType,
                'RequestBody' => $params,
            ];
        }

        return $params;
    }

    /**
     * 如果没有填写前缀，自动补全 appMethod
     *
     * @param string $appMethod
     * @return string
     */
    private function autoCompleteAppMethod($appMethod)
    {
        if (strpos($appMethod, 'marketplace.') !== 0) {
            return 'marketplace.'.$appMethod;
        }

        return $appMethod;
    }
}
