<?php

	/*
		This is the master router index for our app

	*/



	//use our autoloader
	require "autoloader.php";


	$classLoader = new SplClassLoader('MHS', SERVER_WWW_ROOT . 'incl/classes');
	$classLoader->register();

	$fogLoader = new SplClassLoader('Foghorn', SERVER_WWW_ROOT . 'html/database/foghorn/classes');
	$fogLoader->register();

	$pubsLoader = new SplClassLoader('Publications', SERVER_WWW_ROOT . 'html/publications/lib/classes');
	$pubsLoader->register();

	//this project's classes
	$projectLoader = new SplClassLoader('Melon', SERVER_WWW_ROOT . 'html/publications/melon/manage/classes');
	$projectLoader->register();


	//load settings
	include("../classes/environment.php");


	//load our mvc framework
	require "mhsmvc2.php";
	$mvc = new MHSmvc();




	/**********************************************
	 * ROUTING
	 *********************************************/

	$mvc->route("/", "");



	//start!
	$mvc->run();
