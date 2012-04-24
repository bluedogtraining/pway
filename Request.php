<?php

class Eway
{
    protected static $gatewayUrl = '';
    protected static $requestFields = array(
        'ewayCustomerID',
        'ewayTotalAmount',
        'ewayCustomerFirstName',
        'ewayCustomerLastName',
        'ewayCustomerEmail',
        'ewayCustomerAddress',
        'ewayCustomerPostcode',
        'ewayCustomerInvoiceDescription',
        'ewayCustomerInvoiceRef',
        'ewayCardHoldersName',
        'ewayCardNumber',
        'ewayCardExpiryMonth',
        'ewayCardExpiryYear',
        'ewayTrxnNumber',
        'ewayOption1',
        'ewayOption2',
        'ewayOption3',
        'ewayCVN',
    );
    protected $requestData = array();

    public function __construct($customerId)
    {

    }

    public function send()
    {
        $xml = $this->getRequestXml();
        $config = array(
            'adapter' => 'Zend_Http_Client_Adapter_Curl',
            'timeout' => 240,
        );
        $client = new Zend_Http_Client(self::$gatewayUrl, $config);
        $response = $client->setRawData($xml, 'text/xml')->request(Zend_Http_Client::POST);
        return new Response($response);
    }

    public function __set($var, $val)
    {
        if (in_array($var, $this->requestFields)) {
            $this->requestData[$var] = $val;
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
    }
}