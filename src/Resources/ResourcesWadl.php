<?php

namespace WADLClient\Resources;

use WADLClient\Tools\ArrayToXmlConverter;

class ResourcesWadl
{
    /** @var string */
    private $url;

    /** @var array */
    private $schema;

    /** @var array */
    private $methodList;

    /**
     * ResourcesWadl constructor.
     * @param string $url
     * @param array $resourceDescription
     */
    public function __construct(string $url, array $resourceDescription)
    {
        $this->url = $url;
        $this->schema = $resourceDescription;

        foreach ($resourceDescription as $method => $methodData) {
            $this->methodList[$method] = [
                'requestMediaTypes' => array_keys($methodData['request']),
                'responseMediaTypes' => array_keys($methodData['response'])
            ];
        }
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $method
     * @param string $mediaType
     * @param $arguments
     * @return
     */
    public function getBody(string $method, string $mediaType, $arguments)
    {
        $request = $this->validateCallArguments($method, $mediaType, $arguments);

        // @Todo :Handle More MediaType. Only XML is handled right now.
        $arrayToXmlConverter = new ArrayToXmlConverter('1.0', 'UTF-8', $request['namespace']);

        return  $arrayToXmlConverter->arrayToXml($request['type'], $request['content'])->asXML();

    }

    /**
     * @param $schemaDefinition
     * @param $arguments
     * @param string $rootName
     * @return array
     */
    private function validateArguments($schemaDefinition, $arguments, string $rootName = 'root')
    {
        if (gettype($schemaDefinition) != gettype($arguments)) {
            throw new \InvalidArgumentException(
                sprintf("Key '%s' array must be of type %s. %s provided.", $rootName, gettype($schemaDefinition), gettype($arguments))
            );
        }
        if (is_string($schemaDefinition)) {
            //Handle XML TYPE
            return $arguments;
        }
        foreach ($schemaDefinition as $key => $typeDesc) {
            if (!$this->checkMinAndMaxOccurs($typeDesc, $arguments, $key, $rootName)) {
                if (isset($typeDesc['properties']['default']) && !isset($arguments[$key])) {
                    $arguments[$key] = $typeDesc['properties']['default'];
                }
                if (!isset($typeDesc['properties']['minOccurs']) || $typeDesc['properties']['minOccurs'] !== '0') {
                    $this->checkArrayKey($key, $arguments, $rootName);
                }
                if (isset($arguments[$key])) {
                    $arguments[$key] = $this->validateArguments($typeDesc['content'], $arguments[$key], $key);
                }
            }
        }

        return $arguments;
    }

    /**
     * @param array $typeDesc
     * @param $arguments
     * @param string $key
     * @param string $rootName
     *
     * @return bool
     */
    private function checkMinAndMaxOccurs(array $typeDesc, $arguments, string $key, string $rootName)
    {
        if ((isset($typeDesc['properties']['maxOccurs']) && $typeDesc['properties']['maxOccurs'] !== '0') ||
            (isset($typeDesc['properties']['minOccurs']) && $typeDesc['properties']['minOccurs'] !== '0')) {

            $min = $typeDesc['properties']['minOccurs'] ?? null;
            $max = $typeDesc['properties']['maxOccurs'] ?? null;
            $index = 0;

            foreach ($arguments[$key] as $item) {
                $this->validateArguments($typeDesc['content'], $item, $key);
                $index++;
            }
            if (is_numeric($min)) {
                if ($index < $min) {
                    throw new \InvalidArgumentException(
                        sprintf("There must be at least %s '%s' element in %s. %s provided.", $min, $key, $rootName, $index)
                    );
                }
            }
            
            if (is_numeric($max)) {
                if ($index > $max) {
                    throw new \InvalidArgumentException(
                        sprintf("There must be at most %s '%s' element in %s. %s provided.", $max, $key, $rootName, $index)
                    );
                }
            }
            $this->checkArrayKey($key, $arguments, $rootName);
            return true;
        }
        return false;
    }

    /**
     * @param string $key
     * @param $arguments
     * @param string $rootName
     */
    private function checkArrayKey(string $key, $arguments, string $rootName)
    {
        if (!array_key_exists($key, $arguments)) {
            if (empty($arguments)) {
                throw new \InvalidArgumentException(
                    sprintf("Missing key '%s'. Empty '%s' array provided.", $key, $rootName)
                );
            }
            throw new \InvalidArgumentException(
                sprintf("Missing key '%s'. '%s' array with keys '%s' provided.", $key, $rootName, implode(', ', array_keys($arguments)))
            );
        }
    }

    /**
     * @param string $method
     * @param string $mediaType
     * @param $arguments
     * @return array
     */
    private function validateCallArguments(string $method, string $mediaType, $arguments)
    {
        if (!array_key_exists($method, $this->methodList)){
            throw new \InvalidArgumentException(
                sprintf('method %s not allowed for this resource. Method Allowed : ', $method, implode(', ', array_keys($this->methodList)))
            );
        }
        if (!in_array($mediaType, $this->methodList[$method]['requestMediaTypes'])){
            throw new \InvalidArgumentException(
                sprintf('mediaType %s not allowed for this method. MediaType Allowed : ', $mediaType, implode(', ', $this->methodList[$method]['requestMediaTypes']))
            );
        }
        if (!isset($this->schema[$method]['request'][$mediaType])) {
            throw new \InvalidArgumentException(
                sprintf('Arguments description not found for method %s and mediaType %s', $method, $mediaType)
            );
        }

        $rootNode = $this->schema[$method]['request'][$mediaType];
        $rootNode['content'] = $this->validateArguments($rootNode['content'], $arguments);

        return $rootNode;
    }
}