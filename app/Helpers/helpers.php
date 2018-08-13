<?php
if (! function_exists('errorResponse')) {
    /**
     * Returns an error response
     *
     * The content can be an array that has ['error' => '', 'code' => '', 'type' => ''] or string which will return as
     * [ 'error' => {string} ] or it can handle an Exception
     *
     * @param \Exception|string|array $content
     * @param int $status
     * @param array $headers
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    function errorResponse($content = '', $status = 422, array $headers = []) {
        if ($content instanceof \Exception) {
            try {
                $type = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', (new \ReflectionClass($content))->getShortName()));
            } catch (\ReflectionException $exception) {
                $type = '';
            }
            $response = [
                'error' => [
                    'message' => trans($content->getMessage()),
                    'code' => $content->getCode(),
                    'type' => $type
                ]
            ];
        } else if (is_array($content)) {
            $response = $content;
        } else {
            $response = [
                'error' => $content
            ];
        }

        return response($response, $status, $headers);
    }
}
?>