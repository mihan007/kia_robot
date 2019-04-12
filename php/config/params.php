<?php

return [
    'adminEmail' => 'mk@turbodealer.ru',
    'domainMain' => getenv('APP_ROLE') == 'production' ? 'https://lk.robotkia.ru' : 'http://alarm-robot.lcl',
    'turboDomainMain' => 'http://turbodealer.ru',
    'user.passwordResetTokenExpire' => 3600,
];
