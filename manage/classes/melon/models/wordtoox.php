<?php

/* 
 */

	namespace Melon\Models;

	
	
	
	
	class WordToOx {
	
		
		
		const MIME = "application/octet-stream";
		
		
	
		function __construct($server = "http://localhost:8080", $querystring = false){
			
			$this->server = $server;
			
			if(false === $querystring) {
				$this->querystring = "/ege-webservice//Conversions/docx%3Aapplication%3Avnd.openxmlformats-officedocument.wordprocessingml.document/TEI%3Atext%3Axml/";
			} else {
				$this->querystring = $querystring;
			}
			
		}
		
		
		
		
		
		public function setFile($filename){
			
			//must be docx
			if(mime_content_type($filename) != $this::MIME) {
				return $error;			
			}

			$this->filename = $filename;
			
			$parts = pathinfo($this->filename);
			
			$this->outputFilename = $parts['dirname'] . "/" . $parts['filename'] . ".xml";

			return true;
		}
		
		
			
		
		
		
		public function process(){

			$this->buildURL();

			return $this->curl_me();
			
		}
		
		
		

		private function buildURL(){
			$this->url = $this->server . $this->querystring;
		}
		
		


		private function curl_me() {

			// create a new curl resource
			$ch = curl_init();

			//File to save the contents to
			$fp = fopen ($this->outputFilename, 'w+');
			
			curl_setopt($ch, CURLOPT_VERBOSE, 0);  
			curl_setopt($ch, CURLOPT_URL, $this->url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch,CURLOPT_POSTFIELDS,
				array(
					"file[0]" => new \cURLFile($this->filename)
				)
			);
					//	  'upload' => '@' . realpath($this->filename)


//			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			//Pass our file handle to cURL.
//			curl_setopt($ch, CURLOPT_FILE, $fp);
 

			$data = curl_exec($ch);

			fwrite($fp, $data);
			
			// close curl resource, and free up system resources
			curl_close($ch);
			
			fclose($fp);

			return $data;
		}
		
		
		
		
		
		private function error($msg){
			
			$Env = \MHS\Env::getInstance();
			$Env->Messenger->error(new \Exception($msg));
			
			return false;
		}
	
		
	} //class