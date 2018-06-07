<?php

namespace WADLClient\Tools;

class ArrayToXmlConverter
{
    /** @var string */
    private $xmlVersion;

    /** @var string */
    private $charset;

    /** @var string */
    private $xmlns;

    /**
     * ArrayToXmlConverter constructor.
     * @param string $xmlVersion
     * @param string $charset
     * @param string $xmlns
     */
    public function __construct(string $xmlVersion, string $charset, string $xmlns)
    {
        $this->xmlVersion = $xmlVersion;
        $this->charset = $charset;
        $this->xmlns = $xmlns;
    }

    /**
     * @param string $rootName
     * @param array $data
     * @return \SimpleXMLElement
     */
    public function arrayToXml(string $rootName, array $data)
    {
        $xml = new \SimpleXMLElement(sprintf(
            '<?xml version="%s" encoding="%s"?><%s xmlns="%s"></%s>',
            $this->xmlVersion,
            $this->charset,
            $rootName,
            $this->xmlns,
            $rootName
        ));

        return $this->convertArray($data ,$xml);
    }

    /**
     * @param array $data
     * @param \SimpleXMLElement $xml
     * @return \SimpleXMLElement
     */
    private function convertArray(array $data, \SimpleXMLElement $xml)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (!$this->arrayIsAssociative($value)) {
                    foreach ($value as $childData) {
                        $child = $xml->addChild($key);
                        $this->convertArray($childData, $child);
                    }
                } else {
                    $child = $xml->addChild($key);
                    $this->convertArray($value, $child);
                }
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }

        return $xml;
    }

    /**
     * @param array $array
     * @return bool
     */
    private function arrayIsAssociative(array $array)
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }
}