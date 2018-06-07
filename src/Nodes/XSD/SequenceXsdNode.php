<?php

namespace WADLClient\Nodes\XSD;

use WADLClient\Tools\Misc\BaseXmlNodeTrait;
use WADLClient\Tools\Misc\XmlNodeInterface;

class SequenceXsdNode implements XmlNodeInterface
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
        return 'sequence';
    }

    /**
     * @return array
     */
    public function resolve(): array
    {
        $childrenList = [];

        foreach ($this->children as $child) {
            $childrenList[$child->getAttribute('name')] = $child->getAttributesList();
        }

        return ['sequence' => $childrenList];
    }
}