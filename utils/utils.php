<?php

function GetAppUrl(){
    return sprintf(
        "%s://%s",
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $_SERVER['SERVER_NAME']
    );
}

function ConvertByteToSizeName($bytes)
{
    if ($bytes >= 1073741824)
    {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    }
    elseif ($bytes >= 1048576)
    {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    }
    elseif ($bytes >= 1024)
    {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    }
    elseif ($bytes > 1)
    {
        $bytes = $bytes . ' bytes';
    }
    elseif ($bytes == 1)
    {
        $bytes = $bytes . ' byte';
    }
    else
    {
        $bytes = '0 bytes';
    }

    return $bytes;
}

function ConvertSizeNameToByte(string $from) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    $number = substr($from, 0, -2);
    $suffix = strtoupper(substr($from,-2));

    //B or no suffix
    if(is_numeric(substr($suffix, 0, 1))) {
        return preg_replace('/[^\d]/', '', $from);
    }

    $exponent = array_flip($units)[$suffix] ?? null;
    if($exponent === null) {
        return null;
    }

    return $number * (1024 ** $exponent);
}

function GenerateActivateToken($digit = 6){
    switch ( $digit ) {
        case 4:
            $code = rand( 1000, 9999 );
            break;
        case 6:
            $code = rand( 100000, 999999 );
            break;
        case 7:
            $code = rand( 1000000, 9999999 );
            break;
        case 8:
            $code = rand( 10000000, 99999999 );
            break;
        case 9:
            $code = rand( 100000000, 999999999 );
            break;
        case 10:
            $code = rand( 1000000000, 9999999999 );
            break;
        default:
            $code = rand( 10000, 99999 );
            break;
    }
    return $code;
}

function GetExpireTime($create_time, $expire_day = 7){
    return $create_time + (86400 * intval($expire_day));
}