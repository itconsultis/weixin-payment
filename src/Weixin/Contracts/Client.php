<?php namespace ITC\Weixin\Contracts;

use GuzzleHttp\ClientInterface as HttpClient;

interface Client {

    public function getAppId();
    public function setAppId($app_id);

    public function getAppSecret();
    public function setAppSecret($app_secret);

    public function getHttpClient();
    public function setHttpClient(HttpClient $client);

    public function getHashGenerator();
    public function setHashGenerator(HashGenerator $hashgen);

    public function getSerializer();
    public function setSerializer(Serializer $serializer);
}
