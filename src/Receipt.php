<?php
namespace Itunes;

class Receipt
{
    public $url = '';

    public function __construct($is_pro = false)
    {
        $this->mode($is_pro);
    }

    public function mode($is_pro=true)
    {
        if ($is_pro)
            $this->url = 'https://buy.itunes.apple.com/verifyReceipt';
        else
            $this->url = 'https://sandbox.itunes.apple.com/verifyReceipt';

        return $this;
    }

    public function verify($receipt, $is_ready = false)
    {
        if (!$is_ready) {
            $receipt = base64_encode($receipt);
        }
        $response = $this->makeRequest(json_encode(['receipt-data' => $receipt]));

        $decoded_response = $this->decodeResponse($response);

        if (!isset($decoded_response->status) || $decoded_response->status != 0) {
            throw new \Exception('Invalid receipt. Status code: ' . (!empty($decoded_response->status) ? $decoded_response->status : 'N/A'));
        }
        if (!is_object($decoded_response)) {
            throw new \Exception('Invalid response data');
        }
        return $decoded_response->receipt;
    }


    private function decodeResponse($response)
    {
        return json_decode($response);
    }

    private function makeRequest($request)
    {
        $ch = curl_init($this->url);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        $errmsg   = curl_error($ch);
        curl_close($ch);
        if ($errno != 0) {
            throw new Exception($errmsg, $errno);
        }
        return $response;
    }
}