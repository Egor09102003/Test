<?php

return [
    'id' => 'typo3-monitor',
    'basePath' => dirname(__DIR__),
    'vendorPath' => __DIR__ . '/../../vendor',
    'bootstrap' => ['log'],
    'components' => [
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'nagiosDataRepository' => [
            'class' => 'app\components\NagiosDataRepository',
            'filesPath' => '',
            'signature' => ''
        ]
    ],
];