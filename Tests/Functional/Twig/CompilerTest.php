<?php

namespace Sli\ConfigsCompiler\Tests\Twig;

use Sli\ConfigsCompiler\ConfigPropertyDeclaration;
use Sli\ConfigsCompiler\Tests\Fixtures\DummyConfigsLayout;
use Sli\ConfigsCompiler\Twig\AnalysisResult;
use Sli\ConfigsCompiler\Twig\Compiler;
use Sli\ConfigsCompiler\ConfigPropertyDeclaration as CPD;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.org>
 */
class CompilerTest extends \PHPUnit_Framework_TestCase
{
    public function testAnalyze()
    {
        $dummyFiles = new DummyConfigsLayout();

        $compiler = new Compiler();

        $result = $compiler->analyze([$dummyFiles->url()]);

        $this->assertInstanceOf(AnalysisResult::class, $result);

        $props = $result->getPropertyRegistry()->all();

        $this->assertEquals(4, count($props));

        /* @var ConfigPropertyDeclaration[] $indexedProps */
        $indexedProps = array();
        foreach ($props as $dec) {
            $indexedProps[$dec->getName()] = $dec;
        }

        $this->assertArrayHasKey('image_resizer_lib', $indexedProps);
        $imageResizerLib = $indexedProps['image_resizer_lib'];
        $this->assertNull($imageResizerLib->getDescription());
        $this->assertEquals('gd', $imageResizerLib->getDefaultValue());
        $this->assertEquals(CPD::TYPE_UNIT, $imageResizerLib->getType());

        $this->assertArrayHasKey('stock_api_username', $indexedProps);
        $stockApiUsername = $indexedProps['stock_api_username'];
        $this->assertEquals('Username used to authenticate to API', $stockApiUsername->getDescription());
        $this->assertNull($stockApiUsername->getDefaultValue());
        $this->assertEquals(CPD::TYPE_UNIT, $stockApiUsername->getType());

        $this->assertArrayHasKey('stock_api_password', $indexedProps);
        $stockApiPassword = $indexedProps['stock_api_password'];
        $this->assertEquals('Password used to authenticate to API', $stockApiPassword->getDescription());
        $this->assertNull($stockApiPassword->getDefaultValue());
        $this->assertEquals(CPD::TYPE_UNIT, $stockApiPassword->getType());

        $this->assertArrayHasKey('stock_endpoints', $indexedProps);
        $stockEndpoints = $indexedProps['stock_endpoints'];
        $this->assertEquals('API URLs to use to push orders data', $stockEndpoints->getDescription());
        $this->assertNull($stockEndpoints->getDefaultValue());
        $this->assertEquals(CPD::TYPE_ARRAY, $stockEndpoints->getType());
    }
}