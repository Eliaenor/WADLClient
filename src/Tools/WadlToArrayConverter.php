<?php

namespace WADLClient\Tools;

use WADLClient\Tools\Misc\BaseWadlNode;
use WADLClient\Tools\Misc\XmlNodeInterface;

class WadlToArrayConverter
{
    private const XSD_NAMESPACE_URI = 'http://www.w3.org/2001/XMLSchema';
    private const WADL_NAMESPACE_URI = 'http://wadl.dev.java.net/2009/02';

    /** @var \SimpleXMLElement */
    private $xml;

    /** @var array */
    private $namespaces;

    /**
     * WadlToArrayConverter constructor.
     * @param \SimpleXMLElement $xml
     */
    public function __construct(\SimpleXMLElement $xml)
    {
        $this->namespaces = $xml->getDocNamespaces(true);
        $this->xml = $xml;
    }

    /**
     * @return array
     */
    public function getDocNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * @return array
     */
    public function wadlToArray()
    {
        $xmlNodes = [];

        foreach ($this->xml as $node) {
            $xmlNodes[$node->getName()] = $this->getAllChildren($node);
        }

        return [$this->xml->getName() => $xmlNodes];
    }

    /**
     * @param \SimpleXMLElement $xmlNode
     * @return XmlNodeInterface
     */
    private function getAllChildren(\SimpleXMLElement $xmlNode): XmlNodeInterface
    {
        $nodeAttributes = [];
        $nodeChildren = [];

        foreach ($this->namespaces as $prefix => $namespace) {
            if ($attributes = $xmlNode->attributes($prefix, true)) {
                $nodeAttributes = array_merge($nodeAttributes, ((array)$attributes)['@attributes']);
            }
            $children = $xmlNode->children($prefix, true);
            if ($children->asXML()) {
                foreach ($children as $child) {
                    $nodeChildren[] = $this->getAllChildren($child);
                }
            }
        }

        return $this->createNodeObject($xmlNode, $nodeAttributes, $nodeChildren);
    }

    /**
     * @param \SimpleXMLElement $xmlNode
     * @param array $nodeAttributes
     * @param array $nodeChildren
     *
     * @return BaseWadlNode
     */
    private function createNodeObject(\SimpleXMLElement $xmlNode, array $nodeAttributes, array $nodeChildren)
    {
        $className = null;

        switch (current($xmlNode->getNamespaces())) {
            case self::XSD_NAMESPACE_URI:
                $className = sprintf('WADLClient\Nodes\XSD\%sXsdNode', ucfirst($xmlNode->getName()));
                break;
            case self::WADL_NAMESPACE_URI:
                $className = sprintf('WADLClient\Nodes\WADL\%sWadlNode', ucfirst($xmlNode->getName()));
                break;
        }

        if (isset($className) && class_exists($className)) {
            return new $className($nodeAttributes, $nodeChildren);
        }

        return new BaseWadlNode($xmlNode->getName(), $nodeAttributes, $nodeChildren);
    }
}