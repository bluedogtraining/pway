<?php

namespace Pway;

use \Pway\Response;

/**
 * Pway Request class
 * 
 * Allows you to set eway request data and retrieve a response from the
 * Eway payment gateway.
 */
class Request
{
    /**
     * Possible request fields and their maxlength, as per:
     * http://www.eway.com.au/Developer/eway-api/direct-payment-solution.aspx
     *
     * @type array
     */
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

    /**
     * Gateway URL to the Eway API
     *
     * @type string
     */
    protected $gatewayUrl = '';

    /**
     * Request data to sent in the request
     *
     * @type array
     */
    protected $requestData = array();

    /**
     * Creates a new request object
     *
     * @param string $customerId the Eway API customer ID
     * @param string $gateway the Eway gateway URL (can be changed for testing)
     */
    public function __construct($customerId, $gateway = 'https://www.eway.com.au/gateway_cvn/xmlpayment.asp')
    {
        $this->ewayCustomerID = $customerId;
        $this->gatewayUrl     = $gateway;
    }

    /**
     * Sends the request to the Eway servers
     * 
     * @return \Pway\Response
     */
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

    /**
     * Sets a value to be sent in the request.
     *
     * @param string $var variable name
     * @param string $val variable value
     * @throws \Pway\Exception if invalid request field
     */
    public function __set($var, $val)
    {
        if (isset(self::$requestFields[$var])) {
            $this->requestData[$var] = substr($val, 0, self::$requestFields[$var]);
        } else {
            throw new \Pway\Exception("Unrecognised field name {$var}");
        }
    }

    /**
     * Fetches a value to be sent in the request.
     *
     * @param string $var variable name
     * @return string variable value
     * @throws \Pway\Exception if invalid field name
     */
    public function __get($var)
    {
        if (isset(self::$requestFields[$var])) {
            return isset($this->requestData[$var]) ? $this->requestData[$var] : null;
        } else {
            throw new \Pway\Exception("Unrecognised field name {$var}");
        }
    }

    /**
     * Helper method to allow setting a number of variables at once
     *
     * @param array $arr array of fields and values to set
     */
    public function fromArray(array $arr)
    {
        foreach ($arr as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Build the request XML to send to the Eway API
     *
     * @return string request xml
     */
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