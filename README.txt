Jolt Starter App

App used for starting on new Jolt projects...

Install Composer:

	curl -s https://getcomposer.org/installer | php
 
Create composer.json:

	{
	    "name": "freekrai/joltstarter",
	    "description": "Jolt Starter App",
	    "authors": [
	        {
	            "name": "freekrai",
	            "email": "freekrai@me.com"
	        }
	    ],
	    "require": {
	        "twilio/sdk": "dev-master",
	        "j4mie/idiorm": "v1.4.1",
	        "j4mie/paris": "v1.4.2",
	        "jolt/jolt": "dev-master"
	    }
	}
	
Now, run composer install:

	php composer.phar install

We keep all system files instead a core/ folder, this includes:

	assets/
	vendor/
	core/
		app/
			-	where we store any files we want autoloaded, such as controllers, models, etc
		system/
			-	utility files, such as runtime.php
		views/
			-	views, layout, etc.
	


Create a file called .htaccess (or copy it from vendor/jolt/jolt/):

	RewriteEngine On
	
	# Some hosts may require you to use the `RewriteBase` directive.
	# If you need to use the `RewriteBase` directive, it should be the
	# absolute physical path to the directory that contains this htaccess file.
	#
	# RewriteBase /
	
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^ index.php [QSA,L]

Create, config.ini (or copy it from vendor/jolt/jolt):

	;site settings
	site.name = my site
	
	; rendering vars
	views.root = views
	views.layout = layout
	
	; session vars
	cookies.secret = IeNj0yt0sQu33zeflUFfym0nk1e
	cookies.flash = _F

Finally, create index.php:

	<?php
		require 'vendor/autoload.php';
	
		$app = new Jolt\Jolt();
		Define a HTTP GET route:
		
		$app->get('/hello/:name', function ($name) use ($app){
		    echo "Hello, $name";
		});
		$app->listen();
		
		

You now have a working Jolt App.. Enjoy...



	CREATE TABLE `user` (
	  `id` integer NOT NULL  primary key autoincrement not null,
	  `login` varchar(60) NOT NULL,
	  `pass` varchar(64) NOT NULL,
	  `name` varchar(50) NOT NULL,
	  `email` varchar(100) NOT NULL,
	  `phone` varchar(100) NOT NULL,
	  `registered` datetime not null,
	  `activation_key` varchar(60) NOT NULL,
	  `status` integer NOT NULL,
	  `display_name` varchar(250) NOT NULL,
	  `type` varchar(25) NOT NULL default 'client',
	  `token` varchar(200)   NULL
	);
	CREATE UNIQUE INDEX user_login_key on "user" (
		"login"
	);
	CREATE UNIQUE INDEX user_token on "user" (
		"token"
	);
	CREATE INDEX user_nicename on "user" (
		"display_name"
	);
	
	CREATE TABLE `usermeta` (
	  `id` integer NOT NULL  primary key autoincrement not null,
	  `user_id` integer NOT NULL,
	  `mkey` varchar(255)   NULL,
	  `mvalue` longtext
	);
	CREATE UNIQUE INDEX user_key on "usermeta" (
		"user_id","mkey"
	);
	CREATE INDEX user_user_id on "usermeta" (
		"user_id"
	);
	CREATE INDEX meta_key on "usermeta" (
		"mkey"
	);
	
	CREATE TABLE `post` (
		`id` integer NOT NULL  primary key autoincrement not null,
		`user_id` integer NOT NULL,
		`date` datetime not null,
		`content` longtext NOT NULL,
		`title` text NOT NULL,
		`excerpt` text NOT NULL,
		`password` varchar(20) NOT NULL,
		`name` varchar(200) NOT NULL,
		`status` varchar(20) NOT NULL,
		`modified` datetime not null,
		`content_filtered` text NOT NULL,
		`parent` integer NOT NULL,
		`guid` varchar(255) NOT NULL,
		`category` varchar(255) NOT NULL,
		`type` varchar(20) NOT NULL,
		`mime_type` varchar(100) NOT NULL
	);
	CREATE INDEX post_name on "post" (
		"name"
	);
	CREATE INDEX post_parent on "post" (
		"parent"
	);
	CREATE INDEX post_type on "post" (
		"type"
	);
	CREATE INDEX post_user_id on "post" (
		"user_id"
	);
	
	CREATE TABLE `postmeta` (
		`id` integer NOT NULL  primary key autoincrement not null,
		`post_id` integer NOT NULL,
		`mkey` varchar(255)   NULL,
		`mvalue` longtext
	);
	CREATE UNIQUE INDEX post_post_key on "postmeta" (
		"post_id","mkey"
	);
	CREATE INDEX post_post_id on "postmeta" (
		"post_id"
	);
	CREATE INDEX post_meta_key on "postmeta" (
		"mkey"
	);
	