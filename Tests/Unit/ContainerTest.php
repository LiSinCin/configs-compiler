<?php

namespace Sli\ConfigsCompiler\Tests\Unit;

use Sli\ConfigsCompiler\ConfigsHandler;
use Sli\ConfigsCompiler\Container;
use Sli\ConfigsCompiler\PropParser;
use Sli\ConfigsCompiler\Twig\Compiler;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        $this->container = new Container();
    }

    public function testGet_propParser()
    {
        $this->assertInstanceOf(PropParser::class, $this->container->get('prop_parser'));
    }

    public function testGet_configsHandler()
    {
        $this->assertInstanceOf(ConfigsHandler::class, $this->container->get('configs_handler'));
    }

    public function testGet_compiler()
    {
        $this->assertInstanceOf(Compiler::class, $this->container->get('compiler'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Service with ID "foo" is not declared.
     */
    public function testGet_undefinedService()
    {
        $this->container->get('foo');
    }

    public function testGet_thatServicesAreSingletons()
    {
        $this->assertSame(
            $this->container->get('compiler'),
            $this->container->get('compiler'),
            'Container must always return the same instances of a service.'
        );
    }
}