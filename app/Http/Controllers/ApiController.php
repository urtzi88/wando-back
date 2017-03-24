<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Item;

class ApiController extends Controller
{
    public function search()
    {
        $endpoint = 'http://svcs.sandbox.ebay.com/services/search/FindingService/v1';
        $xmlrequest = $this->createXMLRequest($_REQUEST);
        $response = json_decode($this->constructPostCallAndGetResponse($endpoint, $xmlrequest));
        $items = array();
        foreach ($response->findItemsByKeywordsResponse[0]->searchResult[0]->item as $item) {
          $items[] = new Item($item);
        }
        return response()->json($items);
    }

    protected function createXMLRequest($params, $page = 1)
    {
        $xmlrequest  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $xmlrequest .= "<findItemsByKeywordsRequest xmlns=\"http://www.ebay.com/marketplace/search/v1/services\">\n";
        $xmlrequest .= "<keywords>";
        $xmlrequest .= $params['keywords'];
        $xmlrequest .= "</keywords>\n";
        if(!empty($params['sorting'])) {
            $xmlrequest .= "<sortOrder>";
            if($params['sorting'] == 'by_price_asc') {
                $xmlrequest .= 'PricePlusShippingHighest';
            } else {
                $xmlrequest .= 'BestMatch';
            }
            $xmlrequest .= "</sortOrder>\n";
        }
        if(!empty($params['price_min'])) {
            $xmlrequest .= "<itemFilter><name>MinPrice</name><value>" . $params['price_min'] . "</value></itemFilter>\n";
        }
        if(!empty($params['price_max'])) {
            $xmlrequest .= "<itemFilter><name>MaxPrice</name><value>" . $params['price_max'] . "</value></itemFilter>\n";
        }
        $xmlrequest .= "<paginationInput>\n  <entriesPerPage>100</entriesPerPage>\n  <pageNumber>" . $page . "</pageNumber>\n</paginationInput>\n";
        $xmlrequest .= "</findItemsByKeywordsRequest>";

        return $xmlrequest;
    }

    protected function constructPostCallAndGetResponse($endpoint, $xmlrequest)
    {
        // Set up the HTTP headers
        $headers = array(
          'X-EBAY-SOA-OPERATION-NAME: findItemsByKeywords',
          'X-EBAY-SOA-SERVICE-VERSION: 1.3.0',
          'X-EBAY-SOA-REQUEST-DATA-FORMAT: XML',
          'X-EBAY-SOA-GLOBAL-ID: EBAY-US',
          'X-EBAY-SOA-SECURITY-APPNAME: WandoInt-217b-42d8-a699-e79808dd505e',
          'Content-Type: text/xml;charset=utf-8',
          'X-EBAY-SOA-RESPONSE-DATA-FORMAT: JSON',
        );

        $session  = curl_init($endpoint);                       // create a curl session
        curl_setopt($session, CURLOPT_POST, true);              // POST request type
        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);    // set headers using $headers array
        curl_setopt($session, CURLOPT_POSTFIELDS, $xmlrequest); // set the body of the POST
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);    // return values as a string, not to std out

        $responsexml = curl_exec($session);                     // send the request
        curl_close($session);                                   // close the session
        return $responsexml;                                    // returns a string
    }
}
