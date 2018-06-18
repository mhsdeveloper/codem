<?php

/* 
 */

	namespace Melon\Models;

	
	
	
	
	class WordToOx {
	
		
		private $idRoot = "NOID";
		
		
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
			
			$this->filename = $filename;
			
			$parts = pathinfo($this->filename);
			
			if($parts['extension'] != "docx") {
				$this->error("Please upload a MS Word file with a docx extensions");
				return false;
			}
			
			
			$this->outputFilenameOnly = $parts['filename'] . ".xml";
			
			$this->idRoot = $parts['filename'];
			
			$this->outputFilename = $parts['dirname'] . "/" . $this->outputFilenameOnly;

			return true;
		}
		
		
		public function getFullOutputPath(){
			return $this->outputFilename;
		}
		
		
		public function getFilenameOnly(){
			return $this->outputFilenameOnly;
		}
			
		
		public function getIdRoot(){
			return $this->idRoot;
		}
		
		
		
		public function process(){

			$this->buildURL();

			$output = $this->curl_me();
			
			//let's check for xml content
			if(strpos($output, '<TEI xmlns="http://www.tei-c.org/ns/1.0"><teiHeader>') !== false){
				return true;
			}
			
			//bad Oxgarage output
			
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

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

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