<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/consts/configs.php';

class File_Manager
{
    public function Upload_Image($image_file){
        if(in_array(mime_content_type($image_file), VALID_AVATAR_MIME)) {
            if($image_file['size'] < convert_to_bytes(MAX_AVATAR_FILE_SIZE)) {
                $new_name = date("YmdHis") . '.' . explode('.', $image_file['name'])[1];
                $destination = AVATAR_DIR . $new_name;
                move_uploaded_file($image_file['tmp_name'], $destination);
                return AVATAR_URL . $new_name;
            }
        }
        return false;
    }

    public function Remove_Old_Image($image_name){
        if(file_exists($image_name)) {
            $file_name = explode('/', $image_name);
            $destination = AVATAR_DIR . end($file_name);
            unlink($destination);
        }
    }
}