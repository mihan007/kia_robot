<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => getenv('APP_ROLE') == 'production' ? 'mysql:host=localhost;dbname=alarmdb' : 'mysql:host=localhost;dbname=alarm',
    'username' => getenv('APP_ROLE') == 'production' ? 'alarmuser' : 'root',
    'password' => getenv('APP_ROLE') == 'production' ? 'CW8OFDmz1yp6' : '',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
