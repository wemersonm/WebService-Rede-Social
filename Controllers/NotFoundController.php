<?php 
	namespace Controllers;
	use \Core\Controller;
	
	class NotFoundController extends controller{

		public function index()
		{
			return $this->returnJson(array());
		}

	}

 ?>