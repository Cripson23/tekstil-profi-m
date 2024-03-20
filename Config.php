<?php

class Config {
    const DATABASE = [
        'DB_HOST' => 'localhost',
        'DB_NAME' => '',
        'DB_USER' => '',
        'DB_PASSWORD' => '',
        'DB_CHARSET' => 'utf8mb4',
    ];

	const COMPANY = [
	  	'NAME' => 'Tekstil-Profi M',
		'ADDRESS' => 'Люберцы ул. Хлебозаводская 9',
		'TIME_WORK' => 'Время работы с 8:00 - 19:00',
		'PHONE_1' => '+7-977-957-16-62',
		'PHONE_2' => '+7-965-245-46-53',
		'EMAIL' => 'tekstil-profi@inbox.ru',
	  	'LOGO_1' => 'Tekstil-',
	  	'LOGO_2' => 'Profi M',
        'ADMIN_EMAIL' => ''
	];

    const MAIL = [
        'HOST' => '',
        'USERNAME' => '',
        'PASSWORD' => '',
        'PORT' => 465
    ];

	const MAIN_INDEX_PRODUCT_ID = 15;
}