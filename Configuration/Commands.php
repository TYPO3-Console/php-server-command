<?php
return [
    'server:run' => [
        'class' => \Typo3Console\PhpServer\Command\ServerRunCommand::class,
        'runLevel' => \Helhum\Typo3Console\Core\Booting\RunLevel::LEVEL_COMPILE,
    ],
];
