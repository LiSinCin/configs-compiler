<?php

namespace Sli\ConfigsCompiler\Twig;

use Sli\ConfigsCompiler\ConfigPropertyDeclaration;
use Sli\ConfigsCompiler\ConfigPropertyRegistry;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class AnalysisResult
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var \Twig_TemplateWrapper[]
     */
    private $templates;

    /**
     * @var ConfigPropertyDeclaration[]
     */
    private $propertyRegistry;

    /**
     * @param \Twig_Environment $twig
     * @param \Twig_TemplateWrapper[] $templates
     * @param ConfigPropertyRegistry $propertyRegistry
     */
    public function __construct(\Twig_Environment $twig, array $templates, ConfigPropertyRegistry $propertyRegistry)
    {
        $this->twig = $twig;
        $this->templates = $templates;
        $this->propertyRegistry = $propertyRegistry;
    }

    /**
     * @param array $props
     *
     * @return array
     */
    public function compile(array $props)
    {
        $renderedTemplates = array();

        foreach ($this->templates as $pathname => $template) {
            $renderedTemplates[$pathname] = $template->render(array('props' => $props));
        }

        return $renderedTemplates;
    }

    /**
     * @return ConfigPropertyRegistry
     */
    public function getPropertyRegistry()
    {
        return $this->propertyRegistry;
    }
}