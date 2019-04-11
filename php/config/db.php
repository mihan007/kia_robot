<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => getenv('APP_ROLE') == 'production' ? 'mysql:host=192.168.10.11;dbname=alarmdb' : 'mysql:host=localhost;dbname=alarm',
    'username' => getenv('APP_ROLE') == 'production' ? 'robot_user' : 'root',
    'password' => getenv('APP_ROLE') == 'production' ? 'ByYX8NPe6kbu' : '',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];