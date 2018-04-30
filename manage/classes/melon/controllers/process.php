<?php

	/* uber class for parsing the URL request to determine which items to gather
	 */



	namespace Melon\Controllers;



	class Process {

		const EXTRAS_FILE = "oxout.xml";

		const EXTRAS_FILE_BR = "oxout-br.xml";
		
		
		

		function index(){

		
			$M = new \Melon\Models\WordToTei();

			
			//---- load text
			$text = file_get_contents(\MHS\Env::APP_INSTALL_DIR . "/incl/". $this::EXTRAS_FILE);
			
			$M->text($text);
			
			$M->prepOxFile();

			file_put_contents(\MHS\Env::APP_INSTALL_DIR . "/incl/". $this::EXTRAS_FILE_BR, $M->text());

			$M->separateDocParts();
	
	
			//if we have chunks, that is, more than one document
			if(strpos($text, '{{DOC}}')) {
				
			}
			
			//single doc
			else {
				$M->processDocument();
			}



		}
	
		


	} //class
