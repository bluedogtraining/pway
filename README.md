# pway

 Simple API for Eway Payment gateway [![Build
Status](https://secure.travis-ci.org/bluedogfrontiers/pway.png)](http://travis-ci.org/bluedogfrontiers/pway)


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
    echo "Error: ".$response->getError();
}
```
