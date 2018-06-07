<?php

namespace WADLClient\Nodes\WADL;

use WADLClient\Tools\Misc\BaseXmlNodeTrait;
use WADLClient\Tools\Misc\XmlNodeInterface;

class ResourceWadlNode implements XmlNodeInterface
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
        return 'resource';
    }

    /**
     * @return array
     */
    public function resolve(): array
    {
        $childList = [];

        foreach ($this->children as $child) {
            if (is_a($child, MethodWadlNode::class)) {
                $childList[$child->getName()][$child->getAttribute('name')] = $child->resolve();
            } else {
                $childList[$child->getName()][] = $child->resolve();
            }
        }

        return $childList;
    }
}