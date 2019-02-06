<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'alarm-robot-console',
    'language' => 'ru',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components' => [
        'formatter' => [
            'timeZone' => 'Europe/Minsk',
            'dateFormat' => 'd.m.Y',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'db2' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=prod', // Maybe other DBMS such as psql (PostgreSQL),...
            'username' => 'site',
            'password' => 'uGahch7e',
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'email-smtp.us-west-2.amazonaws.com',
                'username' => 'AKIAINCE6WE3QPMH7YDQ',
                'password' => 'Apu2aQP87uvyb02gxNpWyavurHogerS4Goh/xBZ/lWkp',
                'port' => '465',
                'encryption' => 'ssl',
            ],
        ],
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            'scriptUrl' => getenv('APP_ROLE') == 'production' ? 'http://alarm-robot.turbodealer.ru' : 'http://alarm-robot.lcl'
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'itemTable' => 'auth_item',
            'itemChildTable' => 'auth_item_child',
            'assignmentTable' => 'auth_assignment',
            'ruleTable' => 'auth_rule'
        ],
    ],
    'params' => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
