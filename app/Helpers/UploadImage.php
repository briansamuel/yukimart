<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Validator;

class UploadImage
{
    /*
* function ajax upload avatar
*/

    public static function uploadAvatar($image, $category)
    {
        $result['success'] = false;
        $result['message'] = "File không hợp lệ !";
        $result['url'] = "";

        $file = array('image' => $image);

        $rules = array(
            'image' => 'mimes:jpeg,jpg,png|required|max:10000' // max 10000kb
        );

        $validator = Validator::make($file, $rules);
        if ($validator->fails()) {
            return $result;
        }

        if ($image->isValid()) {
            $cur = date('mY', microtime(true));
            $destinationPath = 'uploads/'.$category."/";
            if (!is_dir('./' . $destinationPath)) {
                mkdir('./' . $destinationPath, 0777, true);
            }
            $fileName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extensionName = $image->getClientOriginalExtension();

            $fileName = Str::slug($fileName, "-");
            $fileName = $fileName . "." . $extensionName;

            if (file_exists($destinationPath . $fileName)) {
                $_fileName = $fileName;
                while ($_fileName == $fileName) {
                    $fileName = rand(111, 99999) . '-' . $fileName;
                }
            }
            $image->move($destinationPath, $fileName); // uploading file to given path

            $result['success'] = true;
            $result['message'] = "Upload ảnh thành công !!!";
            $result['url'] = $destinationPath . $fileName;
        } else {
            $result['success'] = false;
            $result['message'] = "Lỗi khi upload file !";
            $result['url'] = "";
        }

        return $result;
    }

    public static function deleteFile($filename)
    {
        $path=public_path()."/".$filename;

        if (file_exists($path)) {
            unlink($path);
            return ['success' => true];
        }

        return ['success' => false];
    }

}