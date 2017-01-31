<?php

namespace Sli\ConfigsCompiler\Twig;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class Compiler
{
    /**
     * @param array $paths
     * @param callable|null $finderConfigurator
     *
     * @return AnalysisResult
     */
    public function analyze(array $paths, callable $finderConfigurator = null)
    {
        $ext = new Extension();

        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem($paths));
        $twig->addExtension($ext);

        $finder = new Finder();
        foreach ($paths as $path) {
            $finder->in($path);
        }

        if ($finderConfigurator) {
            $finder = $finderConfigurator($finder);
        }

        // Analyzing templates
        /* @var \Twig_TemplateWrapper[] $templates */
        $templates = array();
        foreach ($finder as $file) {
            /* @var SplFileInfo $file */

            // By loading templates we are giving AnalyzingNodeVisitor a chance to analyze their contents
            $templates[$file->getPathname()] = $twig->load($file->getRelativePathname());
        }

        return new AnalysisResult(
            $twig,
            $templates,
            $ext->getAnalyzingNodeVisitor()->getPropertyRegistry()
        );
    }
}