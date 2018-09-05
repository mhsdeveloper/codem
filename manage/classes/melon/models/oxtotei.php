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
			"authors" => "",
			"recipient" => "",
			"date" => "",
			"head" => ""
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

			// add line breaks at specific tags, both open and close, except for p
			$endTags = [ "Desc>", "Stmt>", "Info>", "Change>", "text>", "body>", "</p>"];
			$endTagsModded = [ "Desc>\n", "Stmt>\n", "Info>\n", "Change>\n", "text>\n", "body>\n", "</p>\n"];
			$this->text = str_replace($endTags, $endTagsModded, $this->text);

			//make all <p> simply <p>
			//remove unnecessary @rend
			$this->text = preg_replace('/<p .*>/U', "<p>", $this->text);

			//add our application tags
			$app = '</application>' . "\n" . '<application ident="MHS-WET" version="0.1a"><label>MHS-WET</label></application>';
			$this->text = str_replace("</application>", $app, $this->text);
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
					else if($line->contains("{{AUTHOR}}")) $this->metadata['authors'] = $line->trimLeading()->trimTrailingP()->getText();
					else if($line->contains("{{HEAD}}")) $this->metadata['head'] = $line->trimLeading()->trimTrailingP()->getText();
					else if($line->contains("{{DATE}}")) {
						$tempdate = $line->trimLeading()->trimTrailingP()->getText();
						$this->metadata['date'] = $this->parseDate($tempdate);
					}

				}
			});

			//reconcile what we've found for the head
			$this->parseHead();

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
		public function processDocument($text = false){

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
			$this->appendOutput("<div type=\"doc\" xml:id=\"" . $this->docID . "\">\n{{BIBL}}\n");
			$this->newSection("<div type=\"docbody\">",""); //no closing tag because we'll need to nest other sections
			$this->appendOutput("\n{{HEAD}}\n");

			//---- more detailed line-by-line processing
			$this->forEachLine(function($line){

				if($line->contains("{{CLOSE}}")) {
					$this->newSection("<closer>", "</closer>");
					$this->appendOutput($line->trimLeading()->trimTrailingP()->getText());
				}
				else if($line->contains("{{PS}}")) {
					$this->newSection("<postscript>", "</postscript>");
				}
				else if($line->contains("{{ADDRESS}}")) {
					$this->newSection("<note type=\"address\">", "</note>", "p");
				}
				else if($line->contains("{{INSERTION}}")) {
					$this->newSection("<div type=\"insertion\">", "</div>");
				}
				else if($line->contains("{{ENDORSEMENT}}")) {
					$this->newSection("<note type=\"endorsement\">", "</note>");
				}

				else if($line->contains("{{SOURCE}}")) {
					//close any sections
					$this->closeSection();
					//close docbody
					$this->appendOutput("</div>\n");
					//here we sneak in the docback div
					$this->newSection("<div type=\"docback\">\n", ""); //again, no closing tag since we need to next other sections
					$this->newSection("<note type=\"source\">", "</note>");
				}

				else if($line->contains("{{NOTE}}")) {
					$this->newSection("<note type=\"fn\">", "</note>");
				}


				//add the line
				$this->append($line);
			});

			//close any sections
			$this->closeSection();

			//close document; closes the docback and the doc
			$this->appendOutput("\n</div>\n</div><!-- //document -->\n");
			//rest of processes operate directly on text, so copy output back to text
			$this->updateText();

			//some paragraph replacements, single line or string, not sectional
			$this->findString("{{SALUTE}}")->inParagraphs()->rewrapWith("salute")->remove();
			$this->findString("{{DATELINE}}")->inParagraphs()->rewrapWith("dateline")->remove();
			$this->findString("{{SIGNED}}")->inParagraphs()->rewrapWith("signed")->remove();
//			$this->findString("{{PS}}")->inParagraphs()->wrapWith("postscript")->remove();

			$this->findString("{{ILL}}")->replaceWith("<unclear/>");
			$this->findString("{{DAMAGE}}")->replaceWith("<damage/>");
			$this->findString("{{BLANK}}")->replaceWith("<space/>");
			$this->findString("<p>{{BLANK-BLOCK}}</p>")->replaceWith("<space type=\"block\"/>");
			$this->findString("[")->replaceWith("<supplied>");
			$this->findString("]")->replaceWith("</supplied>");
			$this->findString("^:")->replaceWith("<add>");
			$this->findString("^")->replaceWith("</add>");


			//wrap the dateline/salute elements in <opener>
			$this->findString("<dateline>")->replaceWith("<opener>\n<dateline>");
			$this->findString("</salute>")->replaceWith("</salute>\n</opener>");


			//interate over repeatables
			$this->replaceEach("{{PB}}", function($i){ return '<pb n="' . ($i + 2) . '"/>';	});
			$this->replaceEach("{{N}}", function($i){ return '<ptr n="' . ($i + 1) . '" target="' . $this->docID . "-fn-" . ($i + 1) .  '"/>';	});
			$this->replaceEach("<note ", function($i){ return '<note xml:id="' . $this->docID . "-fn-" . ($i + 1) . '" ';	});
			$this->replaceEach("{{INS}}", function($i){ return '<ptr n="' . ($i + 1) . '" target="' . $this->docID . "-ins-" . ($i + 1) .  '"/>';	});
			$this->replaceEach("<div type=\"insertion\">", function($i){ return '<div type="insertion" xml:id="' . $this->docID . "-ins-" . ($i + 1) .  '">';	});

			//remove HI tags
			$this->text = preg_replace('/<hi.*>/U', "", $this->text);
			$this->text = str_replace("</hi>", "", $this->text);


			//remove unnecessary @rend
			$this->text = preg_replace('/ rend=".*"/U', "", $this->text);

			//remove unnecessary @style
			$this->text = preg_replace('/ style=".*"/U', "", $this->text);


			//swap in pre-gathered metadata
			$this->placeHead();

			$this->placeBibl();

			//move insertions to correct location between docbody and docback
			preg_match('#<div type="insertion".*</div>#U', $this->text, $matches);

			print_r($matches);

			//final clean up

			//remove the paragraphs with the metadata
			$this->findRegex('</head>.*<opener>', 's')->replaceWith("</head>\n<opener>");


			//to finish, look at first arg: if we had passed in text, then pass back. otherwise write it back to teiDocMain
			if($noTextArg) {
				$this->teiDocMain = $this->text;
				return;
			}

			//we had passed in a text arg, so return new text
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
				if(strlen($parts[2]) < 3 and is_numeric($parts[2])) {
					$day = $parts[2];
					//fix single digitas
					if(strlen($day) == 1) $day = "0" . $day;
				}
			}


			return $year . "-" . $month . "-" . $day;
		}





		private function parseHead(){

			//authors specified, so we can just use the head as is
			if(!empty($this->metadata['authors'])) return;

			//if authors not specified in WET, parse head instead

			//discover any " to " grammar
			if(stripos($this->metadata['head'], "to ") === 0) $separator = "to ";
			else if(stripos($this->metadata['head'], " to ") !== false) $separator = " to ";
			else return;

			if(strpos($this->metadata['head'], $separator) !== false) {
				$parts = explode($separator, $this->metadata['head']);
				$this->metadata['author'] = trim($parts[0]);
				$this->metadata['recipient'] = trim($parts[1]);
			}

		}




		private function placeHead(){
			$text = "<head>" . $this->metadata['head'] . "</head>";

			$this->findString("{{HEAD}}")->replaceWith($text);
		}



		private function placeBibl(){

			$text = "<bibl>\n";
			$text .= "\t<date type=\"creation\" when=\"" . $this->metadata['date'] . "\"/>\n";

			if(!empty($this->metadata['authors'])) {
				$aset = explode(";", $this->metadata['authors']);
				foreach($aset as $author){
					$text .= "\t<author>" . trim($author) . "</author>\n";
				}
			}

			if(!empty($this->metadata['recipient'])) $text .= "\t<name type=\"recip\">" . $this->metadata['recipient'] . "</name>\n";
			$text .= "\t<name type=\"transcriber\">" . $this->metadata['transcriber'] . "</name>\n";
			$text .= "\t<date type=\"transcription\" when=\"" . $this->metadata['transcription-date'] . "\"/>\n";
			$text .= "</bibl>";

			$this->findString("{{BIBL}}")->replaceWith($text);
		}


	}
