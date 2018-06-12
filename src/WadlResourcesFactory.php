<?php

namespace WADLClient;

use WADLClient\Resources\ResourcesWadl;

class WadlResourcesFactory
{
    private const XSD_NAMESPACE_URI = 'http://www.w3.org/2001/XMLSchema';

    /** @var array */
    private $namespaces;

    /** @var array */
    private $types;

    /** @var string */
    private $baseUrl;

    /**
     * WadlResourcesFactory constructor.
     * @param string $baseUrl
     * @param array $namespace
     * @param array $types
     */
    public function __construct(string $baseUrl, array $namespace, array $types)
    {
        $this->baseUrl = $baseUrl;
        $this->namespaces = $namespace;
        $this->types = $types;
    }

    /**
     * @param $resources
     * @return array
     */
    public function resolveResourcesDefinition($resources)
    {
        $resourceList = [];

        foreach ($resources as $url => $resource) {
            $resourceList[substr($url, 1)] = new ResourcesWadl($this->baseUrl.$url, $this->resolveResourceSchema($resource));
        }

        return $resourceList;
    }

    /**
     * @param $resources
     * @return array
     */
    private function resolveResourceSchema($resources): array
    {
        $methods = [];

        foreach ($resources['method'] as $methodName => $method) {
            $methods[$methodName] = [
                'request' => $this->resolveMethod($method, 'request'),
                'response' => $this->resolveMethod($method, 'response')
            ];
        }

        return $methods;
    }

    /**
     * @param array $method
     * @param string $dataSource
     * @return array
     */
    private function resolveMethod(array $method, string $dataSource)
    {
        $source = [];

        foreach ($method[$dataSource]['representation'] as $mediaType => $representation) {
            $typeName = explode(':', $representation);
            $source[$mediaType] = [
                'namespace' => $this->namespaces[current($typeName)],
                'type' => end($typeName),
                'content' =>$this->resolveSchema($representation)
            ];
        }

        return $source;
    }

    /**
     * @param string $schemaTypeName
     * @return array|string
     */
    private function resolveSchema(string $schemaTypeName)
    {
        $typeNameArray = explode(':', $schemaTypeName);

        if (!isset($typeNameArray[1])) {
            throw new \LogicException(sprintf("All types must be prefixed. Type %s is not well prefixed", $schemaTypeName));
        }
        $typeName = $typeNameArray[1];
        $namespace = $this->namespaces[$typeNameArray[0]];

        if ($namespace === self::XSD_NAMESPACE_URI) {
            return $this->resolveXmlType($typeName);
        }

        $fieldData = [];

        foreach ($this->types[$namespace] as $key => $elements) {
            if (array_key_exists($typeName, $elements)) {
                $type = $elements[$typeName]['content'];
                if (isset($type['type'])) {
                    $fieldData = array_merge($fieldData, $this->resolveSchema($type['type']));
                }
                if (isset($type['sequence'])) {
                    $fieldData = array_merge($fieldData, $this->resolveSequence($type['sequence']));
                }
                if (isset($type['extensions'])) {
                    $fieldData = array_merge($fieldData, $this->resolveExtension($type['extensions']));
                }
            }
        }

        return $fieldData;
    }

    /**
     * @param array $element
     * @return array
     */
    private function resolveExtension(array $element)
    {
        $fieldData = [];

        if (isset($element['sequence'])) {
            $fieldData = array_merge($fieldData, $this->resolveSequence($element['sequence']));
        }

        return $fieldData;
    }

    /**
     * @param array $element
     * @return array
     */
    private function resolveSequence(array $element)
    {
        $fieldData = [];

        foreach ($element as $sequenceElement) {
            $fieldData[$sequenceElement['name']] = [
                'content' => $this->resolveSchema($sequenceElement['type']),
            ];
            $properties = $this->getSequenceElementProperties($sequenceElement);
            if (!empty($properties)) {
                $fieldData[$sequenceElement['name']]['properties'] = $properties;
            }
        }

        return $fieldData;
    }

    /**
     * @param array $sequenceElement
     * @return array
     */
    private function getSequenceElementProperties(array $sequenceElement)
    {
        unset($sequenceElement['name']);
        unset($sequenceElement['type']);

        return $sequenceElement;
    }

    /**
     * @param string $typeName
     * @return string
     */
    private function resolveXmlType(string $typeName): string
    {
        return $typeName;
    }
}