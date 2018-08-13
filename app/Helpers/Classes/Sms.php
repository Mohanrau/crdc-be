<?php
namespace App\Helpers\Classes;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class Sms
{
    private $clientObj;

    /**
     * Sms constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->clientObj = $client;
    }

    /**
     * Send SMS
     *
     * @param string $mobile
     * @param string $message
     * @return array
     */
    public function sendSMS(string $mobile, string $message)
    {
        $errorResponses = config('sms.api_error_response');

        $result = $this->clientObj->get(config('sms.api_url'), [
            'query' => [
                'api_key' => config('sms.api_key'),
                'action' => 'send',
                'to' => $mobile,
                'msg' => $message,
                'sender_id' => 'CLOUDSMS',
                'content_type' => 4,  // 1 = English, 4 = UNICODE (Includes all languages)
                'mode' => 'shortcode'
            ]
        ]);

        $body = trim($result->getBody());

        if( str_contains($body, 'CP') )
        {
            return [
                'response_code' => 0,
                'response_msg' => config('sms.api_success_response')
            ];
        }
        elseif ( array_key_exists($body, $errorResponses) )
        {
            Log::critical('SEND_MSG: ' . $mobile . ' - response_code: ' . $body . ' - response_msg: ' . $errorResponses[$body]);

            return [
                'response_code' => $body,
                'response_msg' => $errorResponses[$body]
            ];
        }
    }
}