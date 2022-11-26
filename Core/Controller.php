<?php 
	namespace Core;
	class Controller{


		public function getMethod(){
			return $_SERVER['REQUEST_METHOD'];
		}



		public function getRequestData(){
			if($this->getMethod() == "GET"){
				return $_GET;
			}
			elseif($this->getMethod() == "DELETE" || $this->getMethod() == "PUT"){
				parse_str(file_get_contents('php://input'),$data);
				return $data;
				/* parse_str => Separa o input em Key e Valor =>
				 nome=Messi&idade=35
				 em data: $data['nome'] => Messi
				 		  $data['idade'] = 35
				*/
			}
			elseif($this->getMethod() == "POST"){
				$data = json_decode(file_get_contents('php://input'));
				if(is_null($data)){// se nao tiver nada retorna o post
					$data = $_POST;
				}
				return $data;
			}
		}

		public function returnJson($array){
			header("Content-Type: application/json");
			echo json_encode($array);
			exit;
		}


	}


 ?>