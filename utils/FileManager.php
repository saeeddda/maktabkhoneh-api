<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/utils/utils.php';

class File_Manager
{
    public function UploadFile($image_file, $upload_dir, $upload_url){
        if(in_array($image_file['type'], VALID_UPLOAD_MIME)) {
            if($image_file['size'] < ConvertSizeNameToByte(MAX_AVATAR_FILE_SIZE)) {
                $new_name = date("YmdHis") . '.' . explode('.', $image_file['name'])[1];
                $new_destination = $upload_dir . $new_name;
                move_uploaded_file($image_file['tmp_name'], $new_destination);
                return $upload_url . $new_name;
            }
            return false;
        }
        return false;
    }

    public function RemoveOldFile($image_name, $destination): bool{
        $file_name = explode('/', $image_name);
        $new_destination = $destination . end($file_name);
        if(file_exists($new_destination)) {
            return unlink($new_destination);
        }
        return false;
    }
}