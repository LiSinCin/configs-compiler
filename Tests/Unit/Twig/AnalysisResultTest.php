<?php

namespace Sli\ConfigsCompiler\Tests\Unit\Twig;

use Sli\ConfigsCompiler\ConfigPropertyRegistry;
use Sli\ConfigsCompiler\Twig\AnalysisResult;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class AnalysisResultTest extends \PHPUnit_Framework_TestCase
{
    public function testCompile()
    {
        $props = array(
            'foo' => 'fooval',
        );

        $twig = \Phake::mock(\Twig_Environment::class);
        $templates = array(
            'foo.yml' => $this->createMockTemplate($props, 'compiled_foo'),
            'bar.yml' => $this->createMockTemplate($props, 'compiled_bar'),
        );

        $ar = new AnalysisResult($twig, $templates, \Phake::mock(ConfigPropertyRegistry::class));

        $renderedTemplates = $ar->compile($props);

        $expectedCompiledTemplates = array(
            'foo.yml' => 'compiled_foo',
            'bar.yml' => 'compiled_bar',
        );

        $this->assertEquals($expectedCompiledTemplates, $renderedTemplates);
    }

    private function createMockTemplate($props, $renderedResult)
    {
        $tpl = \Phake::mock(\Twig_Template::class);
        \Phake::when($tpl)
            ->render(array('props' => $props))
            ->thenReturn($renderedResult)
        ;

        return $tpl;
    }
}