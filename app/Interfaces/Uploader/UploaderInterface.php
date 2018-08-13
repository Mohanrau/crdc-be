<?php
namespace App\Interfaces\Uploader;

use Illuminate\Http\Request;

interface UploaderInterface
{
    /**
     * upload file function
     *
     * @param Request $request
     * @param strinf $fileType
     * @param mixed $setting
     * @return mixed
     */
    public function processUploadFile(Request $request, $fileType, $setting);

    /**
     * download private file
     * 
     * @param string $pathType
     * @param string $fileType
     * @param string $fileData
     * @param string $fileTime
     * @param string $fileHash
     * @return mixed
     */
    public function downloadPrivateFile(string $pathType, string $fileType, string $fileData, string $fileTime, string $fileHash);
}