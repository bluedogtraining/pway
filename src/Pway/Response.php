<?php

namespace Pway;

class Response
{
    const EWAY_CURL_ERROR_OFFSET = 1000;
    const EWAY_XML_ERROR_OFFSET = 2000;

    const EWAY_TRANSACTION_OK = 0;
    const EWAY_TRANSACTION_FAILED = 1;
    const EWAY_TRANSACTION_UNKNOWN = 2;

    protected $responseData = array();
    protected $error = null;
    protected $errorMessage = null;

    public function __construct($ch, $response)
    {
        if (curl_errno($ch) == CURLE_OK) {
            $this->parseResponse($response->getBody());
        } else {
            $this->error = curl_errno($ch) + self::EWAY_CURL_ERROR_OFFSET;
        }
    }

    protected function parseResponse($xml)
    {
        $dom = new DomDocument();
        try {
            // Munge errors into exceptions
            set_error_handler('\Pway\Response::domErrorHandler');
            $loaded = $dom->loadXml($xml);
            restore_error_handler();

            if (!$loaded) {
                throw new DomException('Error parsing XML');
            }
        } catch (DomException $e) {
            $loaded = false;
            $this->error = self::EWAY_XML_ERROR_OFFSET;
            $this->errorMessage = $e->getMessage();
        }

        if ($this->error) {
            return false;
        }

        foreach ($dom->childNodes as $node) {
            $this->responseData[$node->nodeName] = $node->nodeValue;
        }
    }

    public function getStatus()
    {
        if ($this->error) {
            return $this->error;
        }

        switch ($this->responseData['ewayTrxnStatus']) {
            case 'True':
                $response = self::EWAY_TRANSACTION_OK;
                break;
            case 'False':
                $response = self::EWAY_TRANSACTION_FAILED;
                break;
            default:
                $response = self::EWAY_TRANSACTION_UNKNOWN;
                break;
        }
        return $response;
    }

    public function getErrorMessage()
    {
        if ($this->error) {
            return $this->errorMessage;
        } else {
            return $this->responseData['ewayTrxnError'];
        }
    }

    protected function domErrorHandler($errno, $errstr, $errfile, $errline)
    {
    } 
}