<?php

namespace Sli\ConfigsCompiler\Tests\Unit;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Sli\ConfigsCompiler\ConfigsHandler;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class ConfigsHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    protected function setUp()
    {
        $this->root = vfsStream::setup('root', null, array(
            'configs' => array(
                'config_foo.yml' => <<<YML
props:
    endpoints:
        - endpoint1
        - endpoint2
YML
            ,
                'config_bar.json' => <<<JSON
{
    "props": {
        "endpoints": [
            "endpoint3"
        ],
        "api": {
            "username": "bob",
            "password": 1234
         }
    }
}
JSON
                ,
                'config_baz.yml' => <<<YML
props:
    endpoints:
        - endpoint4
    api:
        password: 4321
        timeout: 60
YML
                ,
            )
        ));
    }

    public function testMerge()
    {
        $handler = new ConfigsHandler();
        $result = $handler->merge([
            $this->root->url().'/configs/config_foo.yml',
            $this->root->url().'/configs/config_bar.json',
            $this->root->url().'/configs/config_baz.yml',
        ]);

        // TODO in this case we want to override value for "api/password" and not create an array containing both
        // 1234 and 4321, for this we need to consult ConfigValueDeclaration
    }
}