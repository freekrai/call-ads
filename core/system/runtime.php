<?php
$_GET['route'] = isset($_GET['route']) ? '/'.$_GET['route'] : '/';
define ('DOCUMENT_ROOT', realpath(dirname(__FILE__)));
define("MAX_LENGTH", 6);

define('DEBUG',false);
#define('DEBUG',true);
if (DEBUG) {
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 1);
} else {
	error_reporting(0);
	@ini_set('display_errors', 0);
}

date_default_timezone_set('America/Los_Angeles');

//	grab all files in the core/app folder and include them..
$load_list = glob('core/app/*.php');
foreach($load_list as $ll) {
	include_once $ll;
}

//	Utility Functions	--------------------------------------------------------------------------------------------

function split_name( $name ){
	list($fname, $lname) = split(' ', $name,2);
	return array($fname,$lname);
}

/*
 * Used mostly for views, let's us grab user information for displaying on dashboard
 */
function get_user( $user_id ){
	return Model::factory('User')->where_equal('id', $user_id)->find_one();
}

/*
 *	shortcut function to grab the site.url variable we've stored in our config.ini file
 */
function site_url(){
	return config( 'site.url' );
}
/*
 *	return a value that matches the key we pass
 */
function config($key){
	$app = \Jolt\Jolt::getInstance();
	return $app->option($key);
}

function get_numbers(){
	$numbers = array();
	$users  = Model::factory('User')->find_many();
	foreach( $users as $user ){
		$numbers[] = $user->phone;
	}
	return $numbers;
}
/*
	Takes a client name and returns a twilio capability token for use in Twilio Client.
*/
function build_twilio_token($name){
	$capability = new Services_Twilio_Capability(
		config('twilio.accountsid'), 
		config('twilio.authtoken')
	);
	$capability->allowClientOutgoing( config('twilio.appid') );
	$capability->allowClientIncoming( $name );

	$token = $capability->generateToken();
	return $token;	
}

function be_nice($status, $sep = '-') {
	return ucwords(str_replace($sep, ' ', $status));
}
function who_called($number) {
	if (preg_match('|^client:|', $number) ){
		$number = str_replace('client:','',$number);
		$ret = $number.' (client)';
	}else{
		$ret = format_phone($number);
	}
	return $ret;
}
function nice_date($date){
	$timestamp = strtotime($date);
	return date('M j, Y', $timestamp).'<br />'.date('H:i:s T', $timestamp );
}

/*
 *	format telephone number for display
 */
function format_telephone($phone = '', $convert = true, $trim = true){
	if ( empty($phone) ) {
		return false;
	}
	$phone = preg_replace("/[^0-9A-Za-z]/", "", $phone);
	$OriginalPhone = $phone;
	if ( $trim == true && (strlen($phone) > 11) ) {
		$phone = substr($phone, 0, 11);
	}

	if ( $convert == true && !is_numeric($phone) ) {
		$replace = array('2'=>array('a','b','c'),
			'3'=>array('d','e','f'),
			'4'=>array('g','h','i'),
			'5'=>array('j','k','l'),
			'6'=>array('m','n','o'),
			'7'=>array('p','q','r','s'),
			'8'=>array('t','u','v'),
			'9'=>array('w','x','y','z'));
		foreach($replace as $digit=>$letters) {
			$phone = str_ireplace($letters, $digit, $phone);
		}
	}
	
	$length = strlen($phone);
	switch ($length) {
		case 7:
			// Format: xxx-xxxx
			return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1-$2", $phone);
		case 10:
			// Format: (xxx) xxx-xxxx
			return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "($1) $2-$3", $phone);
		case 11:
			// Format: x(xxx) xxx-xxxx
			return preg_replace("/([0-9a-zA-Z]{1})([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1 ($2) $3-$4", $phone);
		default:
			// Return original phone if not 7, 10 or 11 digits long
			return $OriginalPhone;
	}
}
/*
 * format telephone number for storage in db, makes it easier to perform lookups when the phone number is cleaner.
 */
function clean_number($phone_number){
	return preg_replace("/[^0-9]/", "", $phone_number);
}
/*
 *	If the user member does not have a code, then we generate it for them.
 */
function generate_code( $digits_needed=8 ){
	$random_number=''; // set up a blank string
	$count=0;
	while ( $count < $digits_needed ) {
		$random_digit = mt_rand(0, 9);
		$random_number .= $random_digit;
		$count++;
	}
	return $random_number;
}

function passhash($password){
	$hash = config('password.hash');
	$salt = config('password.salt');
	switch( $hash ){
		case 'md5':		$password = md5($password);	break;
		case 'hash':	$password = md5($salt.sha1(md5($password)));	break;	//	md5ed with a salt of an sha1 of an md5..
		case 'hash2':	$password = hashit($password);
		default:		$password = $password;	break;
	}
	return $password;
}

function hashit($password){
	//	grab our default salt, create a unique salt of that salt plus entered password, then hash the password with the unique salt...
	$salt = config('password.salt');
	$salt = sha1( md5($password.$salt) );
	return md5( $password.$salt );	
}
 
function generateHashWithSalt($password) {
	$intermediateSalt = md5(uniqid(rand(), true));
	$salt = substr($intermediateSalt, 0, MAX_LENGTH);
	return hash("sha256", $password . $salt);
}

function generateHash($password) {
	if (defined("CRYPT_BLOWFISH") && CRYPT_BLOWFISH) {
		$salt = '$2y$11$' . substr(md5(uniqid(rand(), true)), 0, 22);
		return crypt($password, $salt);
	}
}

function verify($password, $hashedPassword) {
	return crypt($password, $hashedPassword) == $hashedPassword;
}

// make a series of digits into a properly formatted US phone number
function format_phone($number)
{
	$no = preg_replace('/[^0-9+]/', '', $number);

	if(strlen($no) == 11 && substr($no, 0, 1) == "1")
		$no = substr($no, 1);
	elseif(strlen($no) == 12 && substr($no, 0, 2) == "+1")
		$no = substr($no, 2);

	if(strlen($no) == 10)
		return "(".substr($no, 0, 3).") ".substr($no, 3, 3)."-".substr($no, 6);
	elseif(strlen($no) == 7)
		return substr($no, 0, 3)."-".substr($no, 3);
	else
		return $no;

}

function normalize_phone_to_E164($phone) {

	// get rid of any non (digit, + character)
	$phone = preg_replace('/[^0-9+]/', '', $phone);

	// validate intl 10
	if(preg_match('/^\+([2-9][0-9]{9})$/', $phone, $matches)){
		return "+{$matches[1]}";
	}

	// validate US DID
	if(preg_match('/^\+?1?([2-9][0-9]{9})$/', $phone, $matches)){
		return "+1{$matches[1]}";
	}


	// validate INTL DID
	if(preg_match('/^\+?([2-9][0-9]{8,14})$/', $phone, $matches)){
		return "+{$matches[1]}";
	}

	// premium US DID
	if(preg_match('/^\+?1?([2-9]11)$/', $phone, $matches)){
		return "+1{$matches[1]}";
	}

	return $phone;
}  

// return an abbreviated url string. ex: "http://example.com/123/page.htm" => "example.com...page.htm"
function short_url($string, $max_len = 30)
{
	$value = str_replace(array('http://', 'https://', 'ftp://'), '', $string);
	if(strlen($value) > $max_len) {
		$domain = reset(explode('/', $value));
		$domain_len = strlen($domain);
		if($domain_len + 3 >= $max_len) {
			return $domain;
		} else {
			$remaining = strlen($value) - $max_len - $domain_len + 3;
			return $domain . ($remaining > 0 ? '...' . substr($value, -$remaining) : '/');
		}
	} else {
		return $value;
	}
}

function random_str($length = 10) {
	$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_";

	$str = '';
	for($a = 0; $a < $length; $a++)
	{
		$str .= $chars[rand(0, strlen($chars) - 1)];
	}

	return $str;
}

function format_player_time($time_in_seconds) {
	$time_in_seconds = intval($time_in_seconds);
	$minutes = floor($time_in_seconds / 60);
	$seconds = $time_in_seconds - ($minutes * 60);

	return sprintf('%02s:%02s', $minutes, $seconds);
}

function format_time_difference($seconds='', $time='') {
	if(!is_numeric($seconds) || empty($seconds)) return true;

	$CI =& get_instance();
	$CI->lang->load('date');
	if(!is_numeric($time)) $time = date('U');
	$difference = abs($time-$seconds);
	$periods = array('date_second', 'date_minute', 'date_hour', 'date_day', 'date_week', 'date_month', 'date_year');
	$lengths = array('60','60','24');
	for($j=0; $difference >= $lengths[$j]; $j++) {
		if($j==count($lengths)-1) break;
		$difference /= $lengths[$j];
	}

	$difference = round($difference);
	if($difference == 0 && $j==0) $difference = 1;
	if($difference != 1) $periods[$j].= 's';

	if($j == 2 && $difference > 23)
		return date('M j g:i A', $seconds);
	return $difference.' '.strtolower($CI->lang->line($periods[$j])).' ago';
}

function sort_by_date($a, $b)
{
	$a_time = strtotime($a->created);
	$b_time = strtotime($b->created);
	if($a_time == $b_time)
	{
		return 0;
	}

	return ($a_time > $b_time)? -1 : 1;
}

function format_short_timestamp($time)
{
	$start_of_today = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
	$start_of_this_year = mktime(0, 0, 0, 1, 1, date("Y"));

	// error_log("time: $time >>>> " . date("%r", $time));
	// error_log("start_of_today: $start_of_today >>>> " . date("%r", $start_of_today) );
	// error_log("start_of_this_year: $start_of_this_year >>>> " . date("%r", $start_of_this_year));

	if ($time > $start_of_today)
	{
		// return H:MM
		return date("g:i a", $time);
	}
	else if ($time > $start_of_this_year)
	{
		// return something like "Mar 3"
		return date("M j", $time);
	}
	else
	{
		// return M/D/YY
		return date("n/j/y", $time);
	}
}

function format_name($user)
{
	if(is_object($user))
	{
		if(!empty($user->first_name)
		   && !empty($user->last_name))
		{
			return "{$user->first_name} {$user->last_name}";
		}
		return $user->email;
	}

	if(is_array($user))
	{
		if(!empty($user['first_name'])
		   && !empty($user['last_name']))
		{
			return "{$user['first_name']} {$user['last_name']}";
		}

		return $user['email'];
	}

	return '';
}

function format_name_as_initials($user)
{
	if(is_object($user))
	{
		$initials = "";

		if ($user->first_name != '')
		{
			$initials .= substr($user->first_name, 0, 1);
		}

		if ($user->last_name != '')
		{
			$initials .= substr($user->last_name, 0, 1);
		}

		return strtoupper($initials);
	}

	return '';
}

function format_url($url)
{
	$str = $url;
	if(preg_match('/^https?:\/\/([^\/]+)\/.*\/([^\/]+)$/i', $url, $matches) > 0)
	{
		$str = $matches[1]
			.'/.../'
			. $matches[2];
	}

	return $str;
}

function html($data)
{
	if(is_string($data))
	{
		return htmlspecialchars($data, ENT_COMPAT, 'UTF-8', false);
	}

	if(is_array($data))
	{
		foreach($data as $key => $val)
		{
			if(is_string($val))
			{
				$data[$key] = htmlspecialchars($val, ENT_COMPAT, 'UTF-8', false);
			}
			else if(is_array($val))
			{
				$data[$key] = html($val);
			}
			else if(is_object($val))
			{
				$object_vars = get_object_vars($val);
				foreach($object_vars as $prop => $propval)
				{
					$data[$key]->{$prop} = html($propval);
				}
			}
		}
	}
	return $data;
}