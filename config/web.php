<?php
require __DIR__ . '/const.php';

$params = require __DIR__ . '/params.php';

if (file_exists(__DIR__ . '/../../db.php')) {
    $db = require(__DIR__ . '/../../db.php');
} else {
    $db = require __DIR__ . '/db.php';
}

$config = [
    'id' => 'basic',
    'language' => 'ru-RU',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'timeZone' => 'Europe/Moscow',
    'defaultRoute' => DEFAULT_ROUTE,
    'components' => [
        'formatter' => [
            'defaultTimeZone'=>'Europe/Moscow',
            'class' => 'yii\i18n\Formatter',
            'dateFormat' => 'd MMMM yyyy',
        ],
        'request' => [
            'cookieValidationKey' => 'SNs6dZeRI7ejrpPMmJ4Riryy3CvGZS0E',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
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
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [

            ],
        ],

    ],
    'params' => $params,
];


if (file_exists(__DIR__ . '/../../api_zoho.php')) {
    $api_zoho = require(__DIR__ . '/../../api_zoho.php');
} else {
    $api_zoho = require(__DIR__ . 'api_zoho.php');
}
$config['params']['api_zoho'] = $api_zoho;



if (GII) {

    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
