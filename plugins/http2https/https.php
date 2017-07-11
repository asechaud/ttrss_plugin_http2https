<?php

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




/*
 * 
 * Prend le fichier en http et le renvoie en https
 * GET -> url --> base32 de l'URL
 * 
 */




if (!isset($_GET['url']))
	die ("No URL");
	
$url = $_GET['url'];

$url = Base32::decode($url);
$url = str_replace("\0","",$url);


$curl = curl_init($url);

curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

curl_setopt($curl,CURLOPT_HEADER,true);
curl_setopt($curl,CURLOPT_NOBODY,true);
$entete_txt = curl_exec($curl);

$entetes = explode("\r\n",$entete_txt);
foreach ($entetes as $entete)
	header ($entete);

curl_setopt($curl,CURLOPT_HEADER,false);
curl_setopt($curl,CURLOPT_NOBODY,false);
$body = curl_exec($curl);

curl_close($curl);
echo ($body);
?>