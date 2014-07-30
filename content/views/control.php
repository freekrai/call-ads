<!DOCTYPE html>
<html>
<head>
	<title>Control center</title>
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css">
	<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet">
	<link href="<?= $uri ?>/assets/bootflat/css/bootflat.css" rel="stylesheet">
	
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script src="//static.twilio.com/libs/twiliojs/1.1/twilio.min.js"></script>
	<script src="http://js.pusher.com/2.2/pusher.min.js" type="text/javascript"></script>
	<style type="text/css">
		.center {
			text-align: center;
		}
		.red {
			color: #DA4453;
		}
		.blue {
			color: #4A89DC;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col-md-6">
				<h1 class="red">Incoming calls</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="list-group">
					<div class="list-group-item">
						<h4 class="list-group-item-heading warning"></h4>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		Twilio.Device.setup("<?=$token?>");
		Twilio.Device.incoming(function (conn) {
			// accept the incoming connection and start two-way audio
			conn.accept();
		});
		function hangup() {
			Twilio.Device.disconnectAll();
		}
		console.log('<?=$client_name?>');
	</script>
	<script type='text/javascript'>
		// Enable pusher logging - don't include this in production
		Pusher.log = function(message) {
			if (window.console && window.console.log) {
				window.console.log(message);
			}
		};
		var pusher = new Pusher('<?=$pusher_key?>');
		var channel = pusher.subscribe('twilio_call_center');
		channel.bind('new_caller', function(data) {
			$('.warning').val(data['name'] + ' wants a ' + data['item']);
		});
	</script>
</body>
</html>