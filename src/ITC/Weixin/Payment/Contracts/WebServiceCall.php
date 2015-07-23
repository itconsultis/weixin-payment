<?php namespace ITC\Weixin\Payment\Contracts;

interface WebServiceCall {

    /**
     * @param string ITC\Weixin\Payment\Contracts\Client $client
     * @return void
     */
    public function setClient(Client $client);

    /**
     * @param void
     * @return string
     */
    public function getUrl();

    /**
     * @param string $url
     * @return void
     */
    public function setUrl($url);

    /**
     * @param array $params
     * @return array
     * @throws RuntimeException
     */
    public function execute(array $params);
}
