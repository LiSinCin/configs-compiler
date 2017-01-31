<?php

namespace Sli\ConfigsCompiler\Tests\Fixtures;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class DummyConfigsLayout
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    /**
     * @var string
     */
    private $dir;

    public function __construct()
    {
        $this->dir = 'fixtures_'.uniqid();

        $this->root = vfsStream::setup('root', null, array(
             $this->dir => array(
                'firewall.yml' => <<<YML
intercept:
    allowed_hosts: {{ array_property('stock_endpoints')|join(',') }}
YML
                ,
                'stock.yml' => <<<YML
stock_provider:
    api:
        username: {{ property('stock_api_username', null, 'Username used to authenticate to API') }}
        password: {{ property('stock_api_password', null, 'Password used to authenticate to API') }}
    endpoints:
{% for ep in array_property('stock_endpoints', null, 'API URLs to use to push orders data') %}
        - {{ ep }}
{% endfor %}
YML
                ,
                'thumbnails.yml' => <<<YML
image_resizer:
    lib: {{ property('image_resizer_lib', 'gd') }}
YML
                ,
            )
        ));
    }

    /**
     * @return string
     */
    public function url()
    {
        return $this->root->url().'/'.$this->dir;
    }
}