<?php namespace ITC\Weixin;

use ITC\Weixin\Contracts\Client as ClientInterface;
use ITC\Weixin\Contracts\HashGenerator as HashGeneratorInterface;
use ITC\Weixin\Contracts\Serializer as SerializerInterface;
use ITC\Weixin\Util\UUID;

use Psr\Http\Message\ResponseInterface as HttpResponse;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use GuzzleHttp\Client as HttpClient;


class Client implements ClientInterface {

    protected $credentials = [
        'app_id' => null,
        'app_secret' => null,
        'mch_id' => null,
    ];

    protected $paths = [
        'public_key' => null,
        'private_key' => null,
    ];

    protected $http;
    protected $hashgen;
    protected $serializer;

    /**
     * @param array $config
     */
    public function __construct(array $config=[])
    {
        $this->credentials = $config['credentials'];
        $this->paths = $config['paths'];
    }

    /**
     * @param void
     * @return GuzzleHttp\ClientInterface
     */
    public function getHttpClient()
    {
        if (!$this->http)
        {
            $this->setHttpClient(new HttpClient());
        }
        return $this->http;
    }

    /**
     * @param GuzzleHttp\ClientInterface $client
     * @return void
     */
    public function setHttpClient(HttpClientInterface $client)
    {
        $this->http = $client;
    }

    /**
     * @param void
     * @return ITC\Weixin\Contracts\HashGenerator
     */
    public function getHashGenerator()
    {
        if (!$this->hashgen)
        {
            $this->setHashGenerator(new HashGenerator());
        }
        return $this->hashgen;
    }

    /**
     *
     *
     */
    public function setHashGenerator(HashGeneratorInterface $hashgen)
    {
        $this->hashgen = $hashgen;
    }

    /**
     * @param void
     * @return ITC\Weixin\Contracts\SerializerInterface
     */
    public function getSerializer()
    {
        if (!$this->serializer)
        {
            $this->setSerializer(new XmlSerializer());
        }
        return $this->serializer;
    }

    /**
     * @param ITC\Weixin\Contracts\Serializer
     * @return void
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param string $url
     * @param array $data
     * @param array $options
     * @param Psr\Http\Message\ResponseInterface $response
     * @return array
     */
    public function call($url, array $data, array $options=[], HttpResponse &$response=null)
    {
        $data['nonce_str'] = UUID::v4();
        $data['sign'] = $this->hashgen->hash($data);

        $headers = ['Content-Type'=>'text/xml', 'Accept'=>'text/xml'];
        $body = $this->serializer->serialize($data);

        $response = $this->http->post($url, [
            'body' => $this->serializer->serialize($data),
            'headers' => [
                'Content-Type' => 'text/xml',
                'Accept' => 'text/xml',
            ],
        ]);

        $status = (int) $response->getStatusCode();

        if ($status >= 200 && $status < 300)
        {
            throw new UnexpectedValueException('got unexpected HTTP status '.$status);
        }

        return $this->serializer->unserialize($response->getBody());
    }

}

