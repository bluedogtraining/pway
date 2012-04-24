<?php

namespace Pway;

class Response
{
    const ERROR_CURL = 1000;
    const ERROR_XML  = 2000;

    const STATUS_OKAY    = 0;
    const STATUS_FAILED  = 1;
    const STATUS_UNKNOWN = 2;

    protected $responseData = array();
    protected $error        = null;
    protected $errorMessage = null;

    public function __construct($curl_errno, $response)
    {
        if ($curl_errno == CURLE_OK) {
            $this->parseResponse($response);
        } else {
            $this->error = $curl_errno + self::ERROR_CURL;
            $this->errorMessage = $curl_errno;
        }
    }

    public function __get($var)
    {
        if (isset($this->responseData[$var])) {
            return $this->responseData[$var];
        }
        return null;
    }

    protected function parseResponse($xml)
    {
        $dom = new \DomDocument();
        try {
            // Munge errors into exceptions
            set_error_handler('\Pway\Response::domErrorHandler');
            $dom->loadXml($xml);
            restore_error_handler();
        } catch (\DomException $e) {
            $this->error = self::ERROR_XML;
            $this->errorMessage = $e->getMessage();
        }

        if ($this->error) {
            return false;
        }

        foreach ($dom->firstChild->childNodes as $node) {
            $this->responseData[$node->nodeName] = $node->nodeValue;
        }
    }

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

    public function getStatusMessage()
    {
        if ($this->error) {
            return $this->errorMessage;
        } elseif (isset($this->responseData['ewayTrxnError'])) {
            return $this->responseData['ewayTrxnError'];
        }
    }

    public function domErrorHandler($errno, $errstr, $errfile, $errline)
    {
        if ($errno == E_WARNING && (substr_count($errstr,"loadXML()") > 0)) {
            throw new \DomException($errstr);
        }
    }
}