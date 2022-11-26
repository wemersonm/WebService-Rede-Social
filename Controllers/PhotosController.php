<?php 
	namespace Controllers;

	use \Core\Controller;
	use \Models\Users;
	use \Models\Photos;

	class PhotosController extends Controller{
		
		public function index()
			{
			
			}

		public function random(){
				$array = array('error'=>'','logged'=>false);

				$method = $this->getMethod();
				$data = $this->getRequestData();

				$u = new Users();
				$p = new Photos();

				if(!empty($data['jwt']) && $u->validateJwt($data['jwt'])){
					$array['logged'] = true;

					
				if($method =='GET'){


					$per_page=10;

					if(!empty($data['per_page'])){
						$per_page=intval($data['per_page']);
					}
					$excludes=array();

					if(!empty($data['excludes'])){
						$excludes=explode(',',$data['excludes']);
					}
					$array['data'] = $p->getRandomPhotos($per_page,$excludes);

				}else{
					$array['error'] = "Metodo ".$method." não disponivel";
				}

				}else{
					$array['error'] = 'Acesso negado';
				}


				$this->returnJson($array);

			}


		public function view($id_photo){
				$array = array('error'=>'','logged'=>false);

				$method = $this->getMethod();
				$data = $this->getRequestData();

				$u = new Users();
				$p = new Photos();

				if(!empty($data['jwt']) && $u->validateJwt($data['jwt'])){
					$array['logged'] = true;

				switch ($method) {
					case 'GET':
							$array['data'] = $p->getPhoto($id_photo);
						break;
					case 'DELETE':
							$array['error'] = $p->deletePhoto($id_photo,$u->getId());
						break;
				
					default:
						$array['error'] = "Metodo ".$method. " não disponivel";
						break;
				}

				}else{
					$array['error'] = 'Acesso negado';
				}


				$this->returnJson($array);

			}

		public function comment($id_photo){
				$array = array('error'=>'','logged'=>false);

				$method = $this->getMethod();
				$data = $this->getRequestData();

				$u = new Users();
				$p = new Photos();

				if(!empty($data['jwt']) && $u->validateJwt($data['jwt'])){
					$array['logged'] = true;

				switch ($method) {
					case 'POST':
							if(!empty($data['txt'])){
								$array['error'] = $p->addComment($id_photo,$u->getId(),$data['txt']);
							}else{
								$array['error'] = "Comentario Vazio";
							}
						break;
					default:
						$array['error'] = "Metodo ".$method. " não disponivel";
						break;
				}

				}else{
					$array['error'] = 'Acesso negado';
				}


				$this->returnJson($array);


			}

		public function delete_comment($id_comment){
				$array = array('error'=>'','logged'=>false);

				$method = $this->getMethod();
				$data = $this->getRequestData();

				$u = new Users();
				$p = new Photos();

				if(!empty($data['jwt']) && $u->validateJwt($data['jwt'])){
					$array['logged'] = true;

				switch ($method) {
					case 'DELETE':
							$array['error'] = $p->deleteComment($id_comment,$u->getId());
						break;
					default:
						$array['error'] = "Metodo ".$method. " não disponivel";
						break;
				}

				}else{
					$array['error'] = 'Acesso negado';
				}


				$this->returnJson($array);

			}

		public function like($id_photo){
				$array = array('error'=>'','logged'=>false);

				$method = $this->getMethod();
				$data = $this->getRequestData();

				$u = new Users();
				$p = new Photos();

				if(!empty($data['jwt']) && $u->validateJwt($data['jwt'])){
					$array['logged'] = true;

				switch ($method) {
					case 'POST':
							$array['error'] = $p->like($id_photo,$u->getId());
						break;
					case 'DELETE':
							$array['error'] = $p->deslike($id_photo,$u->getId());
						break;
				
					default:
						$array['error'] = "Metodo ".$method. " não disponivel";
						break;
				}

				}else{
					$array['error'] = 'Acesso negado';
				}


				$this->returnJson($array);

			}

}