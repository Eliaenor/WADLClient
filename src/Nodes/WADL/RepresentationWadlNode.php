<?php

namespace WADLClient\Nodes\WADL;

use WADLClient\Tools\Misc\BaseXmlNodeTrait;
use WADLClient\Tools\Misc\XmlNodeInterface;

class RepresentationWadlNode implements XmlNodeInterface
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
        return 'representation';
    }

    /**
     * @return array
     */
    public function resolve(): array
    {
        return [
            'element' => $this->getAttribute('element'),
            'mediaType' => $this->getAttribute('mediaType')
        ];
    }
}