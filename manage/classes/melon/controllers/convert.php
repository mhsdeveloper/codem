<?php

	/*
	 */

	namespace Melon\Controllers;



	class Convert {

		private $response = [];



		
		function index(){

			$this->_mvc->render("word-to-tei.php");

		}


		
		
		function upload(){

			$this->uploader = new \MHS\Uploader();
			$this->uploader->addAllowedFileType(".docx");
			$this->uploader->setDestFolder(\MHS\Env::CONVERT_UPLOAD_DIR);

			//check return from upload
			if(false === $this->uploader->upload()) {
				return $this->ajaxError($this->uploader->getError());
			}

			$filename = $this->uploader->getFilename();

			$this->response = ["filename" => $filename, "message" => "Uploaded $filename."];

			$this->ajaxResponse();
		}




		function process(){

			$filename = str_replace(["..", "/"], "", $_GET['filename']);

			$fullpath = \MHS\Env::CONVERT_UPLOAD_DIR . $filename;

			if(!is_readable($fullpath)){
				return $this->ajaxError("Unable to read $filename in upload dir");
			}

			$Ox = new \Melon\Models\WordToOx();

			if(false === $Ox->setFile(($fullpath))) {
				return $this->ajaxError("Word to Ox script could not read $filename.");
			}

			if(false === $Ox->process()){
				return $this->ajaxError("Unable to process your Word file with Oxgarage.");
			}

			$fullpath = $Ox->getFullOutputPath();
			$idRoot = $Ox->getIdRoot();
			$this->text = file_get_contents($fullpath);

file_put_contents($fullpath . "-ox.xml", $this->text);

			$this->runPreWETxslt();
			
file_put_contents($fullpath . "-ox-prewet.xml", $this->text);
			
			$T = new \Melon\Models\OxToTei();
			$T->setIdRoot($idRoot);

			$T->text($this->text);

			$T->separateDocParts();

			//if we have chunks, that is, more than one document
			if(strpos($this->text, '{{DOC}}')) {
				$T->chunkByChunk();
				$T->rejoinParts();
			}

			//single doc
			else {
				$T->processDocument();
				$T->rejoinParts();
			}

			$this->text = $T->text();

file_put_contents($fullpath . "-ox-postwet.xml", $this->text);
			
			$this->runPostWETxslt();

			//send text back to model
			$T->text($this->text);
			$T->numberNotes();

			//and get FINAL version from model 
			$this->text = $T->text();
			$this->finalFormatting();

			//save
			file_put_contents($fullpath, $this->text);

			$this->response["message"] = "Ready to download TEI";
			$this->response['status'] = "download";
			$this->response['filename'] = str_replace($_SERVER['DOCUMENT_ROOT'], "", $fullpath);

			$this->ajaxResponse();
		}
		
		
		
		
		private function runPreWETxslt(){
			$Prep = new \Melon\Models\OxPrep();
			
			if(false == $Prep->loadXSLT(\MHS\Env::PRE_WET_XSLT)){
				$error = $Prep->errorMsg;
				return $this->ajaxError("Error loading the PRE_WET_XSLT: " . \MHS\Env::PRE_WET_XSLT );
			}

			if(false == $Prep->runTransform($this->text)){
				$error = $Prep->errorMsg;
				return $this->ajaxError("XSLT post-processing the Oxgarage output failed for this reason: " . $error);
			}
			
			$this->text = $Prep->getOutput();
		}



		private function runPostWETxslt(){
			$Prep = new \Melon\Models\OxPrep();
			
			if(false == $Prep->loadXSLT(\MHS\Env::POST_WET_XSLT)){
				$error = $Prep->errorMsg;
				return $this->ajaxError("Error loading the POST_WET_XSLT: " . \MHS\Env::POST_WET_XSLT );
			}

			if(false == $Prep->runTransform($this->text)){
				$error = $Prep->errorMsg;
				return $this->ajaxError("XSLT post-processing the WET output failed for this reason: " . $error);
			}
			
			$this->text = $Prep->getOutput();
		}



		



		public function finalFormatting(){

			// add line breaks at specific tags, both open and close, except for p
			$endTags = [ "Desc>", "Stmt>", "Info>", "Change>", "text>", "body>", "</p>", "<lb/>"];
			$endTagsModded = [ "Desc>\n", "Stmt>\n", "Info>\n", "Change>\n", "text>\n", "body>\n", "</p>\n", "<lb/>\n"];
			$this->text = str_replace($endTags, $endTagsModded, $this->text);

			//remove these 
			$this->text = str_replace(' xml:space="preserve"', "", $this->text);

			//simple replacements
			$this->text = str_replace(["<p><lb/>", "<p/>"], ["<p>", ""], $this->text);

			//clean spaces after <p>
			$this->text = preg_replace("/<p>\s+/", "<p>", $this->text);
			
			//add our application tags
			$app = '</application>' . "\n" . '<application ident="MHS-WETVAC" version="' . \MHS\Env::VERSION . '"><label>MHS-WETVAC</label></application>';
			$this->text = str_replace("</application>", $app, $this->text);
		}




		private function ajaxError($msg){
			$this->response['errors'] = $msg;
			$this->ajaxResponse();
			return;
		}




		public function ajaxResponse(){

			$output = json_encode($this->response);
			header('Content-Type: application/json');
			print $output;

		}





	} //class
