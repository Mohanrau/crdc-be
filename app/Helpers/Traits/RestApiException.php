<?php
namespace App\Helpers\Traits;

use App\Mail\ExceptionOccurred;
use App\Exceptions\AppException;
use Exception;
use Illuminate\{
    Auth\Access\AuthorizationException,
    Auth\AuthenticationException,
    Database\Eloquent\RelationNotFoundException,
    Http\Exceptions\HttpResponseException,
    Http\Request,
    Database\Eloquent\ModelNotFoundException,
    Support\Facades\Log,
    Support\Facades\Mail,
    Validation\ValidationException
};
use Symfony\Component\{
    Debug\Exception\FatalThrowableError,
    Debug\Exception\FlattenException,
    HttpKernel\Exception\HttpException,
    HttpKernel\Exception\NotFoundHttpException,
    Debug\ExceptionHandler as SymfonyExceptionHandler
};

trait RestApiException
{
    /**
     * Creates a new JSON response based on exception type.
     *
     * @param Request $request
     * @param Exception $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForException(Request $request, \Exception $exception)
    {
        if($exception instanceof AuthorizationException) {
            return response(['error' => ['message' => $exception->getMessage()]]
                , 403);
        }

        elseif ($exception instanceof AuthenticationException) {
            return $this->unauthenticated($request, $exception);
        }

        elseif ($exception instanceof ModelNotFoundException) {
            return $this->modelNotFound();
        }

        elseif ($exception instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($exception, $request);
        }

        elseif ($exception instanceOf RelationNotFoundException or $exception instanceof FatalThrowableError) {
            $this->logErrorMessage($request, $exception);

            return response([
                    'error' => [
                        'message' => $exception->getMessage(),
                        'line' => $exception->getLine(),
                        'file' => $exception->getFile()
                    ]]
                , 422);
        }

        elseif ($exception instanceof HttpResponseException) {
            return $exception->getResponse();
        }

        elseif($exception instanceof NotFoundHttpException) {
            return response(['error' => 'Route not exist in our system']);
        }

        elseif($exception instanceof HttpException) {

            //skip logging the authorization if code 403 ------
            if ($exception->getCode() != 403){
                $this->logErrorMessage($request, $exception);
            }

            return response([
                    'error' => [
                        'message' => $exception->getMessage(),
                        'line' => $exception->getLine(),
                        'file' => $exception->getFile()
                    ]
                ]
                , $exception->getStatusCode());
        }

        elseif ($exception instanceof AppException) {
            if ($exception->logException()) {
                $this->logErrorMessage($request, $exception);
            }
            return errorResponse($exception);
        }

        else {
            $this->logErrorMessage($request, $exception);

            return ($exception->getCode() == '23000') ?
                response(['error' => trans('message.db.duplicate_entry')]) :
                response(['error' => [
                        'message' => $exception->getMessage(),
                        'line' => $exception->getLine(),
                        'file' => $exception->getFile()]
                    ]
                    , 422);
        }
    }

    /**
     * Returns json response for generic bad request.
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function badRequest($message='Bad request', $statusCode=400)
    {
        return $this->jsonResponse(['error' => $message], $statusCode);
    }

    /**
     * Returns json response for Eloquent model not found exception.
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function modelNotFound($message='Record not found', $statusCode=404)
    {
        return $this->jsonResponse(['error' => $message], $statusCode);
    }

    /**
     * Returns json response.
     *
     * @param array|null $payload
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonResponse(array $payload=null, $statusCode=404)
    {
        $payload = $payload ?: [];

        return response()->json($payload, $statusCode);
    }

    /**
     * log the error message to slack
     *
     * @param $request
     * @param $exception
     */
    private function logErrorMessage($request, $exception)
    {
        if (config('logging.slack_notifications_enabled')){
            if ($exception->getMessage() !='' or $exception->getCode() != 403) {
                //get the request object
                if (!file_exists('requests')) {
                    mkdir('requests');
                }

                $fileName  = date('Y-m-d_H_i_s').".txt";

                $flatFile = fopen("requests/".$fileName, "w");

                fwrite($flatFile, json_encode(json_decode($request->getContent())));

                Log::critical(
                    $exception->getMessage(),
                    [
                        'line' => $exception->getLine(),
                        'file' => $exception->getFile(),
                        'url' => $request->path(),
                        'method' => $request->method(),
                        'user_name' => (auth()->check()) ?  auth()->user()->name : 'null',
                        'request' => config('app.url').'/requests/'.$fileName
                    ]
                );

                $this->sendEmail($exception);
            }
        }
    }

    /**
     * Sends an email to the developer about the exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    private function sendEmail(Exception $exception)
    {
        $exceptionCreated = FlattenException::create($exception);

        $handler = new SymfonyExceptionHandler();

        $html = $handler->getHtml($exceptionCreated);

        $email = env('LOG_EMAIL_ADDRESS');

        if($email != '')
        {
            Mail::to($email)->send(new ExceptionOccurred($html));
        }
    }
}