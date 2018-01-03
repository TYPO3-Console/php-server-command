<?php
return [
    'commands' => [
        'server:run' => [
            'class' => \Typo3Console\PhpServer\Command\ServerRunCommand::class,
        ],
    ],
    'runLevels' => [
        'server:run' => \Helhum\Typo3Console\Core\Booting\RunLevel::LEVEL_COMPILE,
    ],
];
