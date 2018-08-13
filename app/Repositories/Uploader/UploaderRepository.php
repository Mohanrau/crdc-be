<?php
namespace App\Repositories\Uploader;

use App\Helpers\Classes\Uploader;
use App\Interfaces\Uploader\UploaderInterface;
use Illuminate\{
    Http\Request,
    Http\File,
    Support\Facades\Storage
};
use Carbon\Carbon;

class UploaderRepository implements UploaderInterface
{
    private $uploaderObj;

    /**
     * UploaderRepository constructor.
     *
     * @param Uploader $uploader
     */
    public function __construct(Uploader $uploader)
    {
        $this->uploaderObj = $uploader;
    }

    /**
     * process upload file
     *
     * @param Request $request
     * @param strinf $fileType
     * @param mixed $setting
     * @return mixed
     */
    public function processUploadFile(Request $request, $fileType, $setting)
    {
        $files = [];
        $workEnable = config('setting.uploader-work-enable');
        $workDisk = config('setting.uploader-work-disk');
        $workRoot = config('setting.uploader-work-root');
        $workPath = config('setting.uploader-work-path');
        $fileDisk = config('setting.uploader-temp-disk');
        $fileRoot = config('setting.uploader-temp-root');
        $filePath = config('setting.uploader-temp-path');
        $fileLink = config('setting.uploader-temp-link');
        $fileSubPath = $setting['temp_path'];
        $visibility = ($setting['public']) ? 'public' : 'private';
        $workFileNameOnly = '';
        $workFileWithRoot = '';
        $workFileWithPath = '';
        $tempFileNameOnly = '';
        $tempFileWithRoot = '';
        $tempFileWithPath = '';

        if (count($setting['water_mark']) <= 0 && count($setting['resize_image']) <= 0) {
            $workEnable = 'N'; //no need to use working folder if no image process task
        }

        $uploadFile = $request->file('file');
        
        if ($workEnable === 'Y') {
            $saveFolder = $this->uploaderObj->getUploaderPath($workPath);
            
            $saveName = str_replace('.' . $uploadFile->extension(), '', $uploadFile->hashName()) . '.' . $uploadFile->getClientOriginalExtension();
            
            $source = Storage::disk($workDisk)->putFileAs($saveFolder, $uploadFile, $saveName, $visibility);
    
            $workFileNameOnly = $this->uploaderObj->getFileName($source);
            
            $workFileWithPath = $source;
            
            $workFileWithRoot = $this->uploaderObj->getUploaderPath($workRoot) . $workFileWithPath;

            if ($setting['rename_file']) {
                $tempFileNameOnly = $workFileNameOnly;
            }
            else {
                $tempFileNameOnly = $_FILES['file']["name"];
            }
            
            $tempFileWithPath = $this->uploaderObj->getUploaderPath($filePath) . $this->uploaderObj->getUploaderPath($fileSubPath) . $tempFileNameOnly;
            
            $tempFileWithRoot = $this->uploaderObj->getUploaderPath($fileRoot) . $tempFileWithPath;
        }
        else {
            $saveFolder = $this->uploaderObj->getUploaderPath($filePath) . $this->uploaderObj->getUploaderFolder($fileSubPath);
        
            $saveName = '';
            
            if ($setting['rename_file']) {
                $saveName = str_replace('.' . $uploadFile->extension(), '', $uploadFile->hashName()) . '.' . $uploadFile->getClientOriginalExtension();
            }
            else {
                $saveName = $_FILES['file']["name"];
            }
            
            $source = Storage::disk($fileDisk)->putFileAs($saveFolder, $uploadFile, $saveName, $visibility);
    
            $tempFileNameOnly = $this->uploaderObj->getFileName($source);

            $tempFileWithPath = $source;
            
            $tempFileWithRoot = $this->uploaderObj->getUploaderPath($fileRoot) . $tempFileWithPath;

            $workFileNameOnly = $tempFileNameOnly;
            
            $workFileWithPath = $tempFileWithPath;
            
            $workFileWithRoot = $tempFileWithRoot;
        }

        array_push($files, [
            'name' => $tempFileNameOnly, 
            'link' => $this->uploaderObj->getFileLink('temp', $fileType, $tempFileNameOnly)
        ]);

        //apply water mark first, so that the watermark persist in all resized images
        if (count($setting['water_mark']) > 0) {
            foreach ($setting['water_mark'] as $key => $watermark) {
                $waterMarkFileName = $watermark['filename'];
                
                $isRemoteFile = false;
                
                if (strtolower(substr($waterMarkFileName, 0, 7)) == "http://" || strtolower(substr($waterMarkFileName, 0, 8)) == "https://") {
                    $sourceUrl = $waterMarkFileName;

                    $waterMarkFileName = $this->uploaderObj->getFileNameWithPrefixSuffix(
                        $workFileWithRoot, 'WM_', '_' . $key);

                    $this->uploaderObj->downloadFile($sourceUrl, $waterMarkFileName);

                    $isRemoteFile = true;
                }

                $this->uploaderObj->applyWaterMark($workFileWithRoot, $waterMarkFileName, 
                    $watermark['position_x'], $watermark['position_y'], 
                    $watermark['margin_x'], $watermark['margin_y'], 
                    $watermark['opacity'], 
                    $watermark['shrink_to_fit'], $watermark['stretch_to_fit']);
                
                if ($isRemoteFile) {
                    unlink($waterMarkFileName);
                }
            }
        }

        //resize images
        if (count($setting['resize_image']) > 0) {
            foreach ($setting['resize_image'] as $resizeImage) {
                $targetFileWithRoot = $this->uploaderObj->getFileNameWithPrefixSuffix(
                    $workFileWithRoot, $resizeImage['prefix'], $resizeImage['suffix']);

                $this->uploaderObj->resizeImage($workFileWithRoot, $targetFileWithRoot, 
                    $resizeImage['max_height'], $resizeImage['max_width'], 
                    $resizeImage['full_canvas'], $resizeImage['background_color']);

                $sizeFileNameOnly = $this->uploaderObj->getFileName($targetFileWithRoot);

                $sizeFileWithRoot = $this->uploaderObj->getUploaderPath($fileRoot) . $this->uploaderObj->getUploaderPath($filePath) . $this->uploaderObj->getUploaderPath($fileSubPath) . $sizeFileNameOnly;

                array_push($files, [
                    'name' => $sizeFileNameOnly, 
                    'link' => $this->uploaderObj->getFileLink('temp', $fileType, $sizeFileNameOnly)
                ]);
            }
        }

        if ($workEnable === 'Y') {
            //move file from work to temp
            $readerObject = Storage::disk($workDisk);

            $writerObject = Storage::disk($fileDisk);
            
            $fileContent = $readerObject->get($workFileWithPath);
            
            $writerObject->put($tempFileWithPath, $fileContent, $visibility);
            
            $readerObject->delete($workFileWithPath);
            
            foreach ($setting['resize_image'] as $resizeImage) {
                $readerFile = $this->uploaderObj->getFileNameWithPrefixSuffix($workFileWithPath, $resizeImage['prefix'], $resizeImage['suffix']);
                
                $writerFile = $this->uploaderObj->getFileNameWithPrefixSuffix($tempFileWithPath, $resizeImage['prefix'], $resizeImage['suffix']);
                
                $fileContent = $readerObject->get($readerFile);
                
                $writerObject->put($writerFile, $fileContent, $visibility);
                
                $readerObject->delete($readerFile);
            }
        }

        return [
            'name' => $tempFileNameOnly,
            'link' => $this->uploaderObj->getFileLink('temp', $fileType, $tempFileNameOnly),
            'files' => $files
        ];
    }

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
    public function downloadPrivateFile(string $pathType, string $fileType, string $fileData, string $fileTime, string $fileHash)
    {
        $setting = $this->uploaderObj->getUploaderSetting(false);

        if (!isset($setting[$fileType])) { 
            abort(400); //Bad request
        } 
        else if ($fileHash != $this->uploaderObj->generateFileHash($pathType, $fileType, $fileData, $fileTime, $setting[$fileType]['secret_key'])) {
            abort(403); //Forbidden
        } 
        else if ($setting[$fileType]['public'] == true) {
            abort(422); //Unprocessable entity
        } 
        else {
            $timeStamp1 = new Carbon($fileTime);

            $timeStamp2 = Carbon::now();

            $validityPeriod = $setting[$fileType]['validity_period'];
            
            if ($validityPeriod <= 0  || $timeStamp2->diffInSeconds($timeStamp1) <= $validityPeriod) {
                if ($pathType == 'file') {
                    $readerObject = Storage::disk(config('setting.uploader-file-disk'));

                    $readerFile = $this->uploaderObj->getUploaderPath(config('setting.uploader-file-path')) . 
                                $this->uploaderObj->getUploaderPath($setting[$fileType]['file_path']) .
                                $this->uploaderObj->getFileName($fileData);
                }
                else {
                    $readerObject = Storage::disk(config('setting.uploader-temp-disk'));

                    $readerFile = $this->uploaderObj->getUploaderPath(config('setting.uploader-temp-path')) . 
                                $this->uploaderObj->getUploaderPath($setting[$fileType]['temp_path']) .
                                $this->uploaderObj->getFileName($fileData);
                }
                
                if ($readerObject->exists($readerFile)) {
                    return $readerObject->download($readerFile);
                }
                else {
                    abort(404); //Not found
                }
            }
            else {
                abort(410); //Gone
            }        
        }
    }
}