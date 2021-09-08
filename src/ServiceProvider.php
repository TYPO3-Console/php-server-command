<?php
declare(strict_types=1);
namespace Typo3Console\PhpServer;

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

use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Console\CommandRegistry;
use TYPO3\CMS\Core\Package\AbstractServiceProvider;
use Typo3Console\PhpServer\Command\ServerRunCommand;

class ServiceProvider extends AbstractServiceProvider
{
    protected static function getPackagePath(): string
    {
        return __DIR__ . '/../';
    }

    public function getFactories(): array
    {
        return [
            ServerRunCommand::class => [ static::class, 'getServerRunCommand' ],
        ];
    }

    public function getExtensions(): array
    {
        return [
                CommandRegistry::class => [ static::class, 'configureCommands' ],
            ] + parent::getExtensions();
    }

    public static function getServerRunCommand(): ServerRunCommand
    {
        return new ServerRunCommand('server:run');
    }
    public static function configureCommands(ContainerInterface $container, CommandRegistry $commandRegistry): CommandRegistry
    {
        $commandRegistry->addLazyCommand('server:run', ServerRunCommand::class, 'Start a PHP web server for the current project');

        return $commandRegistry;
    }
}
