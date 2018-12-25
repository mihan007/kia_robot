<?php

return [
    'adminEmail' => 'admin@example.com',
    'domainMain' => getenv('APP_ROLE') == 'production' ? 'http://alarm-robot.turbodealer.ru' : 'http://alarm-robot.lcl',
    'turboDomainMain' => 'http://turbodealer.ru'
];
