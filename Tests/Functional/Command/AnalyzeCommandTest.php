<?php

namespace Sli\ConfigsCompiler\Tests\Functional;

use Sli\ConfigsCompiler\Application;
use Sli\ConfigsCompiler\Command\AnalyzeCommand;
use Sli\ConfigsCompiler\Tests\Fixtures\DummyConfigsLayout;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class AnalyzeCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        $configsFactory = new DummyConfigsLayout();

        $app = new Application();
        $app->setAutoExit(false);

        $tester = new ApplicationTester($app);
        $tester->run(array(
            AnalyzeCommand::NAME,
            'seed-path' => [$configsFactory->url()]
        ));

        $expectedOutput = <<<OUTPUT
+--------------------+---------------+--------------------------------------+--------------------+
| Name               | Default value | Description                          | Declared in & Line |
+--------------------+---------------+--------------------------------------+--------------------+
| stock_endpoints    |               | API URLs to use to push orders data  | firewall.yml:2     |
| stock_api_username |               | Username used to authenticate to API | stock.yml:3        |
| stock_api_password |               | Password used to authenticate to API | stock.yml:4        |
| image_resizer_lib  | gd            |                                      | thumbnails.yml:2   |
+--------------------+---------------+--------------------------------------+--------------------+

OUTPUT;

        $this->assertEquals($expectedOutput, $tester->getDisplay());
    }
}