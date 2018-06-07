<?php

namespace WADLClient\Nodes\XSD;

use WADLClient\Tools\Misc\BaseXmlNodeTrait;
use WADLClient\Tools\Misc\XmlNodeInterface;

class ExtensionXsdNode implements XmlNodeInterface
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
        return 'extension';
    }

    /**
     * @return array
     */
    public function resolve(): array
    {
        return [
            'type' => $this->getAttribute('base'),
            'extensions' => $this->children[0]->resolve(),
        ];
    }
}