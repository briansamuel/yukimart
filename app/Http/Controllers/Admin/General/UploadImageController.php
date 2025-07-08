<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Helpers\UploadImage;

class UpLoadImageController extends Controller
{
    protected $request;

    // Test Bug Class App\Http\Controllers\General\UpLoadImageController does not exist
    // Change Force
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
    * ======================
    * Method:: login
    * ======================
    */

    public function uploadImage()
    {
        $image = $this->request->file('file');
        
        $result = UploadImage::uploadAvatar($image, '/images/');

        return $result;
    }

    public function imageDestroy(Request $request)
    {
        $filename =  $request->get('filename');

        $result = UploadImage::deleteFile($filename);

        return response()->json($result);
    }

    
}
