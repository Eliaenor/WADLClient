<?php

namespace WADLClient\Tools\Misc;


trait BaseXmlNodeTrait
{
    /** @var array */
    private $attributes;

    /** @var XmlNodeInterface[] */
    private $children;

    /**
     * @param string $name
     * @return null|string
     */
    public function getAttribute(string $name): ?string
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    /**
     * @return array
     */
    public function getAttributesList(): array
    {
        return $this->attributes;
    }

    /**
     * @return XmlNodeInterface[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param string|null $name
     * @return XmlNodeInterface[]
     */
    public function getChildrenByName(string $name = null): array
    {
        $childrenArray = [];

        foreach ($this->children as $child) {
            if (is_null($name) || $name === $child->getName()) {
                $childrenArray[$child->getName()][] = $child;
            }
        }

        return (is_null($name) || empty($childrenArray)) ? $childrenArray : $childrenArray[$name];
    }

    /**
     * @return array
     */
    public function resolveChildren()
    {
        return [];
    }
}