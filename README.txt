PHP Port of The Twilio Call Ad Demo that was originally built in Python here:

https://www.twilio.com/blog/2014/07/creating-a-click-to-call-service-with-twilio-client-pusher-and-python.html

To get started:

1. Install Composer:

	curl -s https://getcomposer.org/installer | php
 
2. Now, run composer install:

	php composer.phar install

3. This will set up our libraries.

4. Edit config.ini

	;site settings
	site.name = Call Ad Demo
	
	; rendering vars
	views.root = content/views
	views.layout = layout
	
	; session vars
	cookies.secret = IeNj0yt0sQu33zeflUFfym0nk1e
	cookies.flash = _F
	
	; password hashing
	password.hash = hash
	password.salt = IeNj0yt0sQu33zeflUFfym0nk1e
	
	; twilio vars
	twilio.enabled = true
	twilio.accountsid = 'YOUR-TWILIO-ACCOUNT-SID'
	twilio.authtoken = 'YOUR-TWILIO-ACCOUNT-TOKEN'
	twilio.fromNumber = 'YOUR-TWILIO-PHONE-NUMBER'
	twilio.appid = 'YOUR-TWILIO-APP-ID'
	
	; pusher stuff
	pusher.enabled = true
	pusher.appid = 'xxxxxxxxxxxxxxxxxxxx'
	pusher.key = 'xxxxxxxxxxxxxxxxxxxx'
	pusher.secret = 'xxxxxxxxxxxxxxxxxxxx'
	pusher.channel = 'twilio_call_center'

5. In your Twilio app that you created, add the following URL for voice:

	http://MYSITEURL/voice
		
	This will handle voice calls.
	
6. Upload the files and run, the index will display your ads, and the control center can be accessed via:

	http://MYSITEURL/control

Enjoy :)
