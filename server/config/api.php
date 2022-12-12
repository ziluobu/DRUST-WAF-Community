<?php

return [
    'jwt_key'           => env('JWT_KEY', 'ICWwIBAAKBgQCzs3vnkqwxwQrcvAUYQe2ymXO'),
    'login_num'         => env('LOGIN_NUM', 0),
    'token_expire_time' => env('TOKEN_EXPIRE_TIME', 0),
    'check_sign'        => env('CHECK_SIGN', 0),
    'check_ip'          => env('CHECK_IP', 0),
    'menu_html_tag'     => env('MENU_HTML_TAG', 'menuHtmlTag'),
    'url_tag'           => env('URL_TAG', 'urlTag'),
    'config_tag'        => env('CONFIG_TAG', 'configTag'),
    'sign_secret_key'   => env('SIGN_SECRET_KEY', 'GyCANZj6RT7ktqSL'),
    'parse_ip'          => env('PARSE_IP', '111.6.186.117'),
    'operation_log'     => [
        'enable'          => true,
        /*
         * Only logging allowed methods in the list
         */
        'allowed_methods' => [
            'POST',
            'PUT',
            'DELETE',
        ],
        /*
         * Routes that will not log to database.
         */
        'except'          => [
            'api/opertlog*',
            'api/*/searchList',
            'api/role/menuTree',
            'api/report/reportZdyDown',
            'api/report/reportMonthDown',
        ],
        'secret_fields'   => [
            'oldPassword',
            'newPassword',
        ],
    ],
    'scp_ip'            => [
        '172.20.77.86',
        '172.20.77.64',
    ],
    'allow_ip'          => [
        '172.20.77.86',
        '172.20.77.64',
        '127.0.0.1',
        '117.160.246.233',
        '117.160.246.232',
        '123.52.27.72',
        '172.20.77.79',
    ],
];
