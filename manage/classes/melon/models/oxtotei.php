<?php


	namespace Melon\Models;



	class OxToTei extends \MHS\TxtProcessor\LineByLine {

		public $teiHeader = "";

		private $teiDocMain = "";

		private $teiDocEnding = "";

		//array of individual docs with the XML
		private $chunks = [];


		private $idRoot = "NOID";
		
		private $docID = "NOID";
		
		private $metadata = [
			"document-id" => "",
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
			$this->text = preg_replace('/ rend=".*"/U', "", $this->text);
			
			//remove unnecessary style tags
			$this->text = preg_replace('/ style=".*"/U', "", $this->text);

			//remove HI tags
			$this->text = preg_replace('/<hi.*>/U', "", $this->text);
			$this->text = str_replace("</hi>", "", $this->text);
		}




		public function separateDocParts(){
			$parts1 = explode("<body>\n", $this->text);

			$parts2 = explode("</body>\n", $parts1[1]);

			$this->teiHeader = $parts1[0] . "<body>\n";

			$this->teiDocMain =  $parts2[0];

			$this->teiDocEnding = "</body>\n" . $parts2[1];
		}


		public function chunkByChunk(){
			
			//first expand {{DOC}} placeholder
			$this->teiDocMain = str_replace("{{DOC}}", "{{DOC}}**DOCLINE**", $this->teiDocMain);

			//KEEP IN MIND there's a paragraph surrounding the {{DOC}} and the following ID			
			$this->chunks = explode("<p>{{DOC}}", $this->teiDocMain);

			//zero our text holder so we can add chunks as they are done
			$this->teiDocMain = "";
			
			foreach($this->chunks as $chunk){
				
				//skip leading or trailing empty doc
				if(strlen($chunk) < 10) continue;
				
				$chunk = $this->processDocument($chunk);
				
				$this->teiDocMain .= $chunk;
			}
		}

		
		
		
		
		public function rejoinParts(){
			$this->text = $this->teiHeader . $this->teiDocMain . $this->teiDocEnding;
			
			return $this->text;
		}
		




		public function gleanMetadata(){
			
			//which line of actual content are we at?
			$this->contentLineCount = 0;
			
			$this->foundDate = false;
			$this->foundHeader = false;
			
			
			$this->forEachLine(function($line){
				//llook for doc id
				if($line->begins("**DOCLINE**")) {
					$this->metadata['document-id'] = $line->trimLeading()->trimTrailingP()->getText();
				}
					
				//lines beginning with {{  are our header metadata (transcriber, etc)
				elseif($line->begins("<p>{{")){
					
					if($line->contains("{{TRANSCRIBER}}")) $this->metadata['transcriber'] = $line->trimLeading()->trimTrailingP()->getText();
					else if($line->contains("{{TRANSCRIPTION-DATE}}")) {
						
						$tempdate = $line->trimLeading()->trimTrailingP()->getText();
						$this->metadata['transcription-date'] = $this->parseDate($tempdate);
					}
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
			

			$this->docID = $this->idRoot;
		}




		/* this also accepts a string input (coming from chunkByChunk), but
		 * when $text === false, it uses full teiDocMain as text
		 * 
		 * so when NOT passing in $text, we operate on ->teiDocMain, and keep
		 * that updated.
		 * 
		 * When passing in Text, we keep teiDocMain separate
		 */
		public	function processDocument($text = false){
			
			$this->noDocBackYet = true;
			
			$noTextArg = false;

			if($text === false) {
				$noTextArg = true;
				$text = $this->teiDocMain;
			}

			$this->setText($text);

			$this->gleanMetadata();


			//add document beginning with placeholders to pop in metadata once we find it
			if(!empty($this->metadata['document-id'])) {
				$this->docID = $this->metadata['document-id'];
			}
			$this->appendOutput("<div type=\"doc\" xml:id=\"" . $this->docID . "\">\n{{HEAD}}\n{{BIBL}}\n<div type=\"docbody\">\n");
			
			
			//---- more detailed line-by-line processing
			$this->forEachLine(function($line){

				if($line->contains("{{ADDRESS}}")) $this->newSection("<div type=\"addr\">", "</div>");
				else if($line->contains("{{SOURCE}}")) {
					$this->newSection("<div type=\"source\">", "</div>");
				}
				else if($line->contains("{{NOTE}}")) {
					$this->newSection("<note type=\"fn\">", "</note>");
				}
				else if($line->contains("{{INSERTION}}")) {
					$this->newSection("<div type=\"insertion\">", "</div>");
				}
				else if($line->contains("{{CLOSE}}")) {
					$this->newSection("<closer>", "</closer>");
				}

				//add the line if not empty
				if(!strpos($line->text, "}}</p>")) $this->append($line);
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


			//to finish, look at first arg: if we had passed in text, then pass back. otherwise write it back to teiDocMain
			if($noTextArg) {
				$this->teiDocMain = $this->text;
				return;
			}

			//we had passing in a text arg, so return new text
			return $this->text;
		}






		private function parseDate($text){
			
			//if we have dashes, split on those
			if(strpos($text, "-") !== false) $del = "-";
			else $del = " ";
			
			$parts = explode($del, $text);
			
			
			$year = "0000";
			$month = $day = "00";
			
			//YEAR
			if(isset($parts[0])) {	
				if(strlen($parts[0]) == 4 and is_numeric($parts[0])) $year = $parts[0];
			}

				
			//month
			if(isset($parts[1])){
				
				$rawmo = $parts[1];
				
				if(strlen($rawmo) == 2){
					$month = $rawmo;
				}
				
				if(strlen($rawmo) > 2){
					//match month
					if(stripos($rawmo, "jan") !== false) $month = "01";
					else if(stripos($rawmo, "feb") !== false) $month = "02";
					else if(stripos($rawmo, "mar") !== false) $month = "03";
					else if(stripos($rawmo, "apr") !== false) $month = "04";
					else if(stripos($rawmo, "may") !== false) $month = "05";
					else if(stripos($rawmo, "jun") !== false) $month = "06";
					else if(stripos($rawmo, "jul") !== false) $month = "07";
					else if(stripos($rawmo, "aug") !== false) $month = "08";
					else if(stripos($rawmo, "sep") !== false) $month = "09";
					else if(stripos($rawmo, "oct") !== false) $month = "10";
					else if(stripos($rawmo, "nov") !== false) $month = "11";
					else if(stripos($rawmo, "dec") !== false) $month = "12";
				}
				
				if(strlen($rawmo) == 1){
					$month = "0" . $rawmo;
				}
				
			}

			
			//DAY
			if(isset($parts[2])){
				if(strlen($parts[2]) < 3 and is_numeric($parts[1])) {
					$day = $parts[2];
					
					//fix single digitas
					if(strlen($day) == 1) $day = "0" . $day;
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
			$text .= "\t<name type=\"transcriber\">" . $this->metadata['transcriber'] . "</name>\n";
			$text .= "\t<date type=\"transcription\" when=\"" . $this->metadata['transcription-date'] . "\"/>\n";
			$text .= "</bibl>";
			
			$this->findString("{{BIBL}}")->replaceWith($text);
		}
		

	}
