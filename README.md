# pway

 Simple API for Eway Payment gateway

## Installation

Installation is done using <http://getcomposer.org/> and
<http://packagist.org/>.

## Usage

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
