<?php
namespace App\Helpers\Classes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Uploader
{
    /**
     * get setting
     *
     * @param string $setting
     * @return mixed
     */
    public function getSetting($setting) {
        return config($setting);
    }

    /**
     * get uploader setting for all file types
     *
     * @param bool $cascade
     * @return mixed
     */
    public function getUploaderSetting($cascade) {
        $setting = $this->getSetting('setting.uploader');

        if ($cascade) {
            foreach (array_keys($setting) as $fileType) {
                if (is_string($setting[$fileType]['resize_image'])) {
                    $setting[$fileType]['resize_image'] = $this->getSetting($setting[$fileType]['resize_image']);
                }

                if (is_string($setting[$fileType]['water_mark'])) {
                    $setting[$fileType]['water_mark'] = $this->getSetting($setting[$fileType]['water_mark']);
                }
            }
        }

        return $setting;
    }

    /**
     * get uploader setting for particular file type
     *
     * @param string $fileType
     * @return mixed
     */
    public function getUploaderFileTypeSetting($fileType) {
        return $this->getUploaderSetting(true)[$fileType];
    }

    /**
     * ensure folder does not ended with /
     *
     * @param string $folder
     * @return mixed
     */
    public function getUploaderFolder($folder) {
        if (strlen($folder) > 0 && substr($folder, -1) === '/') {
            $folder = substr($folder, 0, strlen($folder) - 1);
        }
        return $folder;
    }

    /**
     * ensure path ended with /
     *
     * @param string $path
     * @return mixed
     */
    public function getUploaderPath($path) {
        if (strlen($path) > 0 && substr($path, -1) !== '/') {
            $path = $path . '/';
        }
        return $path;
    }

    /**
     * get file name, remove the path
     *
     * @param string $filePath
     * @return mixed
     */
    public function getFileName($filePath) {
        $filePart = explode('/', $filePath);
        return $filePart[count($filePart) - 1];
    }

    /**
     * get file link (fully qualified url)
     *
     * @param string $pathType
     * @param string $fileType
     * @param string $fileName
     * @return mixed
     */
    public function getFileLink($pathType, $fileType, $fileName) {
        if (isset($fileName)) {
            $setting = $this->getUploaderFileTypeSetting($fileType);
            if ($setting['public']) {
                if ($pathType == 'file') {
                    $fileRoot = config('setting.uploader-file-root');
                    $filePath = config('setting.uploader-file-path');
                    $fileLink = config('setting.uploader-file-link');
                    $fileTargetPath = $setting['file_path'];
                }
                else {
                    $fileRoot = config('setting.uploader-temp-root');
                    $filePath = config('setting.uploader-temp-path');
                    $fileLink = config('setting.uploader-temp-link');
                    $fileTargetPath = $setting['temp_path'];
                }
                
                $linkPrefix = $this->getUploaderPath($fileLink) . 
                    $this->getUploaderPath($fileRoot) . 
                    $this->getUploaderPath($filePath) . 
                    $this->getUploaderPath($fileTargetPath);
        
                return $linkPrefix . $fileName;
            }
            else {
                $fileLink = config('setting.uploader-private-file-link');
                $fileTime = Carbon::now()->format('YmdHis');
                $secretKey = $setting['secret_key'];
                
                $fileHash = $this->generateFileHash($pathType, $fileType, $fileName, $fileTime, $secretKey);

                return $this->getUploaderPath($fileLink) . 
                    $pathType . '/' . $fileType . '/' . $fileName . '/' . $fileTime . '/' . $fileHash;
            }
        }
        else {
            return null;
        }
    }

    /**
     * get file name with prefix and suffix
     *
     * @param string $fileName
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    public function getFileNameWithPrefixSuffix($fileName, $prefix, $suffix) {
        $result = $fileName;

        if ($prefix !== '' || $suffix !== '') {
            $fileUnit = explode('/', $fileName);

            $filePart = explode('.', $fileUnit[count($fileUnit) - 1]);

            if ($prefix !== '') {
                $filePart[0] = $prefix . $filePart[0];
            }
            
            if ($suffix !== '') {
                $filePart[0] = $filePart[0] . $suffix;
            }

            $fileUnit[count($fileUnit) - 1] = implode('.', $filePart);

            $result = implode('/', $fileUnit);
        }

        return $result;
    }

    /**
     * get random file name
     * 
     * @param string $fileType
     */
    public function getRandomFileName($fileType) 
    {
        return md5($fileType . Auth::id()) . md5(uniqid('', true));
    }

    /**
     * move local file to s3
     * 
     * @param string $localFile
     * @param string $remoteFile
     * @return string
     */
    public function moveLocalFileToS3($localFile, $remoteFile, $publicAccess) 
    {
        $handler = fopen($localFile, "r");

        $fileUrl = $this->createS3File($remoteFile, $handler, $publicAccess);

        fclose($handler);

        unlink($localFile);

        return $fileUrl;
    }

    /**
     * put stream to S3
     * 
     * @param string $remoteFile
     * @param mixed $fileContent
     * @return string
     */
    public function createS3File($remoteFile, $fileContent, $publicAccess)
    {
        $visibility = ($publicAccess) ? 'public' : 'private';

        Storage::disk("s3")->put($remoteFile, $fileContent, $visibility);

        return Storage::disk('s3')->url($remoteFile);
    }

    /**
     * download file (typically image) to local for processing
     * 
     * @param string $sourceUrl
     * @param string $fileName
     */
    public function downloadFile($sourceUrl, $fileName)
    {
        $curl = curl_init($sourceUrl);

        $file = fopen($fileName, 'w');
        
        curl_setopt($curl, CURLOPT_FILE, $file);
        
        curl_exec($curl);
        
        curl_close($curl);
        
        fclose($file);
    }

    /**
     * load image from file
     *
     * @param string $fileName
     * @return mixed
     */
    public function loadImage($fileName)
    {
        $filePart = explode(".", $fileName);

        $fileExtension = strtolower($filePart[count($filePart) - 1]);

        if ($fileExtension === 'bmp') {
            return imagecreatefrombmp($fileName);
        }
        elseif ($fileExtension === 'gd2') {
            return imagecreatefromgd2($fileName);
        }
        elseif ($fileExtension === 'gd') {
            return imagecreatefromgd($fileName);
        }
        elseif ((imagetypes() & IMG_GIF) && $fileExtension === 'gif') {
            return imagecreatefromgif($fileName);
        }
        elseif ((imagetypes() & IMG_JPG) && ($fileExtension === 'jpeg' || $fileExtension === 'jpg')) {
            //return imagecreatefromjpeg($fileName); //TODO: this trigger fatal error
            //return @imagecreatefromjpeg($fileName); //TODO: this suppress fatal error but subsequent code is not running
            try { //TODO: this is workaround to load jpeg image
                  return imagecreatefromstring(file_get_contents($fileName));
            } catch (Exception $ex) {
                  return false;
            }
        }
        elseif ((imagetypes() & IMG_PNG) && $fileExtension === 'png') {
            return imagecreatefrompng($fileName);
        }
        elseif ((imagetypes() & IMG_WBMP) && $fileExtension === 'wbmp') {
            return imagecreatefromwbmp($fileName);
        }
        elseif ((imagetypes() & IMG_WEBP) && $fileExtension === 'webp') {
            return imagecreatefromwebp($fileName);
        }
        elseif ($fileExtension === 'xbm') {
            return imagecreatefromxbm($fileName);
        }
        elseif ((imagetypes() & IMG_XPM) && $fileExtension === 'xpm') {
            return imagecreatefromxbm($fileName);
        }
        else {
            return false;
        }
    }

    /**
     * save image resouce to file
     *
     * @param mixed $imageResource
     * @param string $fileName
     * @return mixed
     */
    public function saveImage($imageResource, $fileName)
    {
        $filePart = explode(".", $fileName);
        
        $fileExtension = strtolower($filePart[count($filePart) - 1]);
        
        if ($fileExtension === 'bmp') {
            return imagebmp($imageResource, $fileName);
        }
        elseif ($fileExtension === 'gd2') {
            return imagegd2($imageResource, $fileName);
        }
        elseif ($fileExtension === 'gd') {
            return imagegd($imageResource, $fileName);
        }
        elseif ((imagetypes() & IMG_GIF) && $fileExtension === 'gif') {
            return imagegif($imageResource, $fileName);
        }
        elseif ((imagetypes() & IMG_JPG) && ($fileExtension === 'jpeg' || $fileExtension === 'jpg')) {
            return imagejpeg($imageResource, $fileName);
        }
        elseif ((imagetypes() & IMG_PNG) && $fileExtension === 'png') {
            return imagepng($imageResource, $fileName);
        }
        elseif ((imagetypes() & IMG_WBMP) && $fileExtension === 'wbmp') {
            return imagewbmp($imageResource, $fileName);
        }
        elseif ((imagetypes() & IMG_WEBP) && $fileExtension === 'webp') {
            return imagewebp($imageResource, $fileName);
        }
        elseif ($fileExtension === 'xbm') {
            return imagexbm($imageResource, $fileName);
        }
        elseif ((imagetypes() & IMG_XPM) && $fileExtension === 'xpm') {
            return imagexbm($imageResource, $fileName);
        }
        else {
            return false;
        }
    }

    /**
     * apply water mark to image
     *
     * @param string $sourceFileName
     * @param string $waterMarkFileName
     * @param string $horizontalPosition
     * @param string $verticalPosition
     * @param string $horizontalMargin
     * @param string $verticalMargin
     * @param string $opacity
     * @param bool $shrinkToFit
     * @param bool $stretchToFit
     * @return mixed
     */
    public function applyWaterMark($sourceFileName, $waterMarkFileName, $horizontalPosition, $verticalPosition, $horizontalMargin, $verticalMargin, $opacity, $shrinkToFit, $stretchToFit) 
    {
        $sourceImage = $this->loadImage($sourceFileName);
        
        if ($sourceImage) {
            $waterMarkImage = $this->loadImage($waterMarkFileName);

            if ($waterMarkImage) {
                //get image properties
                $sourceWidth = imagesx($sourceImage);
                $sourceHeight = imagesy($sourceImage);
                $waterMarkWidth = imagesx($waterMarkImage);
                $waterMarkHeight = imagesy($waterMarkImage);

                if (($shrinkToFit && ($waterMarkWidth > $sourceWidth || $waterMarkHeight > $sourceHeight)) || 
                   (($stretchToFit && $waterMarkWidth < $sourceWidth && $waterMarkHeight < $sourceHeight))) {
                    $maxHeight = $sourceHeight;

                    $maxWidth = $sourceWidth;

                    if ($horizontalPosition === 'left' || $horizontalPosition === 'right') { 
                        $maxWidth -= $horizontalMargin;
                    }

                    if ($verticalPosition === 'top' || $verticalPosition === 'bottom') { 
                        $maxHeight -= $verticalMargin;
                    }

                    if ($maxWidth > 0 && $maxHeight > 0) {
                        $waterMarkImageTemp = $waterMarkImage;
                    
                        $waterMarkImage = null;
    
                        $this->resizeVirtualImage($waterMarkImageTemp, $waterMarkImage, $maxHeight, $maxWidth, false, null);
                        
                        $waterMarkWidth = imagesx($waterMarkImage);
                        
                        $waterMarkHeight = imagesy($waterMarkImage);
    
                        imagedestroy($waterMarkImageTemp);
                    }
                }

                //calculate stamp position
                $destX = 0;
                if ($horizontalPosition === 'left') {
                    $destX = $horizontalMargin;
                }
                elseif ($horizontalPosition === 'right') {
                    $destX = $sourceWidth - $waterMarkWidth - $horizontalMargin;
                }
                else { //center
                    $destX = ($sourceWidth - $waterMarkWidth) / 2;
                }

                $destY = 0;
                if ($verticalPosition === 'top') {
                    $destY = $verticalMargin;
                }
                elseif ($verticalPosition === 'bottom') {
                    $destY = $sourceHeight - $waterMarkHeight - $verticalMargin;
                }
                else { //middle
                    $destY = ($sourceHeight - $waterMarkHeight) / 2;
                }
                
                //stamp the watermark
                if ($opacity === 100) {
                    imagecopy($sourceImage, $waterMarkImage, $destX, $destY, 0, 0, $waterMarkWidth, $waterMarkHeight);
                }
                else if ($opacity !== 0) {
                    imagecopymerge($sourceImage, $waterMarkImage, $destX, $destY, 0, 0, $waterMarkWidth, $waterMarkHeight, $opacity);
                } 

                //delete original image
                unlink($sourceFileName);

                //save the resulted image
                $this->saveImage($sourceImage, $sourceFileName);

                //free memory resource
                imagedestroy($waterMarkImage);
            }

            //free memory resource
            imagedestroy($sourceImage);
        }
    }

    /**
     * resize image resolution, maintaining its aspect ratio
     *
     * @param object $sourceImage
     * @param object $targetImage
     * @param int $maxHeight
     * @param int $maxWidth
     * @param bool $fullCanvas
     * @param array $backgroundColor
     * @return mixed
     */
    public function resizeVirtualImage(&$sourceImage, &$targetImage, $maxHeight, $maxWidth, $fullCanvas, $backgroundColor)
    {
        //calculate resize properties
        $currentWidth = imagesx($sourceImage);
        $currentHeight = imagesy($sourceImage);
        $currentAspect = $currentWidth / $currentHeight;

        $newWidth = $maxWidth;
        $newHeight = $maxHeight;
        $newAspect = $newWidth / $newHeight;

        if ($currentAspect >= $newAspect) {
            $newHeight = $currentHeight / ($currentWidth / $maxWidth);            
        }
        else {
            $newWidth = $currentWidth / ($currentHeight / $maxHeight);
        }

        //prepare the canvas for drawing
        if ($fullCanvas) {
            $newLeft = ($maxWidth - $newWidth) / 2;
            $newTop = ($maxHeight - $newHeight) / 2;
            $targetImage = imagecreatetruecolor($maxWidth, $maxHeight);

            //fill the background color
            if (!empty($backgroundColor)) {
                $fillColor = imagecolorallocate($targetImage, $backgroundColor['red'], $backgroundColor['green'], $backgroundColor['blue']);
                imagefill($targetImage, 0, 0, $fillColor);
            }
            else {
                imagealphablending($targetImage, false);
                imagesavealpha($targetImage, true);
            }
        }
        else {
            $newLeft = 0;
            $newTop = 0;
            $targetImage = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);
        }

        //draw the resize image onto canvas
        imagecopyresampled($targetImage, $sourceImage, $newLeft, $newTop, 0, 0, $newWidth, $newHeight, $currentWidth, $currentHeight);
    }

    /**
     * resize image resolution, maintaining its aspect ratio
     *
     * @param string $sourceFileName
     * @param string $saveFileName
     * @param int $maxHeight
     * @param int $maxWidth
     * @param bool $fullCanvas
     * @param array $backgroundColor
     * @return mixed
     */
    public function resizeImage($sourceFileName, $saveFileName, $maxHeight, $maxWidth, $fullCanvas, $backgroundColor)
    {
        $currentImage = $this->loadImage($sourceFileName);

        if ($currentImage) {
            $newImage = null;

            $this->resizeVirtualImage($currentImage, $newImage, $maxHeight, $maxWidth, $fullCanvas, $backgroundColor);
            
            $this->saveImage($newImage, $saveFileName);

            imagedestroy($currentImage);
            
            imagedestroy($newImage);
        }
    }

    /**
     * synchronize file to server, invoke when create/update model
     * 
     * @param mixed $setting
     * @param array $oldFileList
     * @param array $newFileList
     * @param bool $forceUpload
     * @return mixed
     */
    public function synchronizeServerFile($setting, $oldFileList, $newFileList, $forceUpload) {
        $readerDisk = config('setting.uploader-temp-disk');
        $writerDisk = config('setting.uploader-file-disk');
        $tempPath = config('setting.uploader-temp-path');
        $filePath = config('setting.uploader-file-path');
        $targetTempPath = $setting['temp_path'];
        $targetFilePath = $setting['file_path'];
        $readPath = $this->getUploaderPath($tempPath) . $targetTempPath;
        $savePath = $this->getUploaderPath($filePath) . $targetFilePath;
        $visibility = ($setting['public']) ? 'public' : 'private';
        $readerObject = Storage::disk($readerDisk);
        $writerObject = Storage::disk($writerDisk);
        $files = [];
        
        foreach ($oldFileList as $file) {
            if (!in_array($file, $newFileList)) {
                $fileName =  $this->getFileName($file);

                $readerFile = $this->getUploaderPath($readPath) . $fileName;

                $writerFile = $this->getUploaderPath($savePath) . $fileName;

                if ($readerObject->exists($readerFile)) {
                    $readerObject->delete($readerFile);
                }

                if ($writerObject->exists($writerFile)) {
                    $writerObject->delete($writerFile);
                }

                if (count($setting['resize_image']) > 0) {
                    foreach ($setting['resize_image'] as $resizeImage) {
                        $tempFile = $this->getFileNameWithPrefixSuffix($readerFile, $resizeImage['prefix'], $resizeImage['suffix']);
                        
                        if ($readerObject->exists($tempFile)) {
                            $readerObject->delete($tempFile);
                        }
                        
                        $tempFile = $this->getFileNameWithPrefixSuffix($writerFile, $resizeImage['prefix'], $resizeImage['suffix']);
                        
                        if ($writerObject->exists($tempFile)) {
                            $writerObject->delete($tempFile);
                        }
                    }
                }

                array_push($files, [ 
                    'list_name' => $file, 
                    'file_name' => $fileName,
                    'read_file' => $readerFile,
                    'save_file' => $writerFile,
                    'action' => 'Delete' 
                ]);
            }
        }

        foreach ($newFileList as $file) {
            if ($forceUpload || !in_array($file, $oldFileList)) {
                $fileName =  $this->getFileName($file);

                $readerFile = $this->getUploaderPath($readPath) . $fileName;
                
                $writerFile = $this->getUploaderPath($savePath) . $fileName;

                if ($readerObject->exists($readerFile)) {
                    $fileContent = $readerObject->get($readerFile);
    
                    $writerObject->put($writerFile, $fileContent, $visibility);
    
                    $readerObject->delete($readerFile);
                }
                
                if (count($setting['resize_image']) > 0) {
                    foreach ($setting['resize_image'] as $resizeImage) {
                        $readerFile2 = $this->getFileNameWithPrefixSuffix($readerFile, $resizeImage['prefix'], $resizeImage['suffix']);
                        
                        if ($readerObject->exists($readerFile2)) {
                            $fileContent2 = $readerObject->get($readerFile2);

                            $writerFile2 = $this->getFileNameWithPrefixSuffix($writerFile, $resizeImage['prefix'], $resizeImage['suffix']);
                            
                            $writerObject->put($writerFile2, $fileContent2, $visibility);
                        
                            $readerObject->delete($readerFile2);
                        }
                    }
                }
                
                array_push($files, [ 
                    'list_name' => $file, 
                    'file_name' => $fileName,
                    'read_file' => $readerFile,
                    'save_file' => $writerFile,
                    'action' => 'Add' 
                ]);
            }
        }
        
        return $files;
    }

    /**
     * remove server file, invoke when delete model
     * 
     * @param mixed $setting
     * @param array $fileList
     * @return mixed
     */
    public function deleteServerFile($setting, $fileList) {
        $writerDisk = config('setting.uploader-file-disk');
        $filePath = config('setting.uploader-file-path');
        $targetFilePath = $setting['file_path'];
        $savePath = $this->getUploaderPath($filePath) . $targetFilePath;
        $writerObject = Storage::disk($writerDisk);
        $files = [];
        
        foreach ($fileList as $file) {
            $fileName =  $this->getFileName($file);

            $writerFile = $this->getUploaderPath($savePath) . $fileName;
            
            if ($writerObject->exists($writerFile)) {
                $writerObject->delete($writerFile);
            }

            if (count($setting['resize_image']) > 0) {
                foreach ($setting['resize_image'] as $resizeImage) {
                    $tempFile = $this->getFileNameWithPrefixSuffix($writerFile, $resizeImage['prefix'], $resizeImage['suffix']);
                    
                    if ($writerObject->exists($tempFile)) {
                        $writerObject->delete($tempFile);
                    }
                }
            }

            array_push($files, [ 
                'list_name' => $file, 
                'file_name' => $fileName,
                'save_file' => $writerFile,
                'action' => 'Delete' 
            ]);
        }

        return $files;
    }

    /**
     * generate file hash
     * 
     * @param string $pathType
     * @param string $fileType
     * @param string $fileData
     * @param string $fileTime
     * @param string $secretKey
     * @return string
     */
    public function generateFileHash($pathType, $fileType, $fileData, $fileTime, $secretKey) {
        return hash('sha256', $pathType . '|' . $fileType . '|' . $fileData . '|' . $fileTime . '|' . $secretKey);
    }
}