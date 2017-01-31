<?php

namespace Sli\ConfigsCompiler\Command;

use Sli\ConfigsCompiler\Application;
use Sli\ConfigsCompiler\PropParser;
use Sli\ConfigsCompiler\Twig\Compiler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class CompileCommand extends Command
{
    const NAME = 'sli:cg:compile';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Compiles configuration files found in "source" using given "props"')
            ->addArgument(
                'source',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Where to look for files'
            )
            ->addOption(
                'config',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Optional YML or JSON files with configuration keys'
            )
            ->addOption(
                'dump-props-file',
                null,
                InputOption::VALUE_OPTIONAL,
                'If provided then props used to compile templates will be dumped to this file'
            )
            ->addOption('prop', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY)
            ->addOption('backup', null, InputOption::VALUE_OPTIONAL, '', true)
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

        /* @var PropParser $propParser */
        $propParser = $container->get('prop_parser');

        /* @var Compiler $compiler */
        $compiler = $container->get('compiler');
        $analysisResult = $compiler->analyze($input->getArgument('source'));

        $renderedTemplates = $analysisResult->compile(
            $propParser->parseMultiple($analysisResult->getPropertyRegistry(), $input->getOption('prop'))
        );

        // Creating in-memory backup of files that are going to be modified
        $backup = array();
        if (true == $input->getOption('backup')) {
            foreach ($renderedTemplates as $pathname => $template) {
                $backup[$pathname] = file_get_contents($pathname);
            }
        }

        $compiledIndex = 0;
        try {
            foreach ($renderedTemplates as $pathname => $contents) {
                if (false === file_put_contents($pathname, $contents)) {
                    throw new \RuntimeException(sprintf('Unable to compile file "%s", aborting ...', $pathname));
                }

                $compiledIndex++;
            }
        } catch (\Exception $e) {
            $backupIndex = 0;
            foreach ($backup as $pathname => $originalContents) {
                // restoring only those files which were actually modified
                if ($compiledIndex == $backupIndex) {
                    break;
                }

                file_put_contents($pathname, $originalContents);

                $backupIndex++;
            }

            throw $e;
        }

//        $optionDumpPropsFile = $input->getOption('dump-props-file');
//        if ($optionDumpPropsFile) {
//            /* @var ConfigsHandler $configsHandler */
//            $configsHandler = $app->getContainer()->get('configs_handler');
//
//            $usedProps = array();
//            foreach ($ext->getAnalyzingNodeVisitor()->configDeclarations as $declaration) {
//                $usedProps[$declaration->name] = $declaration->defaultValue;
//            }
//            foreach ($parsedProps as $propName => $value) {
//                $usedProps[$propName] = $value;
//            }
//
//            // TODO
//        }
    }
}