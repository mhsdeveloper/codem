<?php

	/*
		This is the master router index for our app

	*/



	//use our autoloader
	require "autoloader.php";


	$classLoader = new SplClassLoader('MHS', '/var/www/incl/classes');
	$classLoader->register();

	$fogLoader = new SplClassLoader('Foghorn', '/var/www/html/database/foghorn/classes');
	$fogLoader->register();

	$pubsLoader = new SplClassLoader('Publications', '/var/www/html/publications/lib/classes');
	$pubsLoader->register();

	//this project's classes
	$projectLoader = new SplClassLoader('Melon', '/var/www/html/publications/melon/manage/classes');
	$projectLoader->register();


	//load settings
	include("../classes/environment.php");


	//load our mvc framework
	require "mhsmvc2.php";
	$mvc = new MHSmvc();




	/**********************************************
	 * ROUTING
	 *********************************************/

	$mvc->route("/convert/upload", "Melon\Controllers\Convert@upload");

	$mvc->route("/convert/process", "Melon\Controllers\Convert@process");


	$mvc->route("/convert", "\Melon\Controllers\Convert");
	
	



	//start!
	$mvc->run();
