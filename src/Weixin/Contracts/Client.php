<?php namespace ITC\Weixin\Contracts;

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
     * @param string $url
     * @param array $data
     * @param array $headers
     * @param array $options
     * @return array
     */
    public function call($url, array $data, array $options=[], HttpResponse &$response=null);

}
