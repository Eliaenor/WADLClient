<?php

namespace WADLClient\Tools\Misc;

Interface XmlNodeInterface
{
    /**
     * @param string $name
     * @return null|string
     */
    public function getAttribute(string $name): ?string;

    /**
     * @return array
     */
    public function getAttributesList(): array;

    /**
     * @return XmlNodeInterface[]
     */
    public function getChildren(): array;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string|null $name
     * @return XmlNodeInterface[]
     */
    public function getChildrenByName(string $name = null): array;

    /**
     * @return array
     */
    public function resolve(): array;
}