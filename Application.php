<?php

namespace Sli\ConfigsCompiler;

use Sli\ConfigsCompiler\Command\AnalyzeCommand;
use Sli\ConfigsCompiler\Command\CompileCommand;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class Application extends SymfonyApplication
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @return Container
     */
    public function getContainer()
    {
        if (!$this->container) {
            $this->container = new Container();
        }

        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        return array_merge(parent::getDefaultCommands(), [
            new AnalyzeCommand(),
            new CompileCommand(),
        ]);
    }
}