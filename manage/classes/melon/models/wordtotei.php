<?php


	namespace Melon\Models;
	
	
	
	class WordToTei extends \MHS\TxtProcessor\LineByLine {
	
		private $teiHeader = "";
		
		private $teiDocMain = "";
		
		private $teiDocEnding = "";
	
		//array of individual docs with the XML
		private $chunks = [];



		public function text($text = false){
			if(false == $text) return $this->text;
			else $this->text = $text;
		}
	
	
	


		public function prepOxFile(){
					//---- prep OX output

			// add line breaks
			$endTags = [ "Desc>", "Stmt>", "Info>", "Change>", "text>", "body>", "</p>"];
			$endTagsModded = [ "Desc>\n", "Stmt>\n", "Info>\n", "Change>\n", "text>\n", "body>\n", "</p>\n"];
			
			$this->text = str_replace($endTags, $endTagsModded, $this->text);

			//remove unnecessary strings
			$remove = [' rend="Normal"'];
			$this->text = str_replace($remove, "", $this->text);
		}
		
		
		
		
		public function separateDocParts(){
			$parts1 = explode("<body>\n", $this->text);
			
			$parts2 = explode("</body>\n", $parts1[1]);
			
			$this->teiHeader = $parts1[0] . "<body>\n";
			
			$this->teiDocMain =  $parts2[0];
			
			$this->teiDocEnding = "</body>\n" . $parts2[1];
		}





		public function chunkByChunk(){
			
			$this->chunks = explode("<p>{{DOC}}</p>\n", $this->teiDocMain);
			
die("WordToTei->chunkByChunk() not yet implemented");			
		}
		
		

		public function gleanMetadata(){
			
			$this->parseDate($this->deleteParagraphContaining("{{DATELINE}}"));
			
		}
		


		/* this also accepts a string input (coming from chunkByChunk), but
		 * when $text === false, it uses full teiDocMain as text
		 */
		public	function processDocument($text = false){
				
			if($text === false) $text = $this->teiDocMain;
			
			$this->setText($text);

			$this->gleanMetadata();

			//add document beginning
			$this->appendText("<div type=\"doc\">{{HEAD}}\n{{BIBL}}\n<div type=\"docbody\">\n{{OPENER}}");
			
			//---- more detailed line-by-line processing
			$this->forEachLine(function($line){

				if($line->contains("{{SIGNED}}")) $this->newSection("<closer>", "</closer>");
				else if($line->contains("{{PS}}")) $this->newSection("<postscript>", "</postscript>");
				else if($line->contains("{{ADDRESS}}")) $this->newSection("<div type=\"addr\">", "</div>");
				else if($line->contains("{{SOURCE}}")) $this->newSection("<div type=\"source\">", "</div>");
				else if($line->contains("{{NOTE}}")) $this->newSection("<div type=\"note\">", "</div>");
				else if($line->contains("{{INSERTION}}")) $this->newSection("<div type=\"insertion\">", "</div>");
				
				//add line to output
				$this->appendOutput($line);

			});
			//close any sections
			$this->closeSection();
			
			//close document
			$this->appendText("</div>\n</div><!-- //document -->\n");

			//swap in pre-gathered metadata
			
			
			
			print $this->getOutput();
		}
	
			
		



		private function parseDate($matchArray){
			preg_match("/^<p>(.+)<\/p>$/", $line->text, $matches);
			
			if(!isset($matches[1])) return;
			
			//separate
			$parts = explode("-", $matches[1]);

			//day
			if(!isset($parts[2])) $day = "00";
			else {
				$day = $parts[2];
			}
			
			//month
			if(!isset($parts[1])) $month = "00";
			else {
				if(!is_numeric($parts[1][0])) {
					
				}
			}
			
			$this->LBL->appendText("\n");
		
			$this->lineCount++;
			return;
		}
	
	
	
	}