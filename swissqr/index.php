<?php
# PHP Script for generating Swiss-QR-Code for Bank-Transactions
# by HansPaulHansen

#### ==== USER VARS ==== ####
$QRENCODEBIN = trim(shell_exec('which qrencode'));
$QR_RESOLUTION = 600;
$QR_VERSION = 10;
$QR_SIZE = 19;
$QR_MARGIN = 0;
$QR_ERRCORR = 'M';
$LINEDELIM = '<br />';

#### ==== VARS ==== ####

$DIRPATH = dirname(__FILE__);
		
$QRCONTENT = array(
	'QRType' => 'SPC',
	'Version' => '0200',
	'Coding-Type' => '1',
	'Account' => '',
	'CRAdressTyp' => 'K',
	'CRName' => '',
	'CRAddressline1' => '',
	'CRAddressline2' => '',
	'CRPostalcode' => '',
	'CRCity' => '',
	'CRCountry' => '',
	'UCRAdressTyp' => '',
	'UCRName' => '',
	'UCRAddressline1' => '',
	'UCRAddressline2' => '',
	'UCRPostalcode' => '',
	'UCRCity' => '',
	'UCRCountry' => '',
	'Amount' => '',	
	'Currency' => '',
	'UDAdressTyp' => 'K',
	'UDName' => '',
	'UDAddressline1' => '',
	'UDAddressline2' => '',
	'UDPostalcode' => '',
	'UDCity' => '',
	'UDCountry' => '',
	'Referencetype' => 'NON',
	'Reference' => '',
	'Message' => '',
	'Trailer' => 'EPD',
);

#### ==== FUNCTIONS ==== ####

function validate_str($str) {
    return !preg_match('/[^A-Za-z0-9.,:\'\+\-\/()?!"#%&*;<>÷=@_$£\[\]{}` ´~àáâäçèéêëìíîïñòóôöùúûüýßÀÁÂÄÇÈÉÊËÌÍÎÏÒÓÔÖÙÚÛÜÑ]/', $str);
}

function centertxt($msg, $fontfile, $fontsize, $imgx, $imgy) {
	$centerX = $imgx / 2;
  	$centerY = $imgy / 2;	
  	list($left, $bottom, $right, , , $top) = imageftbbox($fontsize, 0, $fontfile, $msg);
  	$left_offset = ($right - $left) / 2;
  	$top_offset = ($bottom - $top) / 2;
  	$x = $centerX - $left_offset;
	$y = $centerY + $top_offset;
	return array("x" => $x, "y" => $y,"width" => ($right - $left), "height" => ($bottom - $top));
}

function err2img ($imgtxtlines, $imgfontsize, $ystart, $imgx, $imgy) {
	$errimg = imagecreate( $imgx, $imgy );							
	$background  = imagecolorallocate( $errimg, 204, 104, 204 );
	$text_colour = imagecolorallocate( $errimg, 244, 244, 144 );
	$line_colour = imagecolorallocate( $errimg, 128, 255, 0 );
	$fontfile = $DIRPATH."/_font/HankenGrotesk-Black.ttf";
	$textspacing = 5;
	
	$fontsize = 44;
	$msg = "ERROR";	
	$msg_coord = centertxt($msg, $fontfile, $fontsize, $imgx, $imgy);
	imagettftext($errimg, $fontsize, 0, $msg_coord["x"], 160, $text_colour, $fontfile, $msg);
	
	$texty = $ystart;
	
	foreach ($imgtxtlines as $imgtxt) {
		$fontsize = $imgfontsize;
		$msg = $imgtxt;	
		$msg_coord = centertxt($msg, $fontfile, $fontsize, $imgx, $imgy);
		
		imagettftext($errimg, $fontsize, 0, $msg_coord["x"], $texty, $text_colour, $fontfile, $msg);
		$texty = $texty + $msg_coord["height"] + $textspacing;
	}
	
	imagesetthickness ( $errimg, 8 );
	imageline( $errimg, 30, 200, 370, 200, $line_colour );

	header( "Content-type: image/png" );
	imagepng( $errimg );
	imagecolordeallocate( $errimg, $line_colour );
	imagecolordeallocate( $errimg, $text_colour );
	imagecolordeallocate( $errimg, $background );
	imagedestroy( $errimg );
}

function validate_iban($iban) {
	# from https://stackoverflow.com/questions/20983339/validate-iban-php
	
	if(strlen($iban) < 5) return false;
	$iban = strtolower(str_replace(' ','',$iban));
	$Countries = array('al'=>28,'ad'=>24,'at'=>20,'az'=>28,'bh'=>22,'be'=>16,'ba'=>20,'br'=>29,'bg'=>22,'cr'=>21,'hr'=>21,'cy'=>28,'cz'=>24,'dk'=>18,'do'=>28,'ee'=>20,'fo'=>18,'fi'=>18,'fr'=>27,'ge'=>22,'de'=>22,'gi'=>23,'gr'=>27,'gl'=>18,'gt'=>28,'hu'=>28,'is'=>26,'ie'=>22,'il'=>23,'it'=>27,'jo'=>30,'kz'=>20,'kw'=>30,'lv'=>21,'lb'=>28,'li'=>21,'lt'=>20,'lu'=>20,'mk'=>19,'mt'=>31,'mr'=>27,'mu'=>30,'mc'=>27,'md'=>24,'me'=>22,'nl'=>18,'no'=>15,'pk'=>24,'ps'=>29,'pl'=>28,'pt'=>25,'qa'=>29,'ro'=>24,'sm'=>27,'sa'=>24,'rs'=>22,'sk'=>24,'si'=>19,'es'=>24,'se'=>24,'ch'=>21,'tn'=>24,'tr'=>26,'ae'=>23,'gb'=>22,'vg'=>24);
	$Chars = array('a'=>10,'b'=>11,'c'=>12,'d'=>13,'e'=>14,'f'=>15,'g'=>16,'h'=>17,'i'=>18,'j'=>19,'k'=>20,'l'=>21,'m'=>22,'n'=>23,'o'=>24,'p'=>25,'q'=>26,'r'=>27,'s'=>28,'t'=>29,'u'=>30,'v'=>31,'w'=>32,'x'=>33,'y'=>34,'z'=>35);

	if(array_key_exists(substr($iban,0,2), $Countries) && strlen($iban) == $Countries[substr($iban,0,2)]){
				
		$MovedChar = substr($iban, 4).substr($iban,0,4);
		$MovedCharArray = str_split($MovedChar);
		$NewString = "";

		foreach($MovedCharArray AS $key => $value){
			if(!is_numeric($MovedCharArray[$key])){
				if(!isset($Chars[$MovedCharArray[$key]])) return false;
				$MovedCharArray[$key] = $Chars[$MovedCharArray[$key]];
			}
			$NewString .= $MovedCharArray[$key];
		}
		
		if(jei_bcmod($NewString, '97') == 1)
		{
			return true;
		}
	}
	return false;
}

/** 
 * my_bcmod - get modulus (substitute for bcmod) 
 * string my_bcmod ( string left_operand, int modulus ) 
 * left_operand can be really big, but be carefull with modulus :( 
 * by Andrius Baranauskas and Laurynas Butkus :) Vilnius, Lithuania 
 **/ 
function jei_bcmod( $x, $y ) { 
	// how many numbers to take at once? carefull not to exceed (int) 
	$take = 5;	 
	$mod = ''; 

	do 
	{ 
		$a = (int)$mod.substr( $x, 0, $take ); 
		$x = substr( $x, $take ); 
		$mod = $a % $y;	
	} 
	while ( strlen($x) ); 

	return (int)$mod; 
}

function validate_country($country_val) {
	$country_val = strtolower($country_val);
	$countries = array('al'=>28,'ad'=>24,'at'=>20,'az'=>28,'bh'=>22,'be'=>16,'ba'=>20,'br'=>29,'bg'=>22,'cr'=>21,'hr'=>21,'cy'=>28,'cz'=>24,'dk'=>18,'do'=>28,'ee'=>20,'fo'=>18,'fi'=>18,'fr'=>27,'ge'=>22,'de'=>22,'gi'=>23,'gr'=>27,'gl'=>18,'gt'=>28,'hu'=>28,'is'=>26,'ie'=>22,'il'=>23,'it'=>27,'jo'=>30,'kz'=>20,'kw'=>30,'lv'=>21,'lb'=>28,'li'=>21,'lt'=>20,'lu'=>20,'mk'=>19,'mt'=>31,'mr'=>27,'mu'=>30,'mc'=>27,'md'=>24,'me'=>22,'nl'=>18,'no'=>15,'pk'=>24,'ps'=>29,'pl'=>28,'pt'=>25,'qa'=>29,'ro'=>24,'sm'=>27,'sa'=>24,'rs'=>22,'sk'=>24,'si'=>19,'es'=>24,'se'=>24,'ch'=>21,'tn'=>24,'tr'=>26,'ae'=>23,'gb'=>22,'vg'=>24);
	
	if (strlen($country_val) != 2) {
		return false;
	}

	else {
		if (array_key_exists($country_val, $countries)) {
			return true;
		}
		else {
			return false;
		}
	}
}

function validate_curency($currency_val) {

# from https://gist.githubusercontent.com/benedict-w/5644085/raw/bc5536bbe9df4127de3d22a523825a137e87363b/iso_4217_currency_codes.php
	$currency_codes = array(
		'AFA' => array('Afghan Afghani', '971'),
		'AWG' => array('Aruban Florin', '533'),
		'AUD' => array('Australian Dollars', '036'),
		'ARS' => array('Argentine Pes', '032'),
		'AZN' => array('Azerbaijanian Manat', '944'),
		'BSD' => array('Bahamian Dollar', '044'),
		'BDT' => array('Bangladeshi Taka', '050'),
		'BBD' => array('Barbados Dollar', '052'),
		'BYR' => array('Belarussian Rouble', '974'),
		'BOB' => array('Bolivian Boliviano', '068'),
		'BRL' => array('Brazilian Real', '986'),
		'GBP' => array('British Pounds Sterling', '826'),
		'BGN' => array('Bulgarian Lev', '975'),
		'KHR' => array('Cambodia Riel', '116'),
		'CAD' => array('Canadian Dollars', '124'),
		'KYD' => array('Cayman Islands Dollar', '136'),
		'CLP' => array('Chilean Peso', '152'),
		'CNY' => array('Chinese Renminbi Yuan', '156'),
		'COP' => array('Colombian Peso', '170'),
		'CRC' => array('Costa Rican Colon', '188'),
		'HRK' => array('Croatia Kuna', '191'),
		'CPY' => array('Cypriot Pounds', '196'),
		'CZK' => array('Czech Koruna', '203'),
		'DKK' => array('Danish Krone', '208'),
		'DOP' => array('Dominican Republic Peso', '214'),
		'XCD' => array('East Caribbean Dollar', '951'),
		'EGP' => array('Egyptian Pound', '818'),
		'ERN' => array('Eritrean Nakfa', '232'),
		'EEK' => array('Estonia Kroon', '233'),
		'EUR' => array('Euro', '978'),
		'GEL' => array('Georgian Lari', '981'),
		'GHC' => array('Ghana Cedi', '288'),
		'GIP' => array('Gibraltar Pound', '292'),
		'GTQ' => array('Guatemala Quetzal', '320'),
		'HNL' => array('Honduras Lempira', '340'),
		'HKD' => array('Hong Kong Dollars', '344'),
		'HUF' => array('Hungary Forint', '348'),
		'ISK' => array('Icelandic Krona', '352'),
		'INR' => array('Indian Rupee', '356'),
		'IDR' => array('Indonesia Rupiah', '360'),
		'ILS' => array('Israel Shekel', '376'),
		'JMD' => array('Jamaican Dollar', '388'),
		'JPY' => array('Japanese yen', '392'),
		'KZT' => array('Kazakhstan Tenge', '368'),
		'KES' => array('Kenyan Shilling', '404'),
		'KWD' => array('Kuwaiti Dinar', '414'),
		'LVL' => array('Latvia Lat', '428'),
		'LBP' => array('Lebanese Pound', '422'),
		'LTL' => array('Lithuania Litas', '440'),
		'MOP' => array('Macau Pataca', '446'),
		'MKD' => array('Macedonian Denar', '807'),
		'MGA' => array('Malagascy Ariary', '969'),
		'MYR' => array('Malaysian Ringgit', '458'),
		'MTL' => array('Maltese Lira', '470'),
		'BAM' => array('Marka', '977'),
		'MUR' => array('Mauritius Rupee', '480'),
		'MXN' => array('Mexican Pesos', '484'),
		'MZM' => array('Mozambique Metical', '508'),
		'NPR' => array('Nepalese Rupee', '524'),
		'ANG' => array('Netherlands Antilles Guilder', '532'),
		'TWD' => array('New Taiwanese Dollars', '901'),
		'NZD' => array('New Zealand Dollars', '554'),
		'NIO' => array('Nicaragua Cordoba', '558'),
		'NGN' => array('Nigeria Naira', '566'),
		'KPW' => array('North Korean Won', '408'),
		'NOK' => array('Norwegian Krone', '578'),
		'OMR' => array('Omani Riyal', '512'),
		'PKR' => array('Pakistani Rupee', '586'),
		'PYG' => array('Paraguay Guarani', '600'),
		'PEN' => array('Peru New Sol', '604'),
		'PHP' => array('Philippine Pesos', '608'),
		'QAR' => array('Qatari Riyal', '634'),
		'RON' => array('Romanian New Leu', '946'),
		'RUB' => array('Russian Federation Ruble', '643'),
		'SAR' => array('Saudi Riyal', '682'),
		'CSD' => array('Serbian Dinar', '891'),
		'SCR' => array('Seychelles Rupee', '690'),
		'SGD' => array('Singapore Dollars', '702'),
		'SKK' => array('Slovak Koruna', '703'),
		'SIT' => array('Slovenia Tolar', '705'),
		'ZAR' => array('South African Rand', '710'),
		'KRW' => array('South Korean Won', '410'),
		'LKR' => array('Sri Lankan Rupee', '144'),
		'SRD' => array('Surinam Dollar', '968'),
		'SEK' => array('Swedish Krona', '752'),
		'CHF' => array('Swiss Francs', '756'),
		'TZS' => array('Tanzanian Shilling', '834'),
		'THB' => array('Thai Baht', '764'),
		'TTD' => array('Trinidad and Tobago Dollar', '780'),
		'TRY' => array('Turkish New Lira', '949'),
		'AED' => array('UAE Dirham', '784'),
		'USD' => array('US Dollars', '840'),
		'UGX' => array('Ugandian Shilling', '800'),
		'UAH' => array('Ukraine Hryvna', '980'),
		'UYU' => array('Uruguayan Peso', '858'),
		'UZS' => array('Uzbekistani Som', '860'),
		'VEB' => array('Venezuela Bolivar', '862'),
		'VND' => array('Vietnam Dong', '704'),
		'AMK' => array('Zambian Kwacha', '894'),
		'ZWD' => array('Zimbabwe Dollar', '716'),
	);
	if (array_key_exists($currency_val, $currency_codes)) {
		return true;
	}
	else {
		return false;
	}
}


#### ==== VALIDATION ==== ####

if (!file_exists($QRENCODEBIN)) {
	$errmsg[0] = "qrencode nicht unter:";
	$errmsg[1] = '"'.$QRENCODEBIN.'"';
	$errmsg[2] = "gefunden!";
	err2img($errmsg, 22, 260, 400, 400);
	exit;
}


if (empty($_REQUEST['rechnung_id'])) {
	$errmsg[0] = "Rechnungsnummer";
	$errmsg[1] = "nicht angegeben!";
	err2img($errmsg, 22, 260, 400, 400);
	exit;
}
else {
	$ID = trim($_REQUEST["rechnung_id"]);
}

if (empty($_REQUEST['project_name']) && empty($_REQUEST['project_comment'])) {
	$errmsg[0] = "Verwendungszweck";
	$errmsg[1] = "nicht angegeben!";
	$errmsg[2] = "(Projektname oder -beschrieb)";
	err2img($errmsg, 20, 260, 400, 400);
	exit;
}
else {	
	if(empty($_REQUEST['project_comment'])) {
		$project_name_raw = trim($_REQUEST["project_name"]);
		if (!validate_str($project_name_raw)) {
			$errmsg[0] = "Projektname";
			$errmsg[1] = "enthält ungültige Zeichen!";
			err2img($errmsg, 22, 260, 400, 400);
			exit;		
		}
		else {
			$Message = substr($project_name_raw, 0, 140);
		}
	}
	else {
		$project_comment_raw = trim($_REQUEST["project_comment"]);
		if (!validate_str($project_comment_raw)) {
			$errmsg[0] = "Projektbeschrieb";
			$errmsg[1] = "enthält ungültige Zeichen!";
			err2img($errmsg, 22, 260, 400, 400);
			exit;		
		}
		else {
			$Message = substr($project_comment_raw, 0, 140);
		}
	}
	$QRCONTENT["Message"] = $Message;
}

if (empty($_REQUEST['paymentDetails'])) {
	$errmsg[0] = "IBAN nicht angegeben!";
	err2img($errmsg, 22, 260, 400, 400);
	exit;
}
else {
	$Acount_raw = trim($_REQUEST["paymentDetails"]);
	$Acount_raw = str_replace(' ', '', $Acount_raw);
	$Acount_raw = strtolower($Acount_raw);

	if(!(validate_iban($Acount_raw))) {
		$errmsg[0] = "IBAN-Format";
		$errmsg[1] = "nicht korrekt!";
		err2img($errmsg, 22, 260, 400, 400);
		exit;
	}
	else {
		if (!(strpos($Acount_raw, 'ch') === 0) && !(strpos($Acount_raw, 'li') === 0) ) {
			$errmsg[0] = "Keine Schweizer IBAN!";
			err2img($errmsg, 22, 260, 400, 400);
			exit;
		}
		else {
			$Acount_raw = strtoupper($Acount_raw);
			$Account = $Acount_raw;
			$QRCONTENT["Account"] = $Account;
		}
	}
}

if (empty($_REQUEST['company_name'])) {
	$errmsg[0] = "Firmenname (Kreditor)";
	$errmsg[1] = "nicht angegeben!";
	err2img($errmsg, 22, 260, 400, 400);
	exit;
}
else {
	$CRName_raw  = trim($_REQUEST["company_name"]);
	if (strlen($CRName_raw) > 70) {
		$errmsg[0] = "Firmenname (Kreditor)";
		$errmsg[1] = "ist zu lang (max. 70 Z.)!";
		err2img($errmsg, 22, 260, 400, 400);
		exit;		
	}	
	if (!validate_str($CRName_raw)) {
		$errmsg[0] = "Firmenname (Kreditor)";
		$errmsg[1] = "enthält ungültige Zeichen!";
		err2img($errmsg, 22, 260, 400, 400);
		exit;		
	}
	else {
		$CRName = substr($CRName_raw, 0, 70);
		$QRCONTENT["CRName"] = $CRName;
	}
}

if (empty($_REQUEST['company_address'])) {
	$errmsg[0] = "Firmenadresse (Kreditor)";
	$errmsg[1] = "nicht angegeben!";
	err2img($errmsg, 22, 260, 400, 400);
	exit;
}
else {
	$CRAddress_raw = trim($_REQUEST["company_address"]);
	if (!validate_str($CRAddress_raw)) {
		$errmsg[0] = "Firmenadresse (Kreditor)";
		$errmsg[1] = "enthält ungültige Zeichen!";
		err2img($errmsg, 22, 260, 400, 400);
		exit;		
	}
	else {	
		if(!empty($LINEDELIM)) {
			$CRAddressLines = explode($LINEDELIM, $CRAddress_raw);
			$CRAddressLine1 = substr($CRAddressLines[0], 0, 70);
			$CRAddressLine2 = substr($CRAddressLines[1], 0, 70);
			$QRCONTENT["CRAddressline1"] = $CRAddressLine1;
			$QRCONTENT["CRAddressline2"] = $CRAddressLine2;
		}
	}
}

if (empty($_REQUEST['company_country'])) {
	$errmsg[0] = "Firmensitz (Kreditor)";
	$errmsg[1] = "nicht angegeben!";
	err2img($errmsg, 22, 260, 400, 400);
	exit;
}
else {
	$CRCountry_raw  = trim($_REQUEST["company_country"]);
	
	if (validate_country($CRCountry_raw)) {
		$CRCountry = strtoupper($CRCountry_raw);
		$QRCONTENT["CRCountry"] = $CRCountry;
	}
	else {
		$errmsg[0] = "Firmensitz (Kreditor)";
		$errmsg[1] = "kein 2-Charakter Landescode!";
		err2img($errmsg, 20, 260, 400, 400);
	exit;
	}
}

if (empty($_REQUEST['money_total'])) {
	$errmsg[0] = "Rechnungsbetrag";
	$errmsg[1] = "nicht angegeben!";
	err2img($errmsg, 22, 260, 400, 400);
	exit;
}
else {
	$Amount_raw  = trim($_REQUEST["money_total"]);
	$Amount_raw = floatval($Amount_raw);
	if (is_float($Amount_raw )) {
		$Amount_raw = round($Amount_raw,2);
		$Amount = number_format($Amount_raw , 2, '.', '');
		$QRCONTENT["Amount"] = $Amount;
	}
	else {
		$errmsg[0] = "Rechnungsbetrag nicht korrekt!";
		$errmsg[1] = "Keine Fliesskommazahl!";
		err2img($errmsg, 20, 260, 400, 400);	
	}
}

if (empty($_REQUEST['money_currency'])) {
	$errmsg[0] = "Rechnungswährung";
	$errmsg[1] = "nicht angegeben!";
	err2img($errmsg, 22, 260, 400, 400);
	exit;
}
else {
	$Currency_raw = trim($_REQUEST["money_currency"]);
	$Currency_raw = strtoupper($Currency_raw);
	
	if(validate_curency($Currency_raw)) {
		$Currency = $Currency_raw;
		$QRCONTENT["Currency"] = $Currency;
	}
	else {
		$errmsg[0] = "Rechnungswährung";
		$errmsg[1] = "nicht korrekt!";
		$errmsg[2] = "Kein 3-Charakter";
		$errmsg[3] = "Währungscode";
		err2img($errmsg, 20, 260, 400, 400);
	}
}

if (empty($_REQUEST['customer_name'])) {
	$errmsg[0] = "Firmenname (Debitor)";
	$errmsg[1] = "nicht angegeben!";
	err2img($errmsg, 22, 260, 400, 400);
	exit;
}
else {
	$UDName_raw = trim($_REQUEST["customer_name"]);
	if (!validate_str($UDName_raw)) {
		$errmsg[0] = "Firmenname (Debitor)";
		$errmsg[1] = "enthält ungültige Zeichen!";
		err2img($errmsg, 22, 260, 400, 400);
		exit;		
	}
	else {
		$UDName = substr($UDName_raw, 0, 70);
		$QRCONTENT["UDName"] = $UDName;
	}
}

if (empty($_REQUEST['customer_address'])) {
	$errmsg[0] = "Firmenadresse (Debitor)";
	$errmsg[1] = "nicht angegeben!";
	err2img($errmsg, 22, 260, 400, 400);
	exit;
}
else {
	$UDAddress_raw  = trim($_REQUEST["customer_address"]);
	if (!validate_str($UDAddress_raw)) {
		$errmsg[0] = "Firmenadresse (Debitor)";
		$errmsg[1] = "enthält ungültige Zeichen!";
		err2img($errmsg, 22, 260, 400, 400);
		exit;		
	}
	else {	
		if(!empty($LINEDELIM)) {
			$UDAddressLines = explode($LINEDELIM, $UDAddress_raw);
			$UDAddressLine1 = substr($UDAddressLines[0], 0, 70);
			$UDAddressLine2 = substr($UDAddressLines[1], 0, 70);		
			$QRCONTENT["UDAddressline1"] = $UDAddressLine1;
			$QRCONTENT["UDAddressline2"] = $UDAddressLine2;
		}
	}
}

if (empty($_REQUEST['customer_country'])) {
	$errmsg[0] = "Firmensitz (Debitor)";
	$errmsg[1] = "nicht angegeben!";
	err2img($errmsg, 22, 260, 400, 400);
	exit;
}
else {
	$UDCountry_raw  = trim($_REQUEST["customer_country"]);

	if (validate_country($UDCountry_raw)) {
		$UDCountry = strtoupper($UDCountry_raw);
		$QRCONTENT["UDCountry"] = $UDCountry;
	}
	else {
		$errmsg[0] = "Firmensitz (Debitor)";
		$errmsg[1] = "kein 2-Charakter Landescode!";
		err2img($errmsg, 20, 260, 400, 400);
	exit;
	}
}

$qrcontent_str = rtrim(implode("\n", $QRCONTENT));
$qrencode = "'".$QRENCODEBIN."' -d $QR_RESOLUTION -l $QR_ERRCORR -v $QR_VERSION -s $QR_SIZE -m $QR_MARGIN -o - '".$qrcontent_str."'";
$qrcode = shell_exec($qrencode);

#print("<pre>".print_r($QRCONTENT,true)."</pre>");
#exit;

header("Content-Type: image/png");
echo ($qrcode);
?>
