# pway

> This library has been deprecated in favour of `bluedogtraining/guzzle-eway`, which leverages the Guzzle HTTP library.


Simple API for Eway Payment Gateway [![Build Status](https://secure.travis-ci.org/bluedogtraining/pway.png?branch=master)](http://travis-ci.org/bluedogtraining/pway)

See <http://www.eway.com.au/Developer/eway-api/> for more detail on the
Eway Payment Gateway.


## Installation

Installation is done using <http://getcomposer.org/> and
<http://packagist.org/>.

## Usage

```php
<?php
$request = new \Pway\Request(87654321);
$request->ewayTotalAmount     = 1000; // Ten dollars
$request->ewayCardHoldersName = 'Test Account';
$request->ewayCardNumber      = '4444333322221111';
$request->ewayCardExpiryMonth = '12';
$request->ewayCardExpiryYear  = '20';
$request->ewayCVN             = '123';

$response = $request->send();

if ($response->isSuccessful()) {
    echo "Thanks for your payment.";
} else {
    echo "Error: ".$response->getStatus();
}
```

## Magic

### Request

`__set()` and `__get` allow you to set request data upon the request
object, as long as the requests match one of the allowed request fields
in the [Eway
documentation](http://www.eway.com.au/Developer/eway-api/direct-payment-solution.aspx).

Example:

    $request->ewayCardHoldersName = 'Foo Bar';

### Response

`__get()` allows you to retreive response data returned from the Eway
API. The fields that you can fetch match the fields returned in the
response XML. If a field is not present, it will simply return `null`.

Example:

    echo $response->ewayTrxnError;

## Status Codes

`getStatus()` will return either:

* a [http://curl.haxx.se/libcurl/c/libcurl-errors.html](cURL error code)
offset by 1000 (ie. `CURLE_UNSUPPORTED_PROTOCOL` would return 1001).
* an XML error (`const ERROR_XML = 2000`)
* the error code returned from the [Eway
  API](http://www.eway.com.au/Developer/payment-code/transaction-results-response-codes.aspx).
* or (`const STATUS_OKAY = 1`)

You can check for a successful response with `isSuccessful()`.
