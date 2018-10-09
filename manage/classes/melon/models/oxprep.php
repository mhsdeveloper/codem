<?php


	namespace Melon\Models;


	class OxPrep {

		public $errorMsg = "";
		
		protected  $xslt; //DOMDocument for xslt file
		protected $doc; //DOMDocument for xml file
		


		public function runTransform($text){
			
			$this->doc = new \DOMDocument();
			if(false == $this->doc->loadXML($text)){
				
				$this->errorMsg = "Error loading the OX text as XSLT";
				return false;
			}
			
			if(false == $this->loadXSLT(\MHS\Env::PRE_WET_XSLT)) {
				$this->errorMsg = "Error loading the PRE_WET_XSLT";
				return false;
			}
			
			if(false == $this->transform()){
				
			}
		}
	
		
		
		public function loadXSLT($fullpath){
			$this->xslt = new \DOMDocument;

			if($this->xslt->load($fullpath)) return true;
			return false;
		}
		
		
		
		/* transform()
		 *
		 * $xsltfile	:	string:	fullpath to xslt transform
		 * $params		:	array: key is parameter name, value is value is value ...
		 *	returns true on success, false on any number of things that could go wrong!
		 */
		public function transform($params = false){

			$this->transformer = new \XSLTProcessor;
		
			$this->transformer->importStyleSheet($this->xslt);

			if(is_array($params)){
				foreach($params as $param => $value) $this->transformer->setParameter('', $param, $value);
			}
			$this->output = $this->transformer->transformToXML($this->doc);
//error handling needed				
			if($this->output === false) {
				$this->errorMsg = "There was an error with the XSLT transformation. ";
				return false;
			}
			else return true;
		}
		
	}