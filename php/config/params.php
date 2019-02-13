<?php

return [
    'adminEmail' => 'mk@turbodealer.ru',
    'domainMain' => getenv('APP_ROLE') == 'production' ? 'http://kia-robot.turbodealer.ru' : 'http://alarm-robot.lcl',
    'turboDomainMain' => 'http://turbodealer.ru',
    'user.passwordResetTokenExpire' => 3600,
];
