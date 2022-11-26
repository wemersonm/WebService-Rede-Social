<?php 
	namespace Controllers;

	use \Core\Controller;
	use \Models\Users;
	use \Models\Photos;

	class UsersController extends Controller{
		public function index()
			{
				echo "Funcionando";
			}
		public function login()
			{
				$u = new Users();
				$array = array('error' => '');
				$method = $this->getMethod();
				$data = $this->getRequestData();
				if($method === 'POST'){
					if(!empty($data['email']) && !empty($data['senha'])){
						if($u->checkCredentials($data['email'],$data['senha'])){
							echo "LOGADO ! ";
							$array['jwt']= $u->createJWT();

						}else{
							$array['error']="Email e/ou senha incorretos"; 
						}
					}else{
						$array['error']="Email e/ou senha não informado"; 
					}
				}else{
					$array['error'] = "Metodo de requisição incompativel"; 
				}
				
				$this->returnJson($array);
			}

		public function new_user(){
				$array = array('error' => '');
				$method = $this->getMethod();
				$data = $this->getRequestData();

				if($method === 'POST'){
					if( !empty($data['nome']) && !empty($data['email']) && !empty($data['senha']) ){
						if(filter_var($data['email'],FILTER_VALIDATE_EMAIL)){
							$u = new Users();
							if($u->create( $data['nome'], $data['email'], $data['senha'] )){
								$array['jwt'] = $u->createJWT();
							}else{
								$array['error']="Email já existente"; 
							}
						}else{
							$array['error']="Email invalido"; 
						}
					}else{
						$array['error']="Preencha todos os campos"; 
					}
				}else{
					$array['error'] = "Metodo de requisição incompativel"; 
				}

				$this->returnJson($array);

			}

		public function view($id){

				$array = array('error'=>'','logged'=>false);

				$method = $this->getMethod();
				$data = $this->getRequestData();

				$u = new Users();
				if(!empty($data['jwt']) && $u->validateJwt($data['jwt'])){
					$array['logged'] = true;

					$array['is_me'] = false;
					if($id == $u->getId()){
						$array['is_me'] = true;
					}

					switch ($method) {
						case 'GET':
								$array['data'] = $u->getInfo($id);
								if(count($array['data']) == 0){
									$array['error'] = "Usuario não existe";
								}
							break;
						case 'PUT':
							$array['error'] = $u->editInfo($id,$data);

							break;
						case 'DELETE':
								$array['error'] = $u->delete($id);
							break;
						default:
							$array['error'] = "Metodo ".$method." não disponivel";
							break;
					}

				}else{
					$array['error'] = 'Acesso negado';
				}


				$this->returnJson($array);
			}

		public function feed(){

				$array = array('error'=>'','logged'=>false);

				$method = $this->getMethod();
				$data = $this->getRequestData();

				$u = new Users();
				if(!empty($data['jwt']) && $u->validateJwt($data['jwt'])){
					$array['logged'] = true;

				if($method =='GET'){

					$offset = 0;
					if(!empty($data['offset'])){
						$offset=intval($data['offset']);
					}	

					$per_page=10;

					if(!empty($data['per_page'])){
						$per_page=intval($data['per_page']);
					}

					$array['data'] = $u->getFeed($offset,$per_page);

				}else{
					$array['error'] = "Metodo ".$method." não disponivel";
				}

				}else{
					$array['error'] = 'Acesso negado';
				}


				$this->returnJson($array);
			}


		public function photos($idUser){
				$array = array('error'=>'','logged'=>false);

				$method = $this->getMethod();
				$data = $this->getRequestData();

				$u = new Users();
				$p = new Photos();

				if(!empty($data['jwt']) && $u->validateJwt($data['jwt'])){
					$array['logged'] = true;

					$array['is_me'] = false;
					if($idUser == $u->getId()){
						$array['is_me'] = true;
					}
				if($method =='GET'){

					$offset = 0;
					if(!empty($data['offset'])){
						$offset=intval($data['offset']);
					}	

					$per_page=10;

					if(!empty($data['per_page'])){
						$per_page=intval($data['per_page']);
					}

					$array['data'] = $p->getPhotosFromUser($idUser,$offset,$per_page);

				}else{
					$array['error'] = "Metodo ".$method." não disponivel";
				}

				}else{
					$array['error'] = 'Acesso negado';
				}


				$this->returnJson($array);

			}
			
		public function follow($id_user){
				$array = array('error'=>'','logged'=>false);

				$method = $this->getMethod();
				$data = $this->getRequestData();

				$u = new Users();
				$p = new Photos();

				if(!empty($data['jwt']) && $u->validateJwt($data['jwt'])){
					$array['logged'] = true;

					switch($method){
						case 'POST':
							$u->follow($id_user);
							break;
						case 'DELETE':	
							$u->unfollow($id_user);
							break;
						default:
							$array['error'] = "Metodo ".$method." não disponivel";
							break;
					}
					
				}else{
					$array['error'] = 'Acesso negado';
				}

				$this->returnJson($array);
	   		
	   			}
	   
	}
	
 ?>