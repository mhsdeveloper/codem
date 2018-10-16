<?php

	/*
	 */

	namespace Melon\Controllers;



	class Controller {

		protected $response = [];

		protected function ajaxError($msg){
			$this->response['errors'] = $msg;
			$this->ajaxResponse();
			return;
		}

		protected function ajaxResponse(){
			$output = json_encode($this->response);
			header('Content-Type: application/json');
			print $output;
		}

		}


