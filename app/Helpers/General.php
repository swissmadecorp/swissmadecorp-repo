<?php
require_once 'Mobile_Detect.php';

if (! function_exists('Materials')) {
    function Materials() {
        return collect(['Select material for this product','Leather','Platinum','Titanium',
            '18K Yellow Gold','18K Yellow Gold/Stainless Steel','14K Yellow Gold',
            '14K Yellow Gold/Stainless Steel','18K White Gold','14K White Gold',
            '18K White Gold/Stainless Steel','14K White Gold/Stainless Steel',
            '18K Rose Gold','14K Rose Gold',
            '18K Rose Gold/Stainless Steel','14K Rose Gold/Stainless Steel',
            'Silver','Stainless Steel','Rubber','Canvas','Ceramic','Carbon',
            'Platinum/Stainless Steel','Bronze','Alloy','PVD', 'Velcro', 'Fabric',
            'Plastic','Polymer','Zalium', 'Gold Plated','Aluminum','Palladium']);
    }
}

if (! function_exists('BezelMaterials')) {
    function BezelMaterials() {
        return collect(['Select material for this product','Leather','Platinum','Titanium',
            '18K Yellow Gold','18K Yellow Gold/Stainless Steel','14K Yellow Gold',
            '14K Yellow Gold/Stainless Steel','18K White Gold','14K White Gold',
            '18K White Gold/Stainless Steel','14K White Gold/Stainless Steel',
            '18K Rose Gold','14K Rose Gold',
            '18K Rose Gold/Stainless Steel','14K Rose Gold/Stainless Steel',
            'Silver','Stainless Steel','Rubber','Canvas','Ceramic','Carbon',
            'Platinum/Stainless Steel','Bronze','Alloy','PVD', 'Velcro', 'Fabric',
            'Plastic','Polymer','Zalium','Diamonds', 'Gold Plated','Aluminum','Palladium']);
    }
}

if (! function_exists('MetalMaterial')) {
    function MetalMaterial() {
        return collect(['Select material for this product','Platinum', '18K Yellow Gold',
            '18K Yellow Gold/Stainless Steel','14K Yellow Gold',
            '14K Yellow Gold/Stainless Steel','18K White Gold','14K White Gold',
            '18K White Gold/Stainless Steel','14K White Gold/Stainless Steel',
            '18K Rose Gold','14K Rose Gold',
            '18K Rose Gold/Stainless Steel','14K Rose Gold/Stainless Steel',
            'Silver','Stainless Steel','Platinum/Stainless Steel','PVD','Zalium', 'Gold Plated','Palladium']);
    }
}

if (! function_exists('Conditions')) {
    function Conditions() {
        return collect(['Select condition for this product','New','Unworn','Pre-owned','Store Display','New (old stock)','Vintage']);
    }
}

function is_localhost() {
    $whitelist = array( '127.0.0.1', '::1' );
    return in_array( $_SERVER['REMOTE_ADDR'], $whitelist);
}

if (! function_exists('production')) {
    function production() {
        if(!is_localhost())
            return '/public/';
        else return '/';
    }
}

if (! function_exists('allFirstWordsToUpper')) {
    function allFirstWordsToUpper($s) {
        $sp=explode(' ',$s);
        $build='';

        foreach ($sp as $str) {
            $build .= ucfirst(strtolower($str)).' ';
        }

        $build=substr($build,0,strlen($build)-1);
        return $build;
    }
}

if (! function_exists('orderStatus')) {
    function orderStatus() {
        
        return collect(['Unpaid','Paid','Return','Transferred','Canceled','Wire Transfer','Credit Card']);
    }
}

if (! function_exists('DiscountRules')) {
    function DiscountRules() {
        
        return collect(['Fixed amount discount for whole cart',
        'Percent discount for whole cart',
        'Percent of product price discount',
        'Fixed amount discount',
        'Site-wide product percent discount',
        'Category percent discount']);
    }
}

if (! function_exists('isMobile')) {
    function isMobile() {
        $detect = new Mobile_Detect;
        
        if( $detect->isTablet() || $detect->isMobile() ){
            return true;
        }
    }
}

if (! function_exists('Platforms')) {
    function Platforms() {
        return collect(['','eBay', "Amazon","Walmart"]);
    }
}

if (! function_exists('getClientIP')) {
    function getClientIP(){

        // Get real visitor IP behind CloudFlare network
        if (!empty($_SERVER["HTTP_CF_CONNECTING_IP"]) && validateIP($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER["HTTP_CF_CONNECTING_IP"];
        }

        // Get real visitor IP behind NGINX proxy - https://easyengine.io/tutorials/nginx/forwarding-visitors-real-ip/
        if (!empty($_SERVER["HTTP_X_REAL_IP"]) && validateIP($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER["HTTP_X_REAL_IP"];
        }

        // Check for shared Internet/ISP IP
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && validateIP($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        // Check for IP addresses passing through proxies
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

            // Check if multiple IP addresses exist in var
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
                $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($iplist as $ip) {
                    if (validateIP($ip))
                        return $ip;
                }
            }
            else {
                if (validateIP($_SERVER['HTTP_X_FORWARDED_FOR']))
                    return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED']) && validateIP($_SERVER['HTTP_X_FORWARDED']))
            return $_SERVER['HTTP_X_FORWARDED'];

        if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && validateIP($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
            return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];

        if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && validateIP($_SERVER['HTTP_FORWARDED_FOR']))
            return $_SERVER['HTTP_FORWARDED_FOR'];

        if (!empty($_SERVER['HTTP_FORWARDED']) && validateIP($_SERVER['HTTP_FORWARDED']))
            return $_SERVER['HTTP_FORWARDED'];

        // Return unreliable IP address since all else failed
        return $_SERVER['REMOTE_ADDR'];
    }
}

if (! function_exists('validateIP')) {
    /**
     * Ensures an IP address is both a valid IP address and does not fall within
     * a private network range.
     */
    function validateIP($ip) {

        if (strtolower($ip) === 'unknown')
            return false;

        // Generate IPv4 network address
        $ip = ip2long($ip);

        // Do additional filtering on IP
        if(!filter_var($ip, FILTER_VALIDATE_IP))
            return false;

        // If the IP address is set and not equivalent to 255.255.255.255
        if ($ip !== false && $ip !== -1) {

            // Make sure to get unsigned long representation of IP address
            // due to discrepancies between 32 and 64 bit OSes and
            // signed numbers (ints default to signed in PHP)
            $ip = sprintf('%u', $ip);

            // Do private network range checking
            if ($ip >= 0 && $ip <= 50331647)
                return false;
            if ($ip >= 167772160 && $ip <= 184549375)
                return false;
            if ($ip >= 2130706432 && $ip <= 2147483647)
                return false;
            if ($ip >= 2851995648 && $ip <= 2852061183)
                return false;
            if ($ip >= 2886729728 && $ip <= 2887778303)
                return false;
            if ($ip >= 3221225984 && $ip <= 3221226239)
                return false;
            if ($ip >= 3232235520 && $ip <= 3232301055)
                return false;
            if ($ip >= 4294967040)
                return false;
        }
        return true;
    }

}

if (! function_exists('Status')) {
    function Status() {
        return collect(['Available','On Memo', "On Hold","Hidden",'Special','At the Show','3P','Unavailable','Sold','Repair','Return To Vendor','Not At The Show']);
    }
}

if (! function_exists('JewelryType')) {
    function JewelryType() {
        return collect(['Ring'=>'Ring','Bracelet'=>'Bracelet', "Earrings"=>"Earrings","Necklace"=>"Necklace","Pendant"=>"Pendant", "Cufflings" => "Cufflings"]);
    }
}

if (! function_exists('Clasps')) {
    function Clasps() {
        return collect(['Select clasp type','Deployment','Deployment Buckle','Deployment with Safety','Deployment with Push Button','Push Button Hidden','Push Button FoldOver', "Fold Over Push Button",'Strap buckle','Specialty strap buckle','Tang','Box','Double Fold', 'Jewelry Clasp','Fold Clasp','Fold Clasp Hidden','']);
    }
}

if (! function_exists('localize_us_number')) {
    function localize_us_number($phone) {
        $numbers_only = preg_replace("/[^\d]/", "", $phone);
        return preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $numbers_only);
    }
}


if (! function_exists('Chrono24Magin')) {
    function Chrono24Magin($amount) {
        //$margin = 0;

        // if ($amount>0 && $amount<1000)
        //$margin = \App\Models\GlobaPrices::find('Chrono24')->margin;
        $margin = 0.045;
        // elseif ($amount>1000 && $amount<6000) 
        //     $margin = 0.046;
        // elseif ($amount>6000 && $amount < 10000)
        //     $margin = 0.043;
        // elseif ($amount>10000 && $amount < 30000)
        //     $margin = 0.040;
        // elseif ($amount>30000)
        
        return $margin;
    }
}

if (! function_exists('CCMargin')) {
    function CCMargin() {
        return 0.030;
    }
}

if (! function_exists('getCustomColumns')) {
    function getCustomColumns() {
        $custom_columns = array();
        $columns = \Schema::getColumnListing('products');
                
        foreach (array_reverse($columns) as $column) {
            if (substr($column, 0, 2) == 'c_') {
                $custom_columns[] = $column;
            } 
        }

        // dd($custom_columns);
        return $custom_columns;
    }
}

if (! function_exists('Movement')) {
    function Movement() {
        return collect(['Automatic','Manual','Quartz']);
    }
}

if (! function_exists('DialStyle')) {
    function DialStyle() {
        return collect(['','Arabic Numerals','Roman Numerals','Non-Numeric Hour Marks','No Hour Marks','Digital','Analog Digital']);
    }
}

if (! function_exists('Strap')) {
    function Strap() {
        return collect(['Select material for this product','Oyster','Jubilee', 
            'Aviator', 'Engineer', 'President','Leather',
            'Stainless Steel',
            '18K Yellow Gold','18K Yellow Gold/Stainless Steel','14K Yellow Gold',
            '14K Yellow Gold/Stainless Steel','18K White Gold','14K White Gold',
            '18K White Gold/Stainless Steel','14K White Gold/Stainless Steel',
            '18K Rose Gold','14K Rose Gold',
            '18K Rose Gold/Stainless Steel','14K Rose Gold/Stainless Steel',
            'Silver', 'Rubber','Canvas','Ceramic','Kevlar','PearlMaster','PVD','Velcro','Fabric','Plastic','Polymer','Zalium','Titanium','Gold Plated','Satin','Platinum']);
    }
}

if (! function_exists('Gender')) {
    function Gender() {
        return collect(["Men's","Women's",'Unisex']);
    }
}

if (! function_exists('Payments')) {
    function Payments() {
        return collect(['Invoice'=>'Invoice',
                'On Memo'=>'On Memo',
                'On Hold'=>'On Hold',
                'PayPal' => 'PayPal',
                'Canceled'=>'Canceled',
                'Wire Transfer' => 'Wire Transfer', 
                'AMEX' => 'American Express',
                'DISC' => 'Discover',
                'VISA' => 'Visa',
                'MC' => 'Master Card',
                'Repair' => 'Repair'
            ]);
    }
}

if (! function_exists('PaymentsOptions')) {
    function PaymentsOptions() {
        return collect([
            'Due upon receipt'=>'Due upon receipt',
            'Net-10'=>'Net 10','Net-15'=>'Net 15',
            'Net-30'=>'Net 30','Net-60'=>'Net 60',
            'Net-90'=>'Net 90','Net-120'=>'Net 120',
            'None'=>'None','Incomplete'=>'Incomplete',
            'Wire Transfer'=>'Wire Transfer',
            'AMEX' => 'American Express',
            'DISC' => 'Discover',
            'VISA' => 'Visa',
            'MC' => 'Master Card'
        ]);
    }
}

if (! function_exists('addressFromZip')) {
    // Load USPS module to get the city and state based on the zip code
    function addressFromZip($zipcode) {
        $request_doc_template = 
        '<?xml version="1.0"?>
        <CityStateLookupRequest USERID="889SWISS3253">
            <ZipCode ID="0">
                <Zip5>'.$zipcode.'</Zip5>
            </ZipCode>
        </CityStateLookupRequest>';
        
        // prepare xml doc for query string
        $doc_string = preg_replace('/[\t\n]/', '', $request_doc_template);
        $doc_string = urlencode($doc_string);

        $url = "https://production.shippingapis.com/ShippingAPI.dll?API=CityStateLookup&XML=" . $doc_string;
        
        // perform the get
        $response = file_get_contents($url);

        $xml=simplexml_load_string($response) or die("Error: Cannot create object");
        
        //echo "Address1: " . $xml->ZipCode->Zip5 . "<br>";
        //echo "Address2: " . $xml->ZipCode->City . "<br>";
        //echo "State: " . $xml->ZipCode->State . "<br>";
        $countries = new \App\Libs\Countries;
        $country_b = $countries->getStateByCode($xml->ZipCode->State);

        if ($xml->ZipCode->City) {
            return array('city'=>(STRING) ucwords(strtolower($xml->ZipCode->City)),'state'=>$country_b);
        }else{
            return array('city'=>'','state'=>'');
        }

    }
}

if (! function_exists('priceToLetters')) {
    function priceToLetters($price) {
        $combine='';$previousDig='';$usedA=0;$usedB=0;
    
        $format = array('Z','K','I','N','G','R','O','L','E','X');
        $price = number_format($price, 0, '', '');

        for ($i=0; $i < strlen($price);$i++) { // iterate through price one digit at a time
            $dig = $price[$i];
            if (ctype_digit($dig)) {
                if ($dig == $previousDig && $dig != 0) { // if $dig is the same number as the previous number
                    $usedA++; // $usedA is 0 when it first runs
                    for ($j=0; $j < $usedA;$j++) {
                        if (($j % 2)==1)
                            $search = $format[$dig]; // If A was used, use an original digit
                        else $search = 'A'; // A is used for the first identical number
                    }
                } elseif ($dig == $previousDig && $dig == 0) {
                    $usedB++;
                    for ($j=0; $j < $usedB;$j++) {
                        if (($j % 2)==1)
                            $search = 'Z';
                        else $search = 'S';
                    }
                } else {
                    $search = $format[$dig]; // use original digit
                    $usedA=0;$usedB=0;
                }

                $combine .= $search;
                $previousDig = $dig;
            }

        }
        return $combine;
    }
}