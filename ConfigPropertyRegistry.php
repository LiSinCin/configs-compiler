<?php

namespace Sli\ConfigsCompiler;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class ConfigPropertyRegistry
{
    private $indexedProperties = array();

    /**
     * @param ConfigPropertyDeclaration[] $properties
     */
    public function __construct(array $properties)
    {
        foreach ($properties as $property) {
            $this->indexedProperties[$property->getName()] = $property;
        }
    }

    /**
     * @return ConfigPropertyDeclaration[]
     */
    public function all()
    {
        return array_values($this->indexedProperties);
    }

    /**
     * @param string $name
     *
     * @return ConfigPropertyDeclaration|null
     */
    public function get($name)
    {
        return $this->has($name) ? $this->indexedProperties[$name] : null;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($this->indexedProperties[$name]);
    }
}