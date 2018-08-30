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


			$T = new \Melon\Models\OxToTei();

			$text = file_get_contents($fullpath);

file_put_contents($fullpath . "ox.xml", $text);
			

			$T->setIdRoot($idRoot);

			$T->text($text);

			$T->prepOxFile();

			$T->separateDocParts();

			//if we have chunks, that is, more than one document
			if(strpos($text, '{{DOC}}')) {
				$T->chunkByChunk();
				$T->rejoinParts();
			}

			//single doc
			else {
				$T->processDocument();
				$T->rejoinParts();
			}

			//save
			file_put_contents($fullpath, $T->text());

			$this->response["message"] = "Ready to download TEI";
			$this->response['status'] = "download";
			$this->response['filename'] = str_replace($_SERVER['DOCUMENT_ROOT'], "", $fullpath);

			$this->ajaxResponse();
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
