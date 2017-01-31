<?php

namespace Sli\ConfigsCompiler\Twig;

use Sli\ConfigsCompiler\ConfigPropertyDeclaration;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Twig_LoaderInterface;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class Environment extends \Twig_Environment
{
    /**
     * @var Extension
     */
    private $extension;

    /**
     * {@inheritdoc}
     */
    public function __construct(Twig_LoaderInterface $loader, array $options = array())
    {
        parent::__construct($loader, $options);

        $this->extension = new Extension();

        $this->addExtension($this->extension);
    }

    /**
     * @param array $paths
     *
     * @return ConfigPropertyDeclaration[]
     */
    public static function analyze(array $paths)
    {
        $me = new static(new \Twig_Loader_Filesystem($paths));

        $me->loadTemplates($paths);

        return $me->extension->getAnalyzingNodeVisitor()->configDeclarations;
    }

    private function loadTemplates(array $paths)
    {
        $finder = Finder::create();
        foreach ($paths as $path) {
            $finder->in($path);
        }
        $finder->files();

        // Analyzing templates
        /* @var \Twig_TemplateWrapper[] $templates */
        $templates = array();
        foreach ($finder as $file) {
            /* @var SplFileInfo $file */
            $templates[$file->getPathname()] = $this->load($file->getRelativePathname());
        }

        return $templates;
    }

    public function compile(array $paths, array $props)
    {

    }

}