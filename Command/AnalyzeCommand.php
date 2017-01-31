<?php

namespace Sli\ConfigsCompiler\Command;

use Sli\ConfigsCompiler\Application;
use Sli\ConfigsCompiler\Twig\Compiler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class AnalyzeCommand extends Command
{
    const NAME = 'sli:cg:analyze';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Analyses files and prints info regarding discovered configuration keys')
            ->addArgument('seed-path', InputArgument::REQUIRED | InputArgument::IS_ARRAY)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var Application $app */
        $app = $this->getApplication();
        $container = $app->getContainer();

        /* @var Compiler $compiler */
        $compiler = $container->get('compiler');

        $analysis = $compiler->analyze($input->getArgument('seed-path'));

        $table = new Table($output);
        $table->setHeaders(['Name', 'Default value', 'Description', 'Declared in & Line']);

        $rows = [];
        foreach ($analysis->getPropertyRegistry()->all() as $declaration) {
            $rows[] = [
                $declaration->getName(),
                $declaration->getDefaultValue(),
                $declaration->getDescription(),
                $declaration->getTemplateName().':'.$declaration->getTemplateLine(),
            ];
        }

        $table->setRows($rows);

        $table->render();
    }
}