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

use SebastianBergmann\DeLegacyFy\CharacterizationTestGenerator;

use Symfony\Component\Console\Command\Command as AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCharacterizationTestCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('generate-characterization-test')
            ->setDescription('Generates a characterization test for a function or method')
            ->addArgument(
                'unit',
                InputArgument::REQUIRED,
                'Function or method to be characterized'
            )
            ->addArgument(
                'trace-file',
                InputArgument::REQUIRED,
                'Xdebug trace file'
            )
            ->addArgument(
                'test-class',
                InputArgument::REQUIRED,
                'Name of the test class'
            )
            ->addArgument(
                'test-class-file',
                InputArgument::REQUIRED,
                'File to which to write the test code to'
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
        $testClass     = $input->getArgument('test-class');
        $testClassFile = $input->getArgument('test-class-file');

        $generator = new CharacterizationTestGenerator;

        $generator->generate(
            $input->getArgument('trace-file'),
            $input->getArgument('unit'),
            $testClass,
            $testClassFile
        );

        $output->writeln(
            \sprintf(
                'Generated class "%s" in file "%s"',
                $testClass,
                $testClassFile
            )
        );
    }
}
