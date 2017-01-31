<?php

namespace Sli\ConfigsCompiler\Twig;

use Twig_Filter;
use Twig_Function;
use Twig_NodeVisitorInterface;
use Twig_Test;
use Twig_TokenParserInterface;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class Extension implements \Twig_ExtensionInterface
{
    /**
     * @var AnalyzingNodeVisitor
     */
    private $analyzingNodeVisitor;

    /**
     * @return AnalyzingNodeVisitor
     */
    public function getAnalyzingNodeVisitor()
    {
        if (!$this->analyzingNodeVisitor) {
            $this->analyzingNodeVisitor = new AnalyzingNodeVisitor();
        }

        return $this->analyzingNodeVisitor;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeVisitors()
    {
        return [
            $this->getAnalyzingNodeVisitor(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getTests()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_Function('property', [$this, 'fn_property'], array(
                'needs_context' => true,
            )),
            new \Twig_Function('array_property', [$this, 'fn_property'], array(
                'needs_context' => true,
            )),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getOperators()
    {
        return [];
    }

    public function fn_property(array $context, $key, $defaultValue = null, $description = null, $type = null)
    {
        $property = $this->getAnalyzingNodeVisitor()->getPropertyRegistry()->get($key);

        // Must have been given to Template->render() method
        $props = $context['props'];

        $resolvedValue = null;
        if (isset($props[$key])) {
            $resolvedValue = $props[$key];
        } else {
            if ($defaultValue) {
                $resolvedValue = $defaultValue;
            } else {
                throw new \RuntimeException(sprintf(
                    'Neither explicit value nor a default one is provided for configuration property "%s".',
                    $key
                ));
            }
        }

        return $resolvedValue;
    }
}