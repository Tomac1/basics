<?php
namespace Tomac1\Basics;

class Helpers{

	public static function sanitize($request_value){
		if(is_array($request_value)){
			$ret = filter_var_array($request_value);
		}else{
			$ret = filter_var($request_value, FILTER_SANITIZE_STRING); //$_POST['message']
		}
		return $ret;
	}

	public static function end_slash($string){
		if($string){
			if(substr($string,-1,1)!="/") $string .= "/";
		}
		return $string;
	}

	public static function prd($variable){
		echo '<pre>'.print_r($variable,1).'</pre>';
		Die();
	}
}
/*
Use small caps for basic functions
*/
function is_localhost(){
	if($_SERVER["SERVER_ADDR"] == "127.0.0.1" OR $_SERVER["SERVER_ADDR"] == "::1"){
		return(true);
	}else{
		return(false);
	}
}

function get_domain(){
	$domain = "";
	if(is_localhost()){
		return 'localhost';
	}else{
		return parse_url("http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"],PHP_URL_HOST);
	}
}

function execInBackground($cmd) {
    if (substr(php_uname(), 0, 7) == "Windows"){
        pclose(popen("start /B ". $cmd, "r"));
    }
    else {
        exec($cmd . " > /dev/null &");
    }
}

function print2file($string,$phpEol=PHP_EOL){

	$file = '../temp/print2file.txt';
	file_put_contents($file, $string.$phpEol, FILE_APPEND|LOCK_EX);

}

function toarray($par,$val){
	return [$par=>$val];
}

function first_business_day($ym){
	$dow = date("N",strtotime($ym."-01"));
	if($dow==7){
		return $ym."-02";
	}else if($dow==6){
		return $ym."-03";
	}else{
		$m = date("m",strtotime($ym));
		if($m==1){
			if($dow==5){
				return $ym."-04";
			}else{
				return $ym."-02";
			}
		}
		return $ym."-01";
	}
}

function last_business_day($ym){
	$t = date("t",strtotime($ym."-01"));//number of days
	$dow = date("N",strtotime($ym."-".$t));

	if($dow==7){ //sunday
		return $ym."-".($t-2);
	}else if($dow==6){
		return $ym."-".($t-1);
	}else{
		return $ym."-".$t;
	}
}



//Vlastní save_log log funkce, je použita v třídě ExtraException
function save_log($msg,$location=__FILE__,$line=__LINE__,$params=array(),$eceptionTrace=array()){
	GLOBAL $errors;
	$domain = get_domain();
	//předpokládá se volání z public, tzn error log bude v temp/ na stejné úrovni jako public/
	$file = $domain ? "../temp/log-".$domain.".txt" : "../temp/log-undefined.txt";

	$params_msg = "";
	if(!empty($params)){
		foreach($params as $par=>$val){
			$params_msg .= $par." = ".$val."; ";
		}
		$params_msg = "\nParams: ".$params_msg;
	}
	$date = date("d.m.Y h:i:s");
	$save_msg = "<log>".$date."|".$_SERVER["REMOTE_ADDR"]." (".($errors+1).". ".$location.", ".$line."): ".$msg.$params_msg."</log>\n";
	file_put_contents($file, $save_msg, FILE_APPEND|LOCK_EX);

	if(DEBUG===true || ($_SERVER["REMOTE_ADDR"]=="127.0.0.1" || $_SERVER["REMOTE_ADDR"] == "::1")){
		echo '
		<style>
			body{ background-color: #F9D0D0; padding: 20px; font-family: Consolas, Menlo, Monaco, Lucida Console; font-size: 13px; }
			p{ color: black; margin: 3px;}
			p.msg{ color: red; font-weight: bold; }
			p.params{ color: gray;}
			p.exception pre{ color: gray;}
		</style>
		<div>
			<h1>DEBUG localhost error #'.intval($errors).'</h1>
			<p class="msg">#'.intval($errors).') '.htmlspecialchars($msg).'</p>
			<p class="params">'.nl2br(htmlspecialchars($params_msg)).' &nbsp;</p>
			<p>File: '.$location.'</p>
			<p>Line: '.$line.'</p>
			<p>Address: '.$_SERVER["REMOTE_ADDR"].'</p>
			<p>Time: '.$date.'</p>
			<p class="exception"><pre>'.print_r($eceptionTrace,1).'</pre></p>
		</div>';
	}
	$errors++;
}



function iff($iff,$is,$than,$otherwise=''){
	if($iff == $is) return $than;
	else return $otherwise;
}

/*
*	@sanitize
*/


function create_slug($section1,$section2,$section3){
	$slug = '';
	if($section1) $slug = sanitize($section1);
	if($section2) $slug .= '/'.sanitize($section2);
	if($section3) $slug .= '/'.sanitize($section3);
	return $slug;
}

/*
*	replace
*/
function replace($text,$keys_values=array(),$bracket1="{",$bracket2="}"){
	$from = [];
	$to = [];
	if(!empty($keys_values) AND $text){
		foreach($keys_values as $key=>$value){
			if(gettype($value)!="object" && gettype($value)!="array"){
				$from[]	= $bracket1.$key.$bracket2;
				$to[]	= $value;
			}
		}
		return str_replace($from,$to,$text);
	}else{
		return $text;
	}
}

function printer($arr,$echo=0){
	if($echo==2){
		Die('<pre>'.print_r($arr,1).'</pre>');
	}else if($echo==1){
		echo '<pre>'.print_r($arr,1).'</pre>';
	}else{
		return '<pre>'.print_r($arr,1).'</pre>';
	}
}


function btn_link($url,$name,$class="btn-default",$target="_self",$ico=""){
	if($ico) $ico = '<i class="'.$ico.'"></i> ';
	$a = '<a href="'.$url.'" target="'.$target.'" class="btn '.$class.'">'.$ico.$name.'</a>';
	return $a;
}

function hsp($string){
	if($string){
		return htmlspecialchars($string,ENT_QUOTES,'UTF-8');
	}else{
		return '';
	}
}

function redirect($url='',$delay=0){
	if($delay>0){
		header('Refresh: '.intval($delay).'; URL='.$url);
	}else{
		header('Location: '.$url);
	}
	exit;
}

function request_var($prefix1="",$prefix2="",$key){

	$fullkey = $key;
	if($prefix1) $fullkey = $prefix1."_".$key;
	if($prefix1 AND $prefix2) $fullkey = $prefix1."_".$prefix2."_".$key;

	if($_GET[$fullkey]){
		return sanitize($_GET[$fullkey]);

	}else if($prefix1 != "" && $prefix2 != "" && isset($_SESSION[$prefix1][$prefix2][$key])){
		return $_SESSION[$prefix1][$prefix2][$key];

	}else if($prefix1 != "" && $prefix2 == "" && isset($_SESSION[$prefix1][$key])){
		return $_SESSION[$prefix1][$key];

	}else if($prefix1 == "" && $prefix2 == "" && isset($_SESSION[$key])){
		return $_SESSION[$key];

	}else if(isset($_COOKIE[$fullkey])){
		return $_COOKIE[$fullkey];
	}
	return "";
}

function set_var($prefix1="",$prefix2="",$key,$val){
	$D = "/";
	$time_cookie = time()+(3600*24*365);

	if($prefix1 && $prefix2){
		$_SESSION[$prefix1][$prefix2][$key] = $val;
		setcookie($prefix1."_".$prefix2."_".$key,$val,$time_cookie,$D);
	}else if($prefix1 && !$prefix2){
		$_SESSION[$prefix1][$key] = $val;
		setcookie($prefix1."_".$key,$val,$time_cookie,$D);
	}else{
		$_SESSION[$key] = $val;
		setcookie($key,$val,$time_cookie,$D);
	}
}

function unset_var($prefix1="",$prefix2="",$key){
	$D = "/";

	if($prefix1 && $prefix2){
		Unset($_SESSION[$prefix1][$prefix2][$key]);
		setcookie($prefix1."_".$prefix2."_".$key,"",time()-3600,$D);
	}else if($prefix1 && !$prefix2){
		$_SESSION[$prefix1][$key] = $val;
		setcookie($prefix1."_".$key,"",time()-3600,$D);
	}else{
		Unset($_SESSION[$key]);
		setcookie($key,"",time()-3600,$D);
	}
}


function get_first_index($array){
	if(!empty($array)){
		foreach($array as $key => $value){
			return $key; break;
		}
	}
	return "";
}

function get_first_array($arrays){
	if(!empty($arrays)){
		foreach($arrays as $index => $array){
			return $array; break;
		}
	}
	return "";
}
//translation with replacements
function t($key,$text_alt="",$replacements=[]){
	GLOBAL $t, $t_collect;
	$key = strtolower($key);

	if(isset($t[$key])){	$translation = $t[$key];
	}else if($text_alt){	$translation = $text_alt;
	}else{					$translation = $key;
	}

	if(!empty($replacements) && $translation){
		foreach($replacements as $key=>$value){
			$from[]	= "{".$key."}";
			$to[]	= $value;
		}
		$translation = str_replace($from,$to,$translation);
	}
	$t_collect[$key] = $translation; //for debugging
	return $translation;
}

function t_array($array_key,$value_key="",$array_alt=[]){
	GLOBAL $t;
	$array_key = strtolower($array_key);

	$ret = [];
	if(!empty($array_alt)){
		foreach($array_alt as $key=>$defValue){
			if(isset($t[$array_key][$key])){
				$ret[$key]=$t[$array_key][$key];
			}else{
				$ret[$key]=$defValue;
			}
		}
	}else{
		$ret = $t[$array_key];
	}
	if($value_key){
		return $ret[$value_key];
	}else{
		return $ret;
	}
}

//simple and fast trans
function trans($key){
	GLOBAL $t;
	if(isset($t[$key])) return $t[$key];
	else return $key;
}
function concat_url($url,$query_string){
	$ret = $url;
	if(strrpos(" ".$url,'?')){
		$ret .= "&".$query_string;
	}else{
		$ret .= "?".$query_string;
	}
	return $ret;
}

/*
	human readable format for $size = getimagesize(); $type = $size[2];
 *  */
function image_type($type=0){
	$types = [
        0=>'UNKNOWN',
        1=>'GIF',
        2=>'JPEG',
        3=>'PNG',
        4=>'SWF',
        5=>'PSD',
        6=>'BMP',
        7=>'TIFF_II',
        8=>'TIFF_MM',
        9=>'JPC',
        10=>'JP2',
        11=>'JPX',
        12=>'JB2',
        13=>'SWC',
        14=>'IFF',
        15=>'WBMP',
        16=>'XBM',
        17=>'ICO',
        18=>'COUNT'
    ];
	return $types[$type];
}

function week_of_month($date) {
    //Get the first day of the month.
    $firstOfMonth = strtotime(date("Y-m-01", $date));
    //Apply above formula.
    return intval(date("W", $date)) - intval(date("W", $firstOfMonth)) + 1;
}

function selectOptgroup($keys_names,$inputName,$selectedKey){

	$res = '<select class="form-control" name="'.$inputName.'" id="id_'.$inputName.'"'.$css.'>'.PHP_EOL;

	if(!empty($keys_names)){
		foreach($keys_names as $key=>$name){
			$ch="";
			if($selectedKey==$key) $ch = ' selected="selected"';
			if(substr($key, 0, 8)=="optgroup"){
				$res .= '<optgroup label="'.$name.'"></optgroup>'.PHP_EOL;
			}else{
				$res .= '<option value="'.$key.'"'.$ch.'>'.$name.'</option>'.PHP_EOL;
			}
		}
	}
	$res .= '</select>'.PHP_EOL;
	return $res;
}


function select($keys_names,$inputName,$selectedKey,$params=[]){

	if($params["css"]) $css = ' class="'.$params["css"].'"';
	if($params["multirows"]){
		$res = '<select name="'.$inputName.'[]" id="id_'.$inputName.'"'.$css.'>'.PHP_EOL;
	}else{
		$res = '<select name="'.$inputName.'" id="id_'.$inputName.'"'.$css.'>'.PHP_EOL;
	}

	if(!empty($keys_names)){
		foreach($keys_names as $key=>$name){
			$ch="";
			if($selectedKey==$key){
				$ch = ' selected="selected"';
			}

			$res .= '<option value="'.$key.'"'.$ch.'>'.$name.'</option>'.PHP_EOL;
		}
	}
	$res .= '</select>'.PHP_EOL;
	return $res;
}

function select_js($urls_names,$selectedUrl,$params=[]){

	if($params["css"]) $css = ' class="'.$params["css"].'"';
	$res = '<select onchange="document.location.href=this.value" '.$css.'>'.PHP_EOL;

	if(!empty($urls_names)){
		foreach($urls_names as $url=>$name){
			$ch="";
			if($selectedUrl==$url) $ch = ' selected="selected"';
			$res .= '<option value="'.$url.'"'.$ch.'>'.$name.'</option>'.PHP_EOL;
		}
	}
	$res .= '</select>'.PHP_EOL;

	return $res;
}

function generate_code($lenght,$entities=true,$case=''){
	$Chars = "abcdefghijklmnopqrstuvwxyz123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	if($entities == true) $Chars .= "*,-@";

	$lenghtsOfChars = strlen($Chars)-1;
	for($i=0;$i< $lenght;$i++){
		$res .= $Chars[mt_rand(0,$lenghtsOfChars)];
	}
	if($case=='lowercase') $res = strtolower($res);
	else if($case=='uppercase') $res = strtoupper($res);

	return $res;
}

function get_client_ip() {
    $ipaddress = '';

	if(filter_var($_SERVER['HTTP_CLIENT_IP'],FILTER_VALIDATE_IP))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(filter_var($_SERVER['HTTP_X_FORWARDED_FOR'],FILTER_VALIDATE_IP))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(filter_var($_SERVER['HTTP_X_FORWARDED'],FILTER_VALIDATE_IP))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(filter_var($_SERVER['HTTP_FORWARDED_FOR'],FILTER_VALIDATE_IP))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(filter_var($_SERVER['HTTP_FORWARDED'],FILTER_VALIDATE_IP))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(filter_var($_SERVER['REMOTE_ADDR'],FILTER_VALIDATE_IP))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function simple_mail($to,$subject,$text,$from,$fromName,$options = []){
	GLOBAL $simple_mail_error;
	$simple_mail_error = '';

	if($from && $fromName && $to && $text && $subject){

		//options, text, subject
		if(!$options["type"]) $options["type"] = "text/plain";
		if($options["type"]=="text/plain") $text = strip_tags($text);
		$subject = strip_tags($subject);

		// To send HTML mail, the Content-type header must be set
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-type: '.$options["type"].'; charset=utf-8';
		//$headers[] = "Content-Transfer-Encoding: 8bit";

		// Additional headers
		//$headers[] = 'To: '.$to;
		$headers[] = 'From: '.$fromName.' <'.$from.'>';

		if($options["cc"])	$headers[] = 'Cc: '.$options["cc"];
		if($options["bcc"])	$headers[] = 'Bcc: '.$options["bcc"];
		if($options["replyto"]) $headers[] = 'Reply-To: '.$options["replyto"];
		/* delete if new code works
		if(@mail($to,$subject,$text,implode("\n",$headers))){
			$simple_mail_error = "";
			return true;
		}else{
			$e = error_get_last();
			$simple_mail_error = htmlspecialchars_decode($e['message']);
			return false;
		}*/
		if(is_localhost()){
			if(pseudo_mail('SimpleMailLocalhost',$to, $subject, $text, implode("\n", $headers))){
				$simple_mail_error = "";
				return true;
			}else{
				$simple_mail_error = error_get_last()['message'].' ';
				return false;
			}
		}else{
			if(mail($to,$subject,$text,implode("\n", $headers))){
				$simple_mail_error = "";
				return true;
			}else{
				$simple_mail_error = error_get_last()['message'].' ';
				return false;
			}
		}
	}else{
		return false;
	}
}

function pseudo_mail($mailId,$to,$subject,$text,$headers=""){
	$dir = "../temp/logs/".end_slash(DOMAIN);
	if(!is_dir($dir)){ mkdir($dir, 0755); }

	if($mailId) $mailId = "-".$mailId;
	$file = 'debugmail'.$mailId.'.txt';
	$date = date("d.m.Y h:i:s");

	$save_msg  = "Date: ".Date("H:i:s d. m. Y")."\n";
	$save_msg .= "To: $to\n";
	$save_msg .= "Subject: $subject\n\n";
	$save_msg .= "Headers: $headers\n\n";
	$save_msg .= "Text:\n $text\n";

	if(file_put_contents($dir.$file, $save_msg)){
		return true;
	}else{
		return false;
	}
}


function remove_diak($text){
	$from = array("á","ä","č","ď","é","ě","ë","í","ň","ó","ö","ř","š","ť","ú","ů","ü","ý","ž","Á","Ä","Č","Ď","É","Ě","Ë","Í","Ň","Ó","Ö","Ř","Š","Ť","Ú","Ů","Ü","Ý","Ž");
	$to  =  array("a","a","c","d","e","e","e","i","n","o","o","r","s","t","u","u","u","y","z","A","A","C","D","E","E","E","I","N","O","O","R","S","T","U","U","U","Y","Z");

	$text = str_replace($from, $to, $text);
	return $text;
}

function replace_count($from,$to,$string,$start=1){ //from "SELECT * FROM ? WHERE name = ? AND id = ?" will be "SELECT * FROM $1 WHERE name = $2 AND id = $3"
	$count = $start;
	while(1){
		if(!$string = preg_replace("/\?/", preg_quote('$').$count, $string,1)){
			break;
		}
	}
	return $string;
}

function sort_by_parent($Array,$parentId='',$deep=0){
	$retArray = [];

	if(!empty($Array)){
		foreach($Array as $id=>$row){

			//$Array[$id]["parent_id"] = intval($Array[$id]["parent_id"]);
			if($row["parent_id"]==$parentId){
				$retArray[$id] = $row;
				$retArray[$id]["deep"] = $deep;
				if($ret = sort_by_parent($Array,$id,$deep+1)){
					$retArray = $retArray+$ret;
				}
			}
		}
		return $retArray;
	}else{
		return false;
	}
}

function zerofill($num,$lenght=2){
	return str_pad($num,$lenght,"0",STR_PAD_LEFT);
}

function numberColor($number,$append=""){
	if($number<0){
		return '<span class="number_minus">'.$number.$append.'</span>';
	}else if($number>0){
		return '<span class="number_plus">'.$number.$append.'</span>';
	}else{
		return $number.$append;
	}

	return number_format($number, 0);
}

function norm0($number){
	return number_format($number, 0);
}
function norm1($number){
	return number_format($number, 1);
}
function norm2($number){
	return number_format($number, 2);
}

function is_json($string) {
	if(is_array($string) OR is_object($string)){
		return false;
	}else{
		$string = trim($string);
		if($string[0] != '{' && $string[strlen($string) - 1] != '}') {
			return false;
		}else{
			@json_decode($string);
			//echo "; err (".$string."): ".json_last_error();
			return (json_last_error() == JSON_ERROR_NONE);
		}
	}
}

function is_pg_array($string) {
	$string = trim($string);
	if($string[0] != '{' && $string[strlen($string) - 1] != '}') {
		return false;
	}else{
		return true;
	}
}


function glue($oldString='',$addString='',$glue=' '){
	$return = '';
	if($oldString && $addString){
		$return = $oldString.$glue.$addString;
	}else if($oldString && $addString=''){
		$return = $oldString;
	}else if(!$oldString && $addString){
		$return = $addString;
	}
	return $return;
}

function bigintval($value) {
  $value = trim($value);
  if (ctype_digit($value)) {
    return $value;
  }
  $value = preg_replace("/[^0-9](.*)$/", '', $value);
  if (ctype_digit($value)) {
    return $value;
  }
  return 0;
}

function safe_divide($num1,$num2){
	if($num1 && $num2){
		return $num1/$num2;
	}else{
		return 0;
	}
}

function percentage_change($num1,$num2){
	$ratio = safe_divide($num1,$num2);
	if($ratio){
		$percentChange = ($ratio-1) * 100;
	}else if($num1!=0 && $num2==0){
		$percentChange = 100;
	}else if($num1==0 && $num2!=0){
		$percentChange = -100;
	}
	return $percentChange;
}

function plus_minus($num){
	if($num>0){
		return 'number_plus';
	}else if($num<0){
		return 'number_minus';
	}else{
		return 'number_zero';
	}
}

/* javascript minification when generating js settings for smartics or another codes */

function minify_javascript($javascript){
	$blocks = array('for', 'while', 'if', 'else');
	$javascript = preg_replace('/([-\+])\s+\+([^\s;]*)/', '$1 (+$2)', $javascript);
	// remove new line in statements
	$javascript = preg_replace('/\s+\|\|\s+/', ' || ', $javascript);
	$javascript = preg_replace('/\s+\&\&\s+/', ' && ', $javascript);
	$javascript = preg_replace('/\s*([=+-\/\*:?])\s*/', '$1 ', $javascript);
	// handle missing brackets {}
	foreach ($blocks as $block){
	$javascript = preg_replace('/(\s*\b' . $block . '\b[^{\n]*)\n([^{\n]+)\n/i', '$1{$2}', $javascript);
	}
	// handle spaces
	$javascript = preg_replace(array("/\s*\n\s*/", "/\h+/"), array("\n", " "), $javascript); // \h+ horizontal white space
	$javascript = preg_replace(array('/([^a-z0-9\_])\h+/i', '/\h+([^a-z0-9\$\_])/i'), '$1', $javascript);
	$javascript = preg_replace('/\n?([[;{(\.+-\/\*:?&|])\n?/', '$1', $javascript);
	$javascript = preg_replace('/\n?([})\]])/', '$1', $javascript);
	$javascript = str_replace("\nelse", "else", $javascript);
	$javascript = preg_replace("/([^}])\n/", "$1;", $javascript);
	$javascript = preg_replace("/;?\n/", ";", $javascript);
	return $javascript;
}

//get string size in bits
function strbits($string){
    return (strlen($string)*8);
}

//force download. Please remember to
function force_download($content,$filename){

	header('Content-Description: File Transfer');
	header('Content-Type: */*');
	header('Content-Disposition: attachment; filename="'.basename($filename).'"');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . strbits($content));
	echo $content;
	exit;
}

//check external file exist or not /return 200 = ok, > 400 bad
function check_external_file($url){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $retCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $retCode;
}




/* Encrypt and decrypt
 *
 * @author Nazmul Ahsan <n.mukto@gmail.com>
 * @link http://nazmulahsan.me/simple-two-way-function-encrypt-decrypt-string/
 *
 * @param string $string string to be encrypted/decrypted
 * @param string $action what to do with this? e for encrypt, d for decrypt
 */
function my_simple_crypt( $string, $action = 'e' ) {
    // you may change these values to your own
    $secret_key = 'my_simple_secret_key';
    $secret_iv = 'my_simple_secret_iv';

    $output = false;
    $encrypt_method = "AES-256-CBC";
    $key = hash( 'sha256', $secret_key );
    $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

    if( $action == 'e' ) {
        $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
    }
    else if( $action == 'd' ){
        $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
    }

    return $output;
}

/*
$encrypted = my_simple_crypt( 'Hello World!', 'e' );
echo $encrypted.PHP_EOL;
$decrypted = my_simple_crypt( $encrypted, 'd' );
echo $decrypted.PHP_EOL;
*/