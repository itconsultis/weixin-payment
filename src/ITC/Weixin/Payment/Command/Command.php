<?php

namespace ITC\Weixin\Payment\Command;

use InvalidArgumentException;
use ITC\Weixin\Payment\Contracts\Client as ClientInterface;
use ITC\Weixin\Payment\Contracts\Command as CommandInterface;

abstract class Command implements CommandInterface
{
    private $url;
    protected $client;

    /**
     * @param array $params
     * @param array $errors
     */
    abstract protected function getRequiredParams();

    /**
     * @param array $params
     * @param array $errors
     */
    protected function validateParams(array $params, array &$errors)
    {
        // no-op; override me
    }

    /**
     * @param ITC\Weixin\Payment\Contracts\Client $client
     */
    public function __construct(ClientInterface $client = null)
    {
        $client && $this->setClient($client);
    }

    /**
     * @param void
     *
     * @return string
     */
    protected function getDefaultUrl()
    {
        return 'https://api.mch.weixin.qq.com/'.$this->name();
    }

    /**
     * Satisfies ITC\Weixin\Payment\Contracts\Command#setUrl.
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Satisfies ITC\Weixin\Payment\Contracts\Command#getUrl.
     *
     * @param void
     *
     * @return string
     */
    public function getUrl()
    {
        if (!$this->url) {
            $this->url = $this->getDefaultUrl();
        }

        return $this->url;
    }

    /**
     * Satisfies ITC\Weixin\Payment\Contracts\Command#setClient.
     *
     * @param ITC\Weixin\Payment\Contracts\Client $client
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Satisfies ITC\Weixin\Payment\Contracts\Command#execute.
     *
     * @param array $params
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    public function execute(array $params)
    {
        $errors = [];

        foreach ($this->getRequiredParams() as $param) {
            if (!isset($params[$param])) {
                $errors[] = 'missing parameter: '.$param;
            }
        }

        !$errors && $this->validateParams($params, $errors);

        if ($errors) {
            $msg = 'parameter validation errors(s): '.implode(', ', $errors);
            throw new InvalidArgumentException($msg);
        }

        $message = $this->client->createMessage($params);

        return $this->client->post($this->getUrl(), $message);
    }
}
