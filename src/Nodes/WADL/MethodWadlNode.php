<?php

namespace WADLClient\Nodes\WADL;

use WADLClient\Tools\Misc\BaseXmlNodeTrait;
use WADLClient\Tools\Misc\XmlNodeInterface;

class MethodWadlNode implements XmlNodeInterface
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
        return 'method';
    }

    /**
     * @return array
     */
    public function resolve(): array
    {
        $childList = [];

        foreach ($this->children as $child) {
            $childList[$child->getName()] = $child->resolve();
        }

        return $childList;
    }
}