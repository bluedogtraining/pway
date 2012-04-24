<?php

namespace Pway;

use \Pway\Response;

class Request
{
    protected static $gatewayUrl = '';
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
    protected $requestData = array();

    public function __construct($customerId)
    {
    }

    public function send()
    {
        $xml = $this->getRequestXml();
        $ch = curl_init($this->gatewayUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 240);

        $response = curl_exec($ch);
        return new Response($ch, $response);
    }

    public function __set($var, $val)
    {
        if (isset($this->requestFields[$var])) {
            $this->requestData[$var] = substr($val, 0, $this->requestFields[$var]);
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
        $xml = new DomDocument();
        foreach ($this->requestFields as $field) {
            $xml->appendChild(
                $xml->createElement($field, $this->requestData[$field])
            );
        }
        return $xml->saveXml();
    }
}