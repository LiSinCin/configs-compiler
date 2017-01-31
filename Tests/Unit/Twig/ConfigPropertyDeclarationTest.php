<?php

namespace Sli\ConfigsCompiler\Tests\Unit\Twig;

use Sli\ConfigsCompiler\ConfigPropertyDeclaration as CPD;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class ConfigPropertyDeclarationTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructAndGetters()
    {
        $cfg = new CPD(
            'fooName',
            'fooDV',
            'fooDesc',
            'fooTplName',
            'fooTplLine',
            CPD::TYPE_UNIT
        );

        $this->assertEquals('fooName', $cfg->getName());
        $this->assertEquals('fooDV', $cfg->getDefaultValue());
        $this->assertEquals('fooDesc', $cfg->getDescription());
        $this->assertEquals('fooTplName', $cfg->getTemplateName());
        $this->assertEquals('fooTplLine', $cfg->getTemplateLine());
        $this->assertEquals(CPD::TYPE_UNIT, $cfg->getType());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp @.*only these types are supported.*@
     */
    public function testConstruct_withInvalidType()
    {
        new CPD(
            'fooName',
            'fooDV',
            'fooDesc',
            'fooTplName',
            'fooTplLine',
            'unknownType'
        );
    }

    public function testCreateFromNode()
    {
        $arguments = [
            $this->createArgumentMock('fooName'),
            $this->createArgumentMock('fooDV'),
            $this->createArgumentMock('fooDesc')
        ];

        $functionNode = \Phake::mock(\Twig_Node_Expression_Function::class);
        \Phake::when($functionNode)
            ->getNode('arguments')
            ->thenReturn($arguments)
        ;
        \Phake::when($functionNode)
            ->getTemplateName()
            ->thenReturn('fooTplName')
        ;
        \Phake::when($functionNode)
            ->getTemplateLine()
            ->thenReturn(17)
        ;

        $dec = CPD::createFromNode($functionNode, CPD::TYPE_UNIT);

        $this->assertInstanceOf(CPD::class, $dec);
        $this->assertEquals('fooName', $dec->getName());
        $this->assertEquals('fooDV', $dec->getDefaultValue());
        $this->assertEquals('fooDesc', $dec->getDescription());
        $this->assertEquals('fooTplName', $dec->getTemplateName());
        $this->assertEquals(17, $dec->getTemplateLine());
        $this->assertEquals(CPD::TYPE_UNIT, $dec->getType());
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessageRegExp @Every config property declaration must have a 'name' specified, please fix fooTplName:17@
     */
    public function testCreateFromNode_withoutName()
    {
        $functionNode = \Phake::mock(\Twig_Node_Expression_Function::class);
        \Phake::when($functionNode)
            ->getNode('arguments')
            ->thenReturn([])
        ;
        \Phake::when($functionNode)
            ->getTemplateName()
            ->thenReturn('fooTplName')
        ;
        \Phake::when($functionNode)
            ->getTemplateLine()
            ->thenReturn(17)
        ;

        CPD::createFromNode($functionNode, CPD::TYPE_UNIT);
    }

    private function createArgumentMock($value)
    {
        $node = \Phake::mock(\Twig_Node::class);
        \Phake::when($node)
            ->getAttribute('value')
            ->thenReturn($value)
        ;

        return $node;
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessageRegExp  @Names of properties to be merged must always match, but given barName doesn't match fooName@
     */
    public function testMerge_notMatchingName()
    {
        $cfg = new CPD('fooName');

        $cfgOther = new CPD('barName');

        $cfg->merge($cfgOther);
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessageRegExp @.*but foo.yml:4 declaration type is "unit" and bar.yml:7 is "array".*@
     */
    public function testMerge_noMatchingType()
    {
        $cfg = new CPD('fooName', null, null, 'foo.yml', 4, CPD::TYPE_UNIT);
        $cfgOther = new CPD('fooName', null, null, 'bar.yml', 7, CPD::TYPE_ARRAY);

        $cfg->merge($cfgOther);
    }

    public function testMerge_description()
    {
        $cfg = new CPD('fooName', null, 'foo');
        $cfgOther = new CPD('fooName', null, 'foobar');

        $cfg->merge($cfgOther);

        $this->assertEquals('foobar', $cfg->getDescription());

        // but at the same time description shouldn't be updated if it is shorter than original

        $cfg = new CPD('fooName', null, 'foo');
        $cfgOther = new CPD('fooName', null, 'foobar');

        $cfgOther->merge($cfg);

        $this->assertEquals('foo', $cfg->getDescription());
        $this->assertEquals('foobar', $cfgOther->getDescription());
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessageRegExp @.*please fix declarations of property "fooName" in fooTpl:3. barTpl:5 so they would match.*@
     */
    public function testMerge_notMatchingDefaultValue()
    {
        $cfg = new CPD('fooName', 'foo', null, 'fooTpl', 3);
        $cfgOther = new CPD('fooName', 'bar', null, 'barTpl', 5);

        $cfg->merge($cfgOther);
    }

    public function testMerge_happyPath()
    {
        $cfg = new CPD('fooName', null);
        $cfgOther = new CPD('fooName', 'bar');

        $cfg->merge($cfgOther);

        // it should be possible to merge because $cfg' default value is not set, it is NULL, that is
        $this->assertEquals('bar', $cfg->getDefaultValue());
    }
}