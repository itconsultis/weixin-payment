<?php namespace ITC\Weixin;

use ITC\Weixin\Contracts\Serializer as SerializerInterface;

class XMLSerializer implements SerializerInterface {

    /**
     * @param array $data
     * @return string
     */
    public function serialize(array $data)
    {
        $xml[] = '<xml>';

        foreach ($data as $key => $value)
        {
            if (!is_numeric($value))
            {
                $value = sprintf('<![CDATA[%s]]>', $value);
            }
            $xml[] = sprintf('<%s>%s</%s>', $key, $value, $key);
        }

        $xml[] = '</xml>';

        return implode('', $xml);
    }

    /**
     * @param string $serialized
     * @return array
     */
    public function unserialize($xml)
    {
        $xmldoc = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)
        return json_decode(json_encode($xmldoc, true));
    }
}
