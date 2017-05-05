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

use SebastianBergmann\Version;
use Symfony\Component\Console\Application as AbstractApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends AbstractApplication
{
    public function __construct()
    {
        $version = new Version('1.0.2', \dirname(\dirname(__DIR__)));
        parent::__construct('de-legacy-fy', $version->getVersion());

        $this->add(new GenerateCharacterizationTestCommand);
        $this->add(new WrapStaticApiCommand);
    }

    /**
     * Runs the current application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return int 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if (!$input->hasParameterOption('--quiet')) {
            $output->write(
                \sprintf(
                    "de-legacy-fy %s by Sebastian Bergmann.\n\n",
                    $this->getVersion()
                )
            );
        }

        if ($input->hasParameterOption('--version') ||
            $input->hasParameterOption('-V')) {
            exit;
        }

        parent::doRun($input, $output);
    }
}
