<?php

namespace ITC\Weixin\Payment\Contracts;

use Psr\Log\LoggerInterface as Logger;
use Psr\Http\Message\ResponseInterface as HttpResponse;
use GuzzleHttp\ClientInterface as HttpClient;

interface Client extends MessageFactory
{
    public function setHttpClient(HttpClient $client);
    public function getHttpClient();

    public function setHashGenerator(HashGenerator $hashgen);
    public function getHashGenerator();

    public function setSerializer(Serializer $serializer);
    public function getSerializer();

    public function setLogger(Logger $logger);
    public function getLogger();

    /**
     * @param bool $secure
     *
     * @return ITC\Weixin\Payment\Contracts\Client
     */
    public function secure($secure = true);

    /**
     * @param string $name
     *
     * @return ITC\Weixin\Payment\Contracts\Command
     */
    public function command($name);

    /**
     * @param mixed $data
     *
     * @return ITC\Weixin\Payment\Contracts\Message
     */
    public function message($data = null, $required = null);

    /**
     * @param array  $query
     * @param string $nonce     - optional
     * @param int    $timestamp - optional
     *
     * @return JsonSerializable
     */
    public function jsapize(array $query, $nonce = null, $timestamp = null);
}
