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
    define('DB_NAME', 'maktab');

if(!defined('DB_CHARSET'))
    define('DB_CHARSET', 'utf8mb4');

if(!defined('DB_DRIVER'))
    define('DB_DRIVER', 'mysql');

if(!defined('JWT_PRIVATE_KEY'))
    define('JWT_PRIVATE_KEY', 'bQeThWmZq4t7w!z%C*F-JaNcRfUjXn2r');

if(!defined('AVATAR_UPLOAD_DIR'))
    define('AVATAR_UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/uploads/avatar/');

if(!defined('AVATAR_UPLOAD_URL'))
    define('AVATAR_UPLOAD_URL', '/uploads/avatar/');

if(!defined('POST_UPLOAD_DIR'))
    define('POST_UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/uploads/posts/');

if(!defined('POST_UPLOAD_URL'))
    define('POST_UPLOAD_URL', '/uploads/posts/');

if(!defined('STORY_UPLOAD_DIR'))
    define('STORY_UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/uploads/stories/');

if(!defined('STORY_UPLOAD_URL'))
    define('STORY_UPLOAD_URL', '/uploads/stories/');

if(!defined('MAX_AVATAR_FILE_SIZE'))
    define('MAX_AVATAR_FILE_SIZE', '4MB');

if(!defined('VALID_UPLOAD_MIME'))
    define('VALID_UPLOAD_MIME', ['image/jpg','image/png','image/jpeg']);

if(!defined('MAIL_HOST'))
    define('MAIL_HOST', '');

if(!defined('MAIL_PORT'))
    define('MAIL_PORT', '');

if(!defined('MAIL_USERNAME'))
    define('MAIL_USERNAME', '');

if(!defined('MAIL_PASSWORD'))
    define('MAIL_PASSWORD', '');

if(!defined('MAIL_NAME'))
    define('MAIL_NAME', '');

if(!defined('MAIL_FROM'))
    define('MAIL_FROM', '');