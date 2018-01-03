<?php
declare(strict_types=1);
namespace Typo3Console\PhpServer\Command;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Helmut Hummel <info@helhum.io>
 *  All rights reserved
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the text file GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class ServerRunCommand extends Command
{
    protected function configure()
    {
        $this->setDefinition(
            [
                new InputArgument(
                    'address',
                    InputArgument::OPTIONAL,
                    'Alternative IP address and port',
                    '127.0.0.1:8080'
                )
            ]
        )
        ->setDescription('Start a PHP web server for the current project');
    }

    /**
     * Start a PHP web server for the current project
     *
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $address = $input->getArgument('address');
        // Store current md5 of .env file
        $this->dotEnvChanged();
        $output->writeln(sprintf('<info>Server is running at http://%s</info>', $address));
        $output->writeln('Press Ctrl-C to quit.');

        do {
            $process = $this->getProcess($address);
            $process->disableOutput();
            $this->forceDotEnvVarsForSubProcess();
            $process->start();
            while ($process->isRunning()) {
                if ($this->dotEnvChanged()) {
                    $process->stop();
                    break;
                }
                sleep(1);
            }
        } while (true);
    }

    private function getProcess(string $address): Process
    {
        $arguments = [
            PHP_BINARY,
            '-S',
            $address,
            '-t',
            getenv('TYPO3_PATH_WEB'),
        ];

        if (class_exists(ProcessBuilder::class)) {
            $processBuilder = new ProcessBuilder($arguments);
            $processBuilder->setTimeout(null);
            return $processBuilder->getProcess();
        }

        return new Process(
            $arguments,
            null,
            null,
            null,
            null
        );
    }

    private function dotEnvChanged(): bool
    {
        $dotEnfFileName = getenv('TYPO3_PATH_COMPOSER_ROOT') . '/.env';
        static $dotEnvMd5;
        if (file_exists($dotEnfFileName) && $dotEnvMd5 !== md5_file($dotEnfFileName)) {
            $dotEnvMd5 = md5_file($dotEnfFileName);
            return true;
        }
        return false;
    }

    private function forceDotEnvVarsForSubProcess()
    {
        $dotEnfFileName = getenv('TYPO3_PATH_COMPOSER_ROOT') . '/.env';
        if (!class_exists(Dotenv::class) || !file_exists($dotEnfFileName)) {
            return;
        }
        $dotEnv = new Dotenv();
        foreach ($dotEnv->parse(file_get_contents($dotEnfFileName), $dotEnfFileName) as $name => $value) {
            putenv($name);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}
