<?php

namespace Pway;

use \Pway\Response;

class Request
{
    protected static $requestFields = array(
        'ewayCustomerID'                 => 8,
        'ewayTotalAmount'                => 12,
        'ewayCustomerFirstName'          => 50,
        'ewayCustomerLastName'           => 50,
        'ewayCustomerEmail'              => 50,
        'ewayCustomerAddress'            => 255,
        'ewayCustomerPostcode'           => 6,
        'ewayCustomerInvoiceDescription' => 255,
        'ewayCustomerInvoiceRef'         => 50,
        'ewayCardHoldersName'            => 50,
        'ewayCardNumber'                 => 20,
        'ewayCardExpiryMonth'            => 2,
        'ewayCardExpiryYear'             => 2,
        'ewayTrxnNumber'                 => 16,
        'ewayOption1'                    => 255,
        'ewayOption2'                    => 255,
        'ewayOption3'                    => 255,
        'ewayCVN'                        => 4,
    );

    protected $gatewayUrl = '';
    protected $requestData = array();

    public function __construct($customerId, $gateway = 'https://www.eway.com.au/gateway_cvn/xmlpayment.asp')
    {
        $this->ewayCustomerID = $customerId;
        $this->gatewayUrl     = $gateway;
    }

    public function send()
    {
        $xml = $this->getRequestXml();
        $ch = curl_init($this->gatewayUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 240);

        $response = curl_exec($ch);
        return new Response(curl_errno($ch), $response);
    }

    public function __set($var, $val)
    {
        if (isset(self::$requestFields[$var])) {
            $this->requestData[$var] = substr($val, 0, self::$requestFields[$var]);
        } else {
            throw new \Pway\Exception("Unrecognised field name {$var}");
        }
    }

    public function __get($var)
    {
        if (isset(self::$requestFields[$var])) {
            return isset($this->requestData[$var]) ? $this->requestData[$var] : null;
        } else {
            throw new \Pway\Exception("Unrecognised field name {$var}");
        }
    }

    public function fromArray(array $arr)
    {
        foreach ($arr as $key => $value) {
            $this->$key = $value;
        }
    }

    protected function getRequestXml()
    {
        $xml = new \DomDocument();
        $ewayGateway = $xml->createElement('ewaygateway');
        foreach (self::$requestFields as $field => $length) {
            $value = isset($this->requestData[$field]) ? $this->requestData[$field] : null;
            $ewayGateway->appendChild(
                $xml->createElement($field, $value)
            );

        }
        $xml->appendChild($ewayGateway);
        return $xml->saveXml();
    }
}