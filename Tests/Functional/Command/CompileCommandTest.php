<?php

namespace Sli\ConfigsCompiler\Tests\Functional;

use Sli\ConfigsCompiler\Application;
use Sli\ConfigsCompiler\Command\CompileCommand;
use Sli\ConfigsCompiler\Tests\Fixtures\DummyConfigsLayout;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class CompileCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        $configsFactory = new DummyConfigsLayout();

        $app = new Application();
        $app->setAutoExit(false);

        $tester = new ApplicationTester($app);
        $tester->run(array(
            CompileCommand::NAME,
            '-v',
            'source' => [$configsFactory->url()],
            '--prop' => [
                'stock_endpoints=endpoint1',
                'stock_endpoints=endpoint2',
                'stock_api_username=john',
                'stock_api_password=1234'
            ],
        ));

        $this->assertEquals(0, $tester->getStatusCode());

        $expectedFirewallYmlContents = <<<YML
intercept:
    allowed_hosts: endpoint1,endpoint2
YML;

        $this->assertEquals($expectedFirewallYmlContents, file_get_contents($configsFactory->url().'/firewall.yml'));

        $expectedStockYmlContents = <<<YML
stock_provider:
    api:
        username: john
        password: 1234
    endpoints:
        - endpoint1
        - endpoint2

YML;

        $this->assertEquals($expectedStockYmlContents, file_get_contents($configsFactory->url().'/stock.yml'));

        $expectedThumbnailsYmlContents = <<<YML
image_resizer:
    lib: gd
YML;

        $this->assertEquals($expectedThumbnailsYmlContents, file_get_contents($configsFactory->url().'/thumbnails.yml'));
    }
}