<?php


	namespace Melon\Models;



	class OxToTei extends \MHS\TxtProcessor\LineByLine {

		private $teiHeader = "";

		private $teiDocMain = "";

		private $teiDocEnding = "";

		//array of individual docs with the XML
		private $chunks = [];


		private $idRoot = "NOID";
		
		private $docID = "NOID";
		
		private $metadata = [
			"transcriber" => "",
			"transcription-date" => "",
			"editor" => "",
			"edition" => "",
			"author" => "",
			"recipient" => "",
			"date" => ""
		];
		
		
		
		
		

		public function text($text = false){
			if(false == $text) return $this->text;
			else $this->text = $text;
		}


		
		public function setIdRoot($id){
			$this->idRoot = $id;
		}
		



		public function prepOxFile(){
					//---- prep OX output

			// add line breaks at specific tags
			$endTags = [ "Desc>", "Stmt>", "Info>", "Change>", "text>", "body>", "</p>"];
			$endTagsModded = [ "Desc>\n", "Stmt>\n", "Info>\n", "Change>\n", "text>\n", "body>\n", "</p>\n"];

			$this->text = str_replace($endTags, $endTagsModded, $this->text);

			//remove unnecessary strings
			$this->text = preg_replace('/ rend="Normal"/', "", $this->text);
			
			//remove unnecessary style tags
			$this->text = preg_replace('/ style=".*"/U', "", $this->text);


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

			//these are all properties so that anon function can get 'em
			
			//which line of actual content are we at?
			$this->contentLineCount = 0;
			
			$this->foundDate = false;
			$this->foundHeader = false;
			
			
			$this->forEachLine(function($line){
				
				//lines beginning with {{  are our header metadata (transcriber, etc)
				if($line->begins("<p>{{")){
					
					if($line->contains("{{TRANSCRIBER}}")) $this->metadata['transcriber'] = $line->trimLeading()->trimTrailingP()->getText();
					else if($line->contains("{{TRANSCRIPTION-DATE}}")) $this->metadata['transcription-date'] = $line->trimLeading()->trimTrailingP()->getText();
					else if($line->contains("{{EDITOR}}")) $this->metadata['editor'] = $line->trimLeading()->trimTrailingP()->getText();
					else if($line->contains("{{EDITION}}")) $this->metadata['edition'] = $line->trimLeading()->trimTrailingP()->getText();
					
				}
				
				else {
					if(strlen($line->text) < 4) return;	
					if($this->contentLineCount > 2) {
						$this->break = true;
						return;
					}
					$this->contentLineCount++;
					
					
					$innerLine = str_replace(["<p>", "</p>"], "", $line->text);
					
					//look for year
					if(!$this->foundDate and is_numeric(substr($innerLine, 0, 4))){
						$this->metadata['date'] = $this->parseDate($innerLine);
						$this->foundDate = true;
					}
					
					//look for author: first 2 lines, not date, not <p>{{ MUST be author accoding to our rules
					else if(!$this->foundHeader){
						$this->parseHeading($innerLine);
						$this->foundHeader = true;						
					}
				}
			});
			
			
			print_r($this->metadata);
//			die();

			$this->docID = $this->idRoot;
		}



		/* this also accepts a string input (coming from chunkByChunk), but
		 * when $text === false, it uses full teiDocMain as text
		 */
		public	function processDocument($text = false){

			if($text === false) $text = $this->teiDocMain;

			$this->setText($text);

			$this->gleanMetadata();

			//add document beginning with placeholders to pop in metadata once we find it
			$this->appendOutput("<div type=\"doc\" xml:id=\"" . $this->docID . "\">\n{{HEAD}}\n{{BIBL}}\n<div type=\"docbody\">\n");
			
			
			//---- more detailed line-by-line processing
			$this->forEachLine(function($line){

				if($line->contains("{{SIGNED}}")) {
					$this->newSection("<closer>", "</closer>")->append($line, \MHS\TxtProcessor\Line::KEEP_PATTERN);
					return;
				}
				else if($line->contains("{{ADDRESS}}")) $this->newSection("<div type=\"addr\">", "</div>");
				else if($line->contains("{{SOURCE}}")) $this->newSection("<div type=\"source\">", "</div>");
				else if($line->contains("{{NOTE}}")) $this->newSection("<note type=\"fn\">", "</note>");
				else if($line->contains("{{INSERTION}}")) $this->newSection("<div type=\"insertion\">", "</div>");

				$this->append($line);
			});

			//close any sections
			$this->closeSection();

			//close document
			$this->appendOutput("</div>\n</div><!-- //document -->\n");


			//rest of processes operate directly on text, so copy output back to text
			$this->updateText();
			
			
			//some paragraph replacements, single line, not sectional
			$this->findString("{{SALUTE}}")->inParagraphs()->rewrapWith("salute")->remove();
			$this->findString("{{DATELINE}}")->inParagraphs()->rewrapWith("dateline")->remove(); 
			$this->findString("{{SIGNED}}")->inParagraphs()->rewrapWith("signed")->remove();
			$this->findString("{{PS}}")->inParagraphs()->rewrapWith("postscript")->remove();

			$this->findString("{{ILL}}")->replaceWith("<unclear/>");
			$this->findString("{{DAMAGE}}")->replaceWith("<damage/>");
			$this->findString("{{BLANK}}")->replaceWith("<space/>");
			$this->findString("{{INS}}")->replaceWith("<add></add>");

			
			//wrap the dateline/salute elements in <opener>
			$this->findString("<dateline>")->replaceWith("<opener>\n<dateline>");
			$this->findString("</salute>")->replaceWith("</salute>\n</opener>");
			

			//interate over pagebreacks
			$this->replaceEach("{{PB}}", function($i){ return '<pb n="' . ($i + 2) . '"/>';	});
			$this->replaceEach("<note ", function($i){ return '<note xml:id="' . $this->docID . "-fn-" . ($i + 1) . '">';	});

			//swap in pre-gathered metadata
			$this->placeHead();
			
			$this->placeBibl();

			//final clean up
			
			//remove the paragraphs with the metadata
			$this->findRegex('<div type="docbody">.*<opener>', 's')->replaceWith("<div type=\"docbody\">\n<opener>");

			
			print $this->getText();
		}






		private function parseDate($text){
			
			//if we have dashes, split on those
			if(strpos($text, "-") !== false) $del = "-";
			else $del = " ";
			
			$parts = explode($del, $text);
			
			
			$year = "0000";
			$month = $day = "00";
			
			foreach($parts as $part){
				
				//year
				if(strlen($part) == 4 and is_numeric($part)) $year = $part;
				
				//day
				else if(strlen($part) < 3 and is_numeric($part)) {
					$day = $part;
					
					//fix single digitas
					if(strlen($part) == 1) $day = "0" . $day;
				}
				
				//month
				else if(strlen($part) > 2){
					
					//match month
					if(stripos($part, "jan") !== false) $month = "01";
					else if(stripos($part, "feb") !== false) $month = "02";
					else if(stripos($part, "mar") !== false) $month = "03";
					else if(stripos($part, "apr") !== false) $month = "04";
					else if(stripos($part, "may") !== false) $month = "05";
					else if(stripos($part, "jun") !== false) $month = "06";
					else if(stripos($part, "jul") !== false) $month = "07";
					else if(stripos($part, "aug") !== false) $month = "08";
					else if(stripos($part, "sep") !== false) $month = "09";
					else if(stripos($part, "oct") !== false) $month = "10";
					else if(stripos($part, "nov") !== false) $month = "11";
					else if(stripos($part, "dec") !== false) $month = "12";
				}
				
			}
			
			return $year . "-" . $month . "-" . $day;

		}

		
		
		
		private function parseHeading($text){
			
			if(strpos($text, " to ") !== false) {
				$parts = explode(" to ", $text);
				$this->metadata['author'] = trim($parts[0]);
				$this->metadata['recipient'] = trim($parts[1]);
			}
			else $this->metadata['author'] = trim($text);
			
		}

		
		
		
		private function placeHead(){
			
			$text = "<head>" . $this->metadata['author'];
			
			if(!empty($this->metadata['recipient'])){
				$text .= " to " . $this->metadata['recipient'];
			}
			
			$text .= "</head>";
			
			$this->findString("{{HEAD}}")->replaceWith($text);
		}
		
		
		
		private function placeBibl(){
			
			$text = "<bibl>\n";
			$text .= "\t<date type=\"creation\" when=\"" . $this->metadata['date'] . "\"/>\n";
			$text .= "\t<author>" . $this->metadata['author'] . "</author>\n";
			$text .= "\t<name type=\"recip\">" . $this->metadata['recipient'] . "</name>\n";
			$text .= "</bibl>";
			
			$this->findString("{{BIBL}}")->replaceWith($text);
		}

	}
