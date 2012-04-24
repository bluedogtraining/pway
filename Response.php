<?php

class Response
{
    const EWAY_CURL_ERROR_OFFSET = 1000;
    const EWAY_XML_ERROR_OFFSET = 2000;

    const EWAY_TRANSACTION_OK = 0;
    const EWAY_TRANSACTION_FAILED = 1;
    const EWAY_TRANSACTION_UNKNOWN = 2;

    protected $responseData = array();
    protected $response;

    public function __construct(Zend_Http_Response $response = null)
    {
        if ($response->isSuccessful()) {
            $this->parseResponse($response->getBody());
        }
    }

    protected function parseResponse($xml)
    {
        $dom = new DomDocument($xml);
        foreach ($dom->childNodes as $node) {
            $this->responseData[$node->nodeName] = $node->nodeValue;
        }
    }

    protected function isSuccessful()
    {
        $response = self::EWAY_TRANSACTION_UNKNOWN;
        if (!empty($this->responseData)) {
            $response = null;
            switch ($this->responseData['ewayTrxnStatus']) {
                case 'True':
                    $response = self::EWAY_TRANSACTION_OK;
                    break;
                case 'False':
                    $response = self::EWAY_TRANSACTION_FAILED;
                    break;
            }
        }
        return $response;
    }
}