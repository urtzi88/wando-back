<?php

namespace App\Http\Controllers;

class Item
{

    function __construct($item)
    {
        $currency = '@currencyId';
        $price = '__value__';

        $this->provider = 'ebay';
        $this->merchant_id = '0001';
        $this->merchant_logo_url = 'http://www.brandemia.org/wp-content/uploads/2012/09/logo_ebay_principal.jpg';
        $this->item_id = $item->itemId[0];
        $this->click_out_link =  $item->viewItemURL[0];
        if (isset($item->galleryURL)) {
          $this->main_photo_url = $item->galleryURL[0];
        } else {
          $this->main_photo_url = "http://pics.ebaystatic.com/aw/pics/express/icons/iconPlaceholder_96x96.gif";
        }
        $this->price = $item->sellingStatus[0]->currentPrice[0]->$price;
        $this->price_currency = $item->sellingStatus[0]->currentPrice[0]->$currency;
        if (isset($item->shippingInfo[0]->shippingServiceCost)) {
            $this->shipping_price = $item->shippingInfo[0]->shippingServiceCost[0]->$price;
        } else {
            $this->shipping_price = null;
        }
        $this->title = $item->title[0];
        $this->valid_until = $this->getPrettyTimeFromEbayTime($item->sellingStatus[0]->timeLeft[0]);
        $this->brand = $item->primaryCategory[0]->categoryName[0];
    }

    protected function getPrettyTimeFromEbayTime($eBayTimeString){
        $matchAry = array();
        $pattern = "#P([0-9]{0,3}D)?T([0-9]?[0-9]H)?([0-9]?[0-9]M)?([0-9]?[0-9]S)#msiU";
        preg_match($pattern, $eBayTimeString, $matchAry);

        $days  = (int) $matchAry[1];
        $hours = (int) $matchAry[2];
        $min   = (int) $matchAry[3];
        $sec   = (int) $matchAry[4];

        $retnStr = '';
        if ($days)  { $retnStr .= "$days day"    . $this->pluralS($days);  }
        if ($hours) { $retnStr .= " $hours hour" . $this->pluralS($hours); }
        if ($min)   { $retnStr .= " $min minute" . $this->pluralS($min);   }
        if ($sec)   { $retnStr .= " $sec second" . $this->pluralS($sec);   }

        return $retnStr;
    }

    protected function pluralS($intIn) {
        if ($intIn > 1) {
            return 's';
        } else {
            return '';
        }
    }

}
