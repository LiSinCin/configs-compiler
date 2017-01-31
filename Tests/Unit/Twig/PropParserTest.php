<?php

namespace Sli\ConfigsCompiler\Tests\Unit\Twig;

use Sli\ConfigsCompiler\ConfigPropertyDeclaration;
use Sli\ConfigsCompiler\ConfigPropertyRegistry;
use Sli\ConfigsCompiler\PropParser;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class PropParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParseSingle()
    {
        $parser = new PropParser();

        $result = $parser->parseSingle('foo=bar');

        $this->assertTrue(is_array($result));
        $this->assertEquals(2, count($result));
        $this->assertEquals('foo', $result[0]);
        $this->assertEquals('bar', $result[1]);

        $result = $parser->parseSingle('key=value1=value2');

        $this->assertTrue(is_array($result));
        $this->assertEquals(2, count($result));
        $this->assertEquals('key', $result[0]);
        $this->assertEquals('value1=value2', $result[1], 'Prop must have been split by first occurrence of =');
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Property 'foo:fooval' isn't properly formatted, correct syntax is where key and value are separated by =, for example api_key=1234
     */
    public function testParseSingle_invalidSyntax()
    {
        $parser = new PropParser();

        $parser->parseSingle('foo:fooval');
    }

    public function testParseMultiple()
    {
        $registry = \Phake::mock(ConfigPropertyRegistry::class);
        \Phake::when($registry)
            ->get('foo')
            ->thenReturn($this->createMockProperty(true))
        ;
        \Phake::when($registry)
            ->get('bar')
            ->thenReturn($this->createMockProperty(false))
        ;

        $parser = new PropParser();

        $result = $parser->parseMultiple($registry, [
            'foo=fooval1',
            'foo=fooval2',
            'bar=val1=val2',
        ]);

        $this->assertTrue(is_array($result));

        $expectedResult = array (
            'foo' => ['fooval1', 'fooval2'],
            'bar' =>'val1=val2',
        );

        $this->assertEquals($expectedResult, $result);
    }

    private function createMockProperty($isArray)
    {
        $property = \Phake::mock(ConfigPropertyDeclaration::class);
        \Phake::when($property)
            ->isArray()
            ->thenReturn($isArray)
        ;

        return $property;
    }
}