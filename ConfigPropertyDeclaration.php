<?php

namespace Sli\ConfigsCompiler;

/**
 * Represents a configuration property found in a template.
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class ConfigPropertyDeclaration
{
    /**
     * Configuration property is meant to contain on one scalar value.
     *
     * @var string
     */
    const TYPE_UNIT = 'unit';

    /**
     * Configuration property can contain multiple scalar values.
     *
     * @var string
     */
    const TYPE_ARRAY = 'array';

    /**
     * @var string[]
     */
    const SUPPORTED_TYPES = [self::TYPE_UNIT, self::TYPE_ARRAY];

    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $templateName;

    /**
     * @var integer
     */
    private $templateLine;

    /**
     * @var string
     */
    private $type = self::TYPE_UNIT;

    /**
     * @param string $name
     * @param string $defaultValue
     * @param string $description
     * @param string $templateName
     * @param int $templateLine
     * @param string $type
     */
    public function __construct(
        $name, $defaultValue = null, $description = null, $templateName = null, $templateLine = null, $type = self::TYPE_UNIT
    )
    {
        $this->name = $name;
        $this->defaultValue = $defaultValue;
        $this->description = $description;
        $this->templateName = $templateName;
        $this->templateLine = $templateLine;

        if (!in_array($type, self::SUPPORTED_TYPES)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid config declaration at %s:%d, only these types are supported: %s, but "%s" is given instead',
                $this->templateName, $this->templateLine, implode(', ', self::SUPPORTED_TYPES),
                $type
            ));
        }

        $this->type = $type;

    }

    /**
     * @internal
     *
     * @param \Twig_Node_Expression_Function $node
     * @param string $type
     *
     * @return ConfigPropertyDeclaration
     */
    public static function createFromNode(\Twig_Node_Expression_Function $node, $type)
    {
        // we could have used "iterator_to_array" but then unit test would have gotten more complicated
        /* @var \Twig_Node[] $args */
        $args = [];
        foreach ($node->getNode('arguments') as $arg) {
            $args[] = $arg;
        }

        if (!isset($args[0])) {
            throw new \DomainException(sprintf(
                "Every config property declaration must have a 'name' specified, please fix %s:%d.",
                $node->getTemplateName(),
                $node->getTemplateLine()
            ));
        }

        return new static(
            $args[0]->getAttribute('value'), // name
            isset($args[1]) ? $args[1]->getAttribute('value') : null, // default value
            isset($args[2]) ? $args[2]->getAttribute('value') : null, // description
            $node->getTemplateName(),
            $node->getTemplateLine(),
            $type
        );
    }

    /**
     * If $other has more details than $this then $this will be updated with $other's contents.
     *
     * @param ConfigPropertyDeclaration $other
     */
    public function merge(ConfigPropertyDeclaration $other)
    {
        if ($this->getName() != $other->getName()) {
            throw new \DomainException(sprintf(
                "Names of properties to be merged must always match, but given %s doesn't match %s",
                $other->getName(), $this->getName()
            ));
        }

        if (strlen($other->getDescription()) > strlen($this->getDescription())) {
            $this->description = $other->getDescription();
        }

        if ($other->getDefaultValue()) {
            // if $this value is not set yet then we are simply setting it
            if (!$this->defaultValue) {
                $this->defaultValue = $other->getDefaultValue();
            } else {
                // but if $this's defaultValue is already set but $other's one doesn't match it then
                // we will inform the user because inconsistent defaultValues might lead to tricky bugs
                if ($other->getDefaultValue() != $this->getDefaultValue()) {
                    $msg = implode(' ', [
                        'Configuration properties which share the same name must always have identical default',
                        'value set for them, please fix declarations of property "%s" in %s:%d.',
                        '%s:%d so they would match.'
                    ]);
                    $msg = sprintf(
                        $msg,
                        $this->getName(),
                        $this->getTemplateName(), $this->getTemplateLine(),
                        $other->getTemplateName(), $other->getTemplateLine()
                    );

                    throw new \DomainException($msg);
                }
            }
        }

        // this might happen when in one place config property is declared as "unit" and in other
        // as "array"
        if ($this->getType() != $other->getType()) {
            $msg = implode(' ', [
                "Properties with the same name must always have identical type set for them, but %s:%d declaration",
                'type is "%s" and %s:%d is "%s".'
            ]);
            $msg = sprintf(
                $msg,
                $this->getTemplateName(), $this->getTemplateLine(), $this->getType(),
                $other->getTemplateName(), $other->getTemplateLine(), $other->getType()
            );

            throw new \DomainException($msg);
        }
    }

    /**
     * @return bool
     */
    public function isUnit()
    {
        return $this->getType() == self::TYPE_UNIT;
    }

    /**
     * @return bool
     */
    public function isArray()
    {
        return $this->getType() == self::TYPE_ARRAY;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }

    /**
     * @return int
     */
    public function getTemplateLine()
    {
        return $this->templateLine;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}