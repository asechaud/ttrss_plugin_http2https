<?php

ini_set('display_errors',1);


/**
* 
* // Code from http://php.net/manual/fr/function.base-convert.php //
* 
* 
* Encode in Base32 based on RFC 4648.
* Requires 20% more space than base64  
* Great for case-insensitive filesystems like Windows and URL's  (except for = char which can be excluded using the pad option for urls)
*
* @package default
* @author Bryan Ruiz
**/
class Base32 {

   private static $map = array(
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
        'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
        'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
        'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
        '='  // padding char
    );
    
   private static $flippedMap = array(
        'A'=>'0', 'B'=>'1', 'C'=>'2', 'D'=>'3', 'E'=>'4', 'F'=>'5', 'G'=>'6', 'H'=>'7',
        'I'=>'8', 'J'=>'9', 'K'=>'10', 'L'=>'11', 'M'=>'12', 'N'=>'13', 'O'=>'14', 'P'=>'15',
        'Q'=>'16', 'R'=>'17', 'S'=>'18', 'T'=>'19', 'U'=>'20', 'V'=>'21', 'W'=>'22', 'X'=>'23',
        'Y'=>'24', 'Z'=>'25', '2'=>'26', '3'=>'27', '4'=>'28', '5'=>'29', '6'=>'30', '7'=>'31'
    );
    
    /**
     *    Use padding false when encoding for urls
     *
     * @return base32 encoded string
     * @author Bryan Ruiz
     **/
    public static function encode($input, $padding = true) {
        if(empty($input)) return "";
        $input = str_split($input);
        $binaryString = "";
        for($i = 0; $i < count($input); $i++) {
            $binaryString .= str_pad(base_convert(ord($input[$i]), 10, 2), 8, '0', STR_PAD_LEFT);
        }
        $fiveBitBinaryArray = str_split($binaryString, 5);
        $base32 = "";
        $i=0;
        while($i < count($fiveBitBinaryArray)) {    
            $base32 .= self::$map[base_convert(str_pad($fiveBitBinaryArray[$i], 5,'0'), 2, 10)];
            $i++;
        }
        if($padding && ($x = strlen($binaryString) % 40) != 0) {
            if($x == 8) $base32 .= str_repeat(self::$map[32], 6);
            else if($x == 16) $base32 .= str_repeat(self::$map[32], 4);
            else if($x == 24) $base32 .= str_repeat(self::$map[32], 3);
            else if($x == 32) $base32 .= self::$map[32];
        }
        return $base32;
    }
    
     /**
     *    Use padding false when encoding for urls
     *
     * @return base32 encoded string
     * @author Bryan Ruiz
     **/
    
    public static function decode($input) {
        if(empty($input)) return;
        $paddingCharCount = substr_count($input, self::$map[32]);
        $allowedValues = array(6,4,3,1,0);
        if(!in_array($paddingCharCount, $allowedValues)) return false;
        for($i=0; $i<4; $i++){ 
            if($paddingCharCount == $allowedValues[$i] && 
                substr($input, -($allowedValues[$i])) != str_repeat(self::$map[32], $allowedValues[$i])) return false;
        }
        $input = str_replace('=','', $input);
        $input = str_split($input);
        $binaryString = "";
        for($i=0; $i < count($input); $i = $i+8) {
            $x = "";
            if(!in_array($input[$i], self::$map)) return false;
            for($j=0; $j < 8; $j++) {
                $x .= str_pad(base_convert(@self::$flippedMap[@$input[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            $eightBits = str_split($x, 8);
            for($z = 0; $z < count($eightBits); $z++) {
                $binaryString .= ( ($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48 ) ? $y:"";
            }
        }
        return $binaryString;
    }
}


/**
 * http2https plugin
 * 
 * 	→ Avoid Mixed content when images are http and tt-rss is https
 * 
 * @author Alexandre SÉCHAUD
 **/
class http2https extends Plugin {
	private $host;

	function about() {
		return array(1.2,
			"Avoid « mixed content » when images are loaded over http and tt-rss is loaded over https",
			"asechaud");
	}	
	
	function api_version() {
		return 2;
	}	
	
	function init($host) {
		$this->host = $host;
		$host->add_hook($host::HOOK_SANITIZE, $this);
		$host->add_hook($host::HOOK_FORMAT_ENCLOSURES, $this);
	}
	
	function get_self_url_prefix() { //from functions2.php
		if (strrpos(SELF_URL_PATH, "/") === strlen(SELF_URL_PATH)-1) {
			return substr(SELF_URL_PATH, 0, strlen(SELF_URL_PATH)-1);
		} else {
			return SELF_URL_PATH;
		}
	}


	function hook_sanitize($doc, $site_url, $allowed_elements = null, $disallowed_attributes = null) {
		$xpath = new DOMXPath($doc);
		$entries = $xpath->query("//img");

		foreach ($entries as $entry) {	
			
				$lnk = $entry->getAttribute('src'); //On prend le lien de la balise img
				if ( ! (substr( $lnk, 0, 5 ) === "https") )
					$entry->setAttribute('src', get_self_url_prefix()."/plugins/http2https/https.php?url=".Base32::encode($lnk,false));
			}
			return $doc;
		}
		
		function hook_format_enclosures($rv, $result, $id, $always_display_enclosures, $article_content, $hide_images) {
			$nvo_result = array();
			$retour = array();
			$retour[0] = '';
			foreach ($result as $ligne)
			{
				if ( ! (substr( $ligne['content_url'], 0, 5 ) === "https") && (substr( $ligne['content_type'], 0, 6 ) === "image/") )
					{
						
						$ligne['content_url'] = get_self_url_prefix(). "/plugins/http2https/https.php?url=". Base32::encode($ligne['content_url'],false);
						array_push($nvo_result,$ligne);
					}
			}
			
			
			$retour[1] = $nvo_result;
			return $retour;
			
		}
}



?>
