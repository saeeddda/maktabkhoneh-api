<?php

if(!defined('DB_HOST'))
    define('DB_HOST', 'localhost');

if(!defined('DB_PORT'))
    define('DB_PORT', '3306');

if(!defined('DB_USERNAME'))
    define('DB_USERNAME', 'root');

if(!defined('DB_PASSWORD'))
    define('DB_PASSWORD', '');

if(!defined('DB_NAME'))
    define('DB_NAME', 'sasan-api');

if(!defined('DB_CHARSET'))
    define('DB_CHARSET', 'utf8mb4');

if(!defined('DB_DRIVER'))
    define('DB_DRIVER', 'mysql');

if(!defined('JWT_PRIVATE_KEY'))
    define('JWT_PRIVATE_KEY', '3j4klv4j5v234$%@#i34ohv4787(*&78786HEJKR4RUIEORERkjnvsdfjh!@$#$@');

if(!defined('AVATAR_DIR'))
    define('AVATAR_DIR', $_SERVER['DOCUMENT_ROOT'] . '/uploads/avatar/');

if(!defined('AVATAR_URL'))
    define('AVATAR_URL', '/uploads/avatar/');

if(!defined('MAX_AVATAR_FILE_SIZE'))
    define('MAX_AVATAR_FILE_SIZE', '2MB');

if(!defined('VALID_AVATAR_MIME'))
    define('VALID_AVATAR_MIME', ['image/jpg','image/png','image/jpeg']);