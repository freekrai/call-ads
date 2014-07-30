<?php
$_GET['route'] = isset($_GET['route']) ? '/'.$_GET['route'] : '/';

// Check for composer installed
if (file_exists('vendor/autoload.php')){
	include_once('vendor/autoload.php');
}else{
	echo '{"error":"Composer Install"}';
	header('HTTP/1.1 500 Internal Server Error', true, 500);
	return False;
}

include("core/system/runtime.php");

$app = new Jolt\Jolt();
$app->option('source', 'config.ini');
$app->store('default_client','anonymous');

if( $app->option('twilio.enabled') != false ){
	$client = new Services_Twilio($app->option('twilio.accountsid'), $app->option('twilio.authtoken') );
	$fromNumber = $app->option('twilio.fromNumber');
	
	//	store Twilio client and our Twilio fromNumber in our session store...
	$app->store('client',$client);
	$app->store('fromNumber',$fromNumber);
}

if( $app->option('pusher.enabled') != false ){
	$pusher = new Pusher(
		$app->option('pusher.key'),
		$app->option('pusher.secret'),
		$app->option('pusher.appid')
	);

	//	store our Pusher client in our session store...
	$app->store('pusher',$pusher);
}

//	Twimlets	--------------------------------------------------------------------------------------------
/*
$app->route('/voicemail', 'twimletsController#voicemail');
$app->route('/forward', 'twimletsController#forward');
$app->route('/conference', 'twimletsController#conference');
$app->route('/menu', 'twimletsController#menu');
$app->route('/findme', 'twimletsController#findme');
$app->route('/simulring','twimletsController#simulring');
$app->route('/callme','twimletsController#callme');
$app->route('/message','twimletsController#message');
$app->route('/echo','twimletsController#echoml');
$app->route('/holdmusic','twimletsController#holdmusic');
$app->route('/whisper','twimletsController#whisper');
*/

//	Logged in area	--------------------------------------------------------------------------------------------

$app->get('/', function() use ($app){
	$client_name = $app->store('default_client');
	if( isset($_REQUEST['client']) ){
		$client_name = $_REQUEST['client'];
	}
	$app->render( 'home', array(
		'token' => build_twilio_token($client_name),
		'client_name' => 'Admin'	
	),'blank' );
});
$app->get('/control', function() use ($app){
	$app->render( 'control', array(
		'token' => build_twilio_token('Admin'),
		'client_name' => 'Admin',
		'pusher_key' => $app->option('pusher.key')	
	),'blank' );
});
$app->route('/voice', function() use ($app){
	$item = $_REQUEST['item'];
	$name = $_REQUEST['name'];
	$app->store('pusher')->trigger(
		$app->option('pusher.channel'),
		'new-caller',
		array(
			'item'=>$item,
			'name'=>$name
		)
	);
	echo '<Response><Dial><Client>Admin</Client></Dial></Response>';
});


//	404 page  --------------------------------------------------------------------------------------------
$app->get('.*',function() use ($app){
	$app->error(404, $app->render('404',  array(
		"pageTitle"=>"404 Not Found",
	),'layout'));
});

$app->listen();