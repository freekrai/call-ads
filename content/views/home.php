<!DOCTYPE html>
<html>
<head>
	<title>Ahoy hoy!</title>
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css">
	<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet">
	<link href="<?= $uri ?>/assets/bootflat/css/bootflat.css" rel="stylesheet">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script src="//static.twilio.com/libs/twiliojs/1.1/twilio.min.js"></script>
	<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.0/js/bootstrap.min.js"></script>
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
				<h1 class="red">Paul's website</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<h4 class="center blue">Click an ad to purchase now!</h4>
				<div class="list-group">
					<a onClick="call('Sofa');" class="list-group-item">
						<h4 class="list-group-item-heading">That's a fancy sofa</h4>
						<p class="center list-group-item-text"><img src="<?= $uri ?>/assets/img/sofa.jpg"></p>
					</a>
					<a onClick="call('Jug');" class="list-group-item">
						<h4 class="list-group-item-heading">What a lovely receptacle</h4>
						<p class="center list-group-item-text"><img src="<?= $uri ?>/assets/img/jug.jpg"></p>
					</a>
					<a onClick="call('Owl');" class="list-group-item">
						<h4 class="list-group-item-heading">I want that owl!</h4>
						<p class="center list-group-item-text"><img src="<?= $uri ?>/assets/img/owl.jpg"></p>
					</a>
				</div>
				<a onClick="hangup();" class="btn btn-primary" style="display:none;" id="hangup">Hang up</a>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		Twilio.Device.setup("<?=$token?>");
		function call(item_of_choice) {
			params = {"item": item_of_choice, "name": '<?=$client_name?>'};
			Twilio.Device.connect(params);
			$("#hangup").show();
		}
		function hangup() {
			Twilio.Device.disconnectAll();
			$("#hangup").hide();
		}
		console.log('<?=$client_name?>');
	</script>
</body>
</html>