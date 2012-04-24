<?php

namespace Pway\Test;

use Pway\Request;
use Pway\Response;

require_once __DIR__.'/../../../src/Pway/Request.php';
require_once __DIR__.'/../../../src/Pway/Response.php';
require_once __DIR__.'/../../../src/Pway/Exception.php';

class RequestResponseTest extends \PHPUnit_Framework_Testcase
{
    public function testConstruct()
    {
        $r = new Request(1);
        $this->assertEquals(1, $r->ewayCustomerID);
    }

    public function testSetGet()
    {
        $r = new Request(1);
        $r->ewayCustomerAddress = "foo";
        $this->assertEquals("foo", $r->ewayCustomerAddress);
    }

    /**
     * @expectedException \Pway\Exception
     */
    public function testSetException()
    {
        $r = new Request(1);
        $r->foo = "bar";
    }

    /**
     * @expectedException \Pway\Exception
     */
    public function testGetException()
    {
        $r = new Request(1);
        $r->foo;
    }

    public function testFromArray()
    {
        $r = new Request(1);
        $r->fromArray(array(
            'ewayCustomerFirstName' => 'foo',
            'ewayCustomerLastName'  => 'bar',
        ));
        $this->assertEquals('foo', $r->ewayCustomerFirstName);
        $this->assertEquals('bar', $r->ewayCustomerLastName);
    }

    public function testResponseType()
    {
        $r = $this->getResponse(1000);
        $this->assertInstanceOf('\Pway\Response', $r);
    }

    public function testGetStatus()
    {
        $r = $this->getResponse(1000);
        $this->assertEquals(Response::STATUS_OKAY, $r->getStatus());
    }

    public function testGetStatusFail()
    {
        $r = $this->getResponse(1006);
        $this->assertEquals(Response::STATUS_FAILED, $r->getStatus());
    }

    public function testGetMessage()
    {
        $r = $this->getResponse(1006);
        $this->assertEquals('06,Error(Test CVN Gateway)', $r->getStatusMessage());
    }

    public function testResponseGet()
    {
        $r = $this->getResponse(1000);
        $this->assertEquals('True', $r->ewayTrxnStatus);
        $this->assertNull($r->foo);
    }

    public function testResponseBadCurlStatus()
    {
        $r = new Response(10, '');
        $this->assertEquals(1010, $r->getStatus());
        $this->assertEquals(10, $r->getStatusMessage());
    }

    public function testResponseBadXml()
    {
        $r = new Response(0, 'foo');
        $this->assertEquals(2000, $r->getStatus());
    }

    public function testResponseBadStatus()
    {
        $r = new Response(0, '<ewayResponse><ewayTrxnStatus>Unknown</ewayTrxnStatus><ewayTrxnNumber>10461</ewayTrxnNumber><ewayTrxnReference/><ewayTrxnOption1/><ewayTrxnOption2/><ewayTrxnOption3/><ewayAuthCode>123456</ewayAuthCode><ewayReturnAmount>1000</ewayReturnAmount><ewayTrxnError>00,Transaction Approved(Test CVN Gateway)</ewayTrxnError></ewayResponse>');
        $this->assertEquals(Response::STATUS_UNKNOWN, $r->getStatus());
    }

    private function getResponse($amount)
    {
        $r = new Request(87654321, 'https://www.eway.com.au/gateway_cvn/xmltest/testpage.asp');
        $r->ewayTotalAmount     = $amount;
        $r->ewayCardHoldersName = 'Test Account';
        $r->ewayCardNumber      = '4444333322221111';
        $r->ewayCardExpiryMonth = '12';
        $r->ewayCardExpiryYear  = '20';
        $r->ewayCVN             = '123';
        return $r->send();
    }
}