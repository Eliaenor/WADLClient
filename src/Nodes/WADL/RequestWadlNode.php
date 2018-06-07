<?php

namespace WADLClient\Nodes\WADL;

use WADLClient\Tools\Misc\BaseXmlNodeTrait;
use WADLClient\Tools\Misc\XmlNodeInterface;

class RequestWadlNode implements XmlNodeInterface
{
    use BaseXmlNodeTrait;

    /**
     * ComplexTypeXsdNode constructor.
     * @param array $nodeAttributes
     * @param XmlNodeInterface[] $children
     */
    public function __construct(array $nodeAttributes, array $children)
    {
        $this->attributes = $nodeAttributes;
        $this->children = $children;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'request';
    }

    /**
     * @return array
     */
    public function resolve(): array
    {
        $childList = [];

        foreach ($this->children as $child) {
            if (is_a($child,RepresentationWadlNode::class)) {
                $childData = $child->resolve();
                $childList[$child->getName()][$childData['mediaType']] = $childData['element'];
            }
        }

        return $childList;
    }
}