<?php

namespace WADLClient;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use WADLClient\Resources\ResourcesWadl;
use WADLClient\Tools\WadlToArrayConverter;
use WADLClient\Tools\Misc\XmlNodeInterface;

class WadlClient
{
    /** @var ResourcesWadl[] */
    private $resourcesList;

    /**
     * @param string $xmlUri
     * @return $this
     */
    public function initialize(string $xmlUri)
    {
        if (!$this->validateWadl($xmlUri)) {
            throw new \LogicException(sprintf('The xml provided does not respect WADL syntax. Please check you file.'));
        }

        $xml = simplexml_load_file($xmlUri);
        $wadlToArrayConverter = new WadlToArrayConverter($xml);
        $wadl = $wadlToArrayConverter->wadlToArray();
        $types = $this->getAllCustomTypes($wadl['application']['grammars']);
        $resources = $this->getAllResources($wadl['application']['resources']);
        $baseUrl = $wadl['application']['resources']->getAttribute('base');

        $resourceFactory = new WadlResourcesFactory($baseUrl, $wadlToArrayConverter->getDocNamespaces(), $types);
        $this->resourcesList = $resourceFactory->resolveResourcesDefinition($resources);

        return $this;
    }

    /**
     * @param ResourcesWadl $request
     * @param string $method
     * @param string $mediaType
     * @param $arguments
     * @return array
     * 
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendRequest(ResourcesWadl $request, string $method, string $mediaType, $arguments)
    {
        $body = $request->getBody($method, $mediaType, $arguments);

        $httpClient = new Client();
        $httpRequest = new Request($method, $request->getUrl(), ['Content-Type' => $mediaType], $body);

        try {
            $response = $httpClient->send($httpRequest);
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
        }

        return [
            'statusCode' => $response->getStatusCode(),
            'content' => $response->getBody()->getContents()
        ];
    }

    /**
     * @param string $name
     * @return ResourcesWadl|null
     */
    public function getResource(string $name)
    {
        if (array_key_exists($name, $this->resourcesList)) {
            return $this->resourcesList[$name];
        }

        return null;
    }

    /**
     * @param XmlNodeInterface $grammarsNode
     * @return array
     */
    private function getAllCustomTypes(XmlNodeInterface $grammarsNode)
    {
        $elements = [];

        foreach ($grammarsNode->getChildren() as $schemaNode) {
            $nodeNamespace = $schemaNode->getAttribute('targetNamespace');
            $elements[$nodeNamespace]['imports'] = $this->getAllChildElementsByName($schemaNode, 'import');
            $elements[$nodeNamespace]['rootElements'] = $this->getAllChildElementsByName($schemaNode, 'element');
            $elements[$nodeNamespace]['complexType'] = $this->getAllChildElementsByName($schemaNode, 'complexType');
            $elements[$nodeNamespace]['simpleType'] = $this->getAllChildElementsByName($schemaNode, 'simpleType');
        }

        return $elements;
    }

    /**
     * @param XmlNodeInterface $xsdNode
     * @param string $name
     * @return array
     */
    private function getAllChildElementsByName(XmlNodeInterface $xsdNode, string $name)
    {
        $elementList = [];

        foreach ($xsdNode->getChildrenByName($name) as $element) {
            if ($element->getAttribute('name')) {
                $elementList[$element->getAttribute('name')] = $element->resolve();
            } else {
                $elementList[] = $element->resolve();
            }
        }

        return $elementList;
    }

    /**
     * @param XmlNodeInterface $resourcesNode
     * @return XmlNodeInterface[]
     */
    private function getAllResources(XmlNodeInterface $resourcesNode): array
    {
        $resourcesList = [];

        foreach ($resourcesNode->getChildren() as $child) {
            $resourcesList[$child->getAttribute('path')] = $child->resolve();
        }

        return $resourcesList;
    }

    /**
     * @param string $fileUri
     * @return bool
     */
    private function validateWadl(string $fileUri)
    {
        $domDoc = new \DOMDocument();
        $domDoc->loadXML(file_get_contents($fileUri));

        return $domDoc->schemaValidate('https://www.w3.org/Submission/wadl/wadl.xsd');
    }
}