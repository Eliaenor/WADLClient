<?php

namespace WADLClient\Nodes\XSD;

use WADLClient\Tools\Misc\BaseXmlNodeTrait;
use WADLClient\Tools\Misc\XmlNodeInterface;

class ElementXsdNode implements XmlNodeInterface
{
    use BaseXmlNodeTrait;

    /**
     * ElementXsdNode constructor.
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
        return 'element';
    }

    /**
     * @return array
     */
    public function resolve(): array
    {
        return $this->children[0]->resolve();
    }
}