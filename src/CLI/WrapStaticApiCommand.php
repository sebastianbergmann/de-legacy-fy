<?php
/*
 * This file is part of de-legacy-fy.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\DeLegacyFy\CLI;

use SebastianBergmann\DeLegacyFy\PhpParserBasedClassParser;

use SebastianBergmann\DeLegacyFy\StaticApiWrapper;
use Symfony\Component\Console\Command\Command as AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WrapStaticApiCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('wrap-static-api')
             ->setDescription('Generates a wrapper for a static API class')
             ->addArgument(
                 'class',
                 InputArgument::REQUIRED,
                 'Name of the static API class'
             )
             ->addArgument(
                 'file',
                 InputArgument::REQUIRED,
                 'Source file that declares the static API class'
             )
             ->addOption(
                 'bootstrap',
                 null,
                 InputOption::VALUE_REQUIRED,
                 'Bootstrap script to be loaded before code analysis'
             );
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $wrapper = new StaticApiWrapper(new PhpParserBasedClassParser());

        $originalClass = $input->getArgument('class');
        $originalFile  = $input->getArgument('file');
        $wrapperClass  = $originalClass . 'Wrapper';
        $wrapperFile   = \str_replace('.php', 'Wrapper.php', $originalFile);

        $wrapper->generate(
            $originalClass,
            $originalFile,
            $wrapperClass,
            $wrapperFile,
            $input->getOption('bootstrap')
        );

        $output->writeln(
            \sprintf(
                'Generated class "%s" in file "%s"',
                $wrapperClass,
                $wrapperFile
            )
        );
    }
}
