<?php namespace ITC\Weixin\Payment\Contracts;

use GuzzleHttp\ClientInterface as HttpClient;
use Psr\Http\Message\ResponseInterface as HttpResponse;

interface Client {

    public function getHttpClient();
    public function setHttpClient(HttpClient $client);

    public function getHashGenerator();
    public function setHashGenerator(HashGenerator $hashgen);

    public function getSerializer();
    public function setSerializer(Serializer $serializer);

    /**
     * @param bool $secure
     * @return ITC\Weixin\Payment\Contracts\Client
     */
    public function secure($secure=true);

    /**
     * @param string $url
     * @param array $message
     * @param array $headers
     * @param array $options
     * @return array
     */
    public function call($url, array $message, array $options=[], HttpResponse &$response=null);

    /**
     * @param array $message
     * @param string $nonce
     * @return array
     */
    public function sign(array $message, $nonce=null);

    /**
     * @param ITC\Weixin\Payment\Contracts\Command $command
     * @return void
     */
    public function register(Command $command);

    /**
     * @param string $name
     * @return ITC\Weixin\Payment\Contracts\Command
     */
    public function command($name);
}
