<?php namespace ITC\Weixin\Payment\Api;

use InvalidArgumentException;
use ITC\Weixin\Payment\Contracts\Client as ClientInterface;
use ITC\Weixin\Payment\Contracts\WebServiceCall as CallInterface;


abstract class Call implements CallInterface {

    /**
     * @param void
     * @return string
     */
    abstract protected function getDefaultUrl();

    /**
     * @param array $params
     * @param array $errors
     * @return void
     */
    abstract protected function getRequiredParams();

    /**
     * @param array $params
     * @param array $errors
     * @return void
     */
    protected function validateParams(array $params, array &$errors)
    {
        // no-op; override me
    }

    /**
     * @param ITC\Weixin\Payment\Contracts\Client $client
     */
    public function __construct(ClientInterface $client=null)
    {
        $client && $this->setClient($client);
    }

    /**
     * Satisfies ITC\Weixin\Payment\Contracts\Client#setUrl
     * @param string $url
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Satisfies ITC\Weixin\Payment\Contracts\Client#getUrl
     * @param void
     * @return string
     */
    public function getUrl()
    {
        if (!$this->url)
        {
            return $this->getDefaultUrl();
        }
        return $this->url;
    }

    /**
     * Satisfies ITC\Weixin\Payment\Contracts\Client#setClient
     * @param ITC\Weixin\Payment\Contracts\Client $client
     * @return void
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Satisfies ITC\Weixin\Payment\Contracts\Client#execute
     * @param array $params
     * @return array
     * @throws InvalidArgumentException
     */
    public function execute(array $params)
    {
        $errors = [];

        foreach ($this->getRequiredParams() as $param)
        {
            if (!isset($params[$param]))
            {
                $errors[] = 'missing parameter: '.$param; 
            }
        }

        !$errors && $this->validateParams($params, $errors);

        if ($errors)
        {
            $msg = 'parameter validation errors(s): '.implode(', ', $errors);
            throw new InvalidArgumentException($msg);
        }

        return $this->client->call($this->getUrl(), $params);
    }

}
