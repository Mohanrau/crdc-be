<?php
namespace App\Http\Controllers\V1\Uploader;

use App\{
    Interfaces\Uploader\UploaderInterface,
    Http\Controllers\Controller
};
use Facades\App\Helpers\Classes\Uploader;
use Illuminate\Http\Request;

class UploaderController extends Controller
{
    private $uploaderObj;

    /**
     * UploadController constructor.
     *
     * @param UploaderInterface $uploaderInterface
     */
    public function __construct(UploaderInterface $uploaderInterface)
    {
        $this->middleware('auth')->except(['downloadPrivateFile']);

        $this->uploaderObj = $uploaderInterface;
    }

    /**
     * get client settings for uploader
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getClientSetting(Request $request)
    {
        $setting = Uploader::getUploaderSetting(false);

        $fileType = $request->input('file_type');
        
        request()->validate([
            'file_type' => 'required|string|in:"' . implode('","', array_keys($setting)) . '"'
        ]);

        return response([
            'file_type' => $fileType,
            'client_validate' => isset($setting[$fileType]['client_validate']) ? $setting[$fileType]['client_validate']: '.*'
        ]);
    }

    /**
     * process uploaded file
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function processUploadFile(Request $request)
    {
        $setting = Uploader::getUploaderSetting(true);

        $fileType = $request->input('file_type');
        
        $serverValidate = isset($setting[$fileType]['server_validate']) ? $setting[$fileType]['server_validate']: 'required|file';
        
        request()->validate([
            'file_type' => 'required|string|in:"' . implode('","', array_keys($setting)) . '"',
            'file' => $serverValidate
        ]);
        
        return response($this->uploaderObj->processUploadFile($request, $fileType, $setting[$fileType]));
    }

    /**
     * process uploaded file
     *
     * @param string $pathType
     * @param string $fileType
     * @param string $fileData
     * @param string $fileTime
     * @param string $fileHash
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function downloadPrivateFile(string $pathType, string $fileType, string $fileData, string $fileTime, string $fileHash)
    {
        return $this->uploaderObj->downloadPrivateFile($pathType, $fileType, $fileData, $fileTime, $fileHash);
    }
}
