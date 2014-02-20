<?php

namespace Pway;

/**
 * A response object returned from the Eway API
 */
class Response
{
    /** @type int */
    const ERROR_CURL = 1000;
    /** @type int */
    const ERROR_XML  = 2000;

    /** @type int */
    const STATUS_OKAY    = 0;
    /** @type int */
    const STATUS_FAILED  = 1;
    /** @type int */
    const STATUS_UNKNOWN = 2;

    /** @type array response data returned from the API */
    protected $responseData = array();
    /** @type int error code in case of non-API (cURL, XML) error */
    protected $error        = null;
    /** @type string error message in case of non-API (cURL, XML) error */
    protected $errorMessage = null;

    /**
     * Create a new response object from the API
     *
     * @param int cURL error number (can be 0 - OKAY)
     * @param string $response response XML
     */
    public function __construct($curl_errno, $response)
    {
        if ($curl_errno == CURLE_OK) {
            $this->parseResponse($response);
        } else {
            $this->error = $curl_errno + self::ERROR_CURL;
            $this->errorMessage = $curl_errno;
        }
    }

    /**
     * Fetch a field from the response data
     *
     * @param string $var variable name
     * @return string response data value
     */
    public function __get($var)
    {
        if (isset($this->responseData[$var])) {
            return $this->responseData[$var];
        }
        return null;
    }

    /**
     * Parse the response XML into an associative array of values.
     *
     * @param string response XML
     */
    protected function parseResponse($xml)
    {
        $dom = new \DomDocument();

        $previous = libxml_use_internal_errors(true);
        $dom->loadXml($xml);

        if (count(libxml_get_errors())) {
            libxml_use_internal_errors($previous);
            libxml_clear_errors();

            $this->error = self::ERROR_XML;
            $this->errorMessage = 'libxml errors: '.json_encode(libxml_get_errors());
            return false;
        }
        libxml_use_internal_errors($previous);

        foreach ($dom->firstChild->childNodes as $node) {
            $this->responseData[$node->nodeName] = $node->nodeValue;
        }
    }

    /**
     * Get the status of the response
     *
     * @return int can either be:
     *   const STATUS_OKAY
     *   const STATUS_UNKNOWN
     *   or one of the eway response codes (http://www.eway.com.au/Developer/payment-code/transaction-results-response-codes.aspx)
     */
    public function getStatus()
    {
        if ($this->error) {
            return $this->error;
        }

        $response = self::STATUS_UNKNOWN;
        if (isset($this->responseData['ewayTrxnStatus'])) {
            switch ($this->responseData['ewayTrxnStatus']) {
                case 'True':
                    $response = self::STATUS_OKAY;
                    break;
                case 'False':
                    $response = self::STATUS_FAILED;
                    break;
            }
        }
        return $response;
    }

    /**
     * Determines if the response was successful.
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return $this->getStatus() == self::STATUS_OKAY;
    }

    /** Get the status message of the response
     *
     * @return string
     */
    public function getStatusMessage()
    {
        if ($this->error) {
            return $this->errorMessage;
        } elseif (isset($this->responseData['ewayTrxnError'])) {
            return $this->responseData['ewayTrxnError'];
        }
    }
}