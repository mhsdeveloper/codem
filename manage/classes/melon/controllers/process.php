<?php

	/* uber class for parsing the URL request to determine which items to gather
	 */



	namespace Melon\Controllers;



	class Process {

		const EXTRAS_FILE = "oxout.xml";

		const EXTRAS_FILE_BR = "oxout-br.xml";
		
		
		

		function index(){


			//---- load text
			$text = file_get_contents(\MHS\Env::APP_INSTALL_DIR . "/incl/". $this::EXTRAS_FILE);
			

	
			//---- prep OX output
	
			// add line breaks
			
			$endTags = [ "Desc>", "Stmt>", "Info>", "Change>", "text>", "body>", "</p>"];
			$endTagsModded = [ "Desc>\n", "Stmt>\n", "Info>\n", "Change>\n", "text>\n", "body>\n", "</p>\n"];
			
			$text = str_replace($endTags, $endTagsModded, $text);

			
			
			//remove unnecessary strings
			$remove = [' rend="Normal"'];
			$text = str_replace($remove, "", $text);
			
			
			file_put_contents(\MHS\Env::APP_INSTALL_DIR . "/incl/". $this::EXTRAS_FILE_BR, $text);

		}
		
		
		
		function processChunk($chunk){
			
			//---- setup
			$this->template->reset();
			
			$this->LBL = new \MHS\TxtProcessor\LineByLine();
			$LBL = $this->LBL;
			
			$LBL->setText($chunk);
			
			
			
			//---- global replacements
/*			
			$LBL->find("name")->replaceWith("friend");
			$LBL->find("joe")->replaceWith("<b>jill</b>");
			$LBL->find("##ENDITAL##")->replaceWith("</i>");
*/

			//---- more detailed line-by-line processing

			$LBL->forEachLine(function($line){
				
				if($line->beginsWith("Name:")) $this->template->gleanName($line);
				//if($line->beginsWith("Birth:")) $this->template->gleanBirth($line);

				if($line->beginsWith("<p>{{ADDRESS}}")) $this->LBL->newSection("<div type=\"addr\">", "</div>");

				
				//add line to output
//				$this->LBL->appendLine($line);

			});

			
			print $this->template->getOutput();
			
//			print $this->LBL->getOutput();
		}

	} //class
