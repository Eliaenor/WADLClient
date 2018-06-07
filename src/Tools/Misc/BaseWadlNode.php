<?php

namespace WADLClient\Tools\Misc;

class BaseWadlNode implements XmlNodeInterface
{
    use BaseXmlNodeTrait;

    /** @var string */
    private $name;

    /**
     * BaseWadlNode constructor.
     * @param string $name
     * @param array $nodeAttributes
     * @param array $children
     */
    public function __construct(string $name, array $nodeAttributes, array $children)
    {
        $this->name = $name;
        $this->attributes = $nodeAttributes;
        $this->children = $children;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function resolve(): array
    {
        return [];
    }
}