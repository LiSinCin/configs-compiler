<?php

namespace Sli\ConfigsCompiler\Twig;

use Sli\ConfigsCompiler\ConfigPropertyDeclaration;
use Sli\ConfigsCompiler\ConfigPropertyRegistry;
use Twig_Environment;
use Twig_Node;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class AnalyzingNodeVisitor implements \Twig_NodeVisitorInterface
{
    /**
     * @var ConfigPropertyDeclaration[]
     */
    private $propertyDeclarations = [];

    /**
     * {@inheritdoc}
     */
    public function enterNode(Twig_Node $node, Twig_Environment $env)
    {
        // ignoring simple nodes
        if (get_class($node) == 'Twig_Node') {
            return $node;
        }

        // here we are interested only in functions
        if ($node instanceof \Twig_Node_Expression_Function) {

            $nameAttr = $node->getAttribute('name');
            if (in_array($nameAttr, ['property', 'array_property'])) {
                $declaration = ConfigPropertyDeclaration::createFromNode(
                    $node,
                    'property' == $nameAttr ? ConfigPropertyDeclaration::TYPE_UNIT : ConfigPropertyDeclaration::TYPE_ARRAY
                );

                if (isset($this->propertyDeclarations[$declaration->getName()])) {
                    $this->propertyDeclarations[$declaration->getName()]->merge($declaration);
                } else {
                    $this->propertyDeclarations[$declaration->getName()] = $declaration;
                }
            }
        }

        return $node;
    }

    /**
     * @return ConfigPropertyRegistry
     */
    public function getPropertyRegistry()
    {
        return new ConfigPropertyRegistry($this->propertyDeclarations);
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Twig_Node $node, Twig_Environment $env)
    {
        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 0;
    }
}