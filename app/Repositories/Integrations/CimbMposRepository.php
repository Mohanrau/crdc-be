<?php
namespace App\Repositories\Integrations;

use App\{
    Interfaces\Integrations\CimbMposInterface,
    Models\Payments\MposTransaction
};
use Artisaninweb\SoapWrapper\SoapWrapper;
use Illuminate\Support\Collection;
use Mockery\Exception;
use Carbon\Carbon;

class CimbMposRepository implements CimbMposInterface
{
    protected $soapWrapper;

    /**
     * CimbMposRepository constructor.
     *
     * @param SoapWrapper $soapWrapper
     */
    public function __construct(
        SoapWrapper $soapWrapper
    )
    {
        $this->soapWrapper = $soapWrapper;
    }

    /**
     * Check if the approvalcode, tid and amount is correct and not redeemed yet
     *
     * @param $params
     * @return bool
     */
    public function checkRedeemable($params)
    {
        $result = $this->queryExistence($params);

        //we will do a real time query to see if there is any latest settled transaction
        //TODO :: ALSON will continue after MPOS connection problem has been solve
        /* if(!isset($result->id)){

            $this->mposRetrieverJob();

            //do a requery
            $result = $this->queryExistence($params);
        } */

        return (isset($result->id)) ? true : false;
    }

    /**
     * Redeem MPOS Transaction
     *
     * @param $params
     * @param int $paymentId
     * @return bool
     */
    public function redeem($params, int $paymentId)
    {
        $result = $this->queryExistence($params);

        if(isset($result->id)){

            $result->redeemed = 1;

            $result->payment_id = $paymentId;

            $result->save();

            return true;
        }

        return false;
    }

    /**
     * Retrieve MPOS Transaction
     *
     * @param $params
     * @return mixed
     */
    public function queryExistence($params)
    {
        $required = array('terminal_id', 'approval_code', 'amount');

        if(!collect($params)->has($required)){
            return false;
        }

        return MposTransaction::where(
            [
                ["terminal_id", $params->get('terminal_id')],
                ["approval_code", $params->get('approval_code')],
                ["amount", $params->get('amount')],
                ["redeemed", 0]
            ]
        )->first();
    }

    /**
     * Query for settled payments.
     *
     * @param null $dateFrom
     * @param null $dateTo
     * @return array|bool
     */
    public function queryMpos($dateFrom = null, $dateTo = null)
    {
        /**
         * Quick tuts
         *
         * openssl pkcs12 -in file.p12 -out file.key.pem -nocerts -nodes    -> private key
         * openssl pkcs12 -in file.p12 -out file.crt.pem -clcerts -nokeys   -> client cert
         *
         * apache-root-ca-cert.cer -> cainfo, dont have to be in pem file to work.
         */

        //url and paths for authentication
        $url = env('MPOS_CIMB_URL');
        $caInfoPath = env('MPOS_CIMB_CAINFO_PATH');
        $certPath = env('MPOS_CIMB_CERT_PATH');
        $privateKeyPath = env('MPOS_CIMB_PRIVATE_KEY_PATH');

        //in none is given, the datefrom and date to supposed to be today - 24 hours to now
        $dateFrom = (is_null($dateFrom)) ? Carbon::now()->subHours(24)->format('Y-m-d\TH:i:s.uP') : $dateFrom;
        $dateTo = (is_null($dateTo)) ? Carbon::now()->format('Y-m-d\TH:i:s') : $dateTo;

        $params = array(
            'fromDate'=>$dateFrom,
            'itemPerPage'=>300,
            'pageNumber'=>'1',
            'toDate'=>$dateTo
        );

        // Set post content
        $xml = '<?xml version="1.0" encoding="utf-8"?>
		<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:ser="http://services.webservices.payment.softspace.com" xmlns:xsd="http://schema.webservices.payment.softspace.com/xsd">
			 <soap:Header/>
			 <soap:Body>
					<ser:getSettledTransactions>
						 <!--Optional:-->
						 <ser:request>
								<!--Optional:-->
								<xsd:fromDate>'.$params['fromDate'].'</xsd:fromDate>
								<!--Optional:-->
								<xsd:itemPerPage>'.$params['itemPerPage'].'</xsd:itemPerPage>
								<!--Optional:-->
								<xsd:pageNumber>'.$params['pageNumber'].'</xsd:pageNumber>
								<!--Optional:-->
								<xsd:toDate>'.$params['toDate'].'</xsd:toDate>
						 </ser:request>
					</ser:getSettledTransactions>
			 </soap:Body>
		</soap:Envelope>';


        $http = new \GuzzleHttp\Client;

        $response = $http->post($url, [
            'headers' => [
                'Content-Type' => 'application/soap+xml;charset=UTF-8',
                'SOAPAction' => 'urn:GetSettledTransactionsService'
            ],
            'verify' => $caInfoPath,
            'cert' => $certPath,
            'ssl_key' => $privateKeyPath,
            'body' => $xml
        ]);

        if($response->getStatusCode() != 200){
            return false;
        } else {
            $response = $response->getBody();
            $response = str_replace('ax21:','',$response);
            $element_name = 'settledTransactions';
            $found = preg_match_all('#<'.$element_name.'(?:\s+[^>]+)?>(.*?)'.'</'.$element_name.'>#s',
                $response,
                $matches
            );
            $records = collect();
            if($found != false) {
                foreach($matches[0] as $key=>$value) {
                    $xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?>'.$value, null, LIBXML_NOERROR);
                    $xmlArr = json_decode(json_encode((array)$xml), TRUE); //convert xml to array
                    $records->push([
                        'amount' => $xmlArr['amount'] / 100,
                        'merchant_id' => $xmlArr['MID'],
                        'terminal_id' => $xmlArr['TID'],
                        'approval_code' => $xmlArr['approvalCode'],
                        'params' => json_encode($xmlArr)
                    ]);
                }
                return $records;
            }
            return collect();
        }
    }

    /**
     * MPOS Transaction Retriever
     *
     */
    public function mposRetrieverJob()
    {
        $results = $this->queryMpos(); // this will get the current time(T) - 24 hours to T

        $this->populateToDB($results);
    }

    /**
     * Insert MPOS Transaction into Database
     *
     * @param Collection $results
     */
    public function populateToDB(Collection $results)
    {
        $results->each(function($info){
            $mposTransaction = MposTransaction::firstOrNew(array('approval_code' => $info['approval_code']));
            $mposTransaction->merchant_id = $info['merchant_id'];
            $mposTransaction->terminal_id = $info['terminal_id'];
            $mposTransaction->approval_code = $info['approval_code'];
            $mposTransaction->amount = $info['amount'];
            $mposTransaction->params = $info['params'];
            $mposTransaction->save();
        });
    }
}