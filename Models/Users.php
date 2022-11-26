<?php 
	namespace Models;
	
	use \Core\Model;
	use Models\Jwt;
	use \Models\Photos;
	class Users extends Model{

		private $id_user;

		public function checkCredentials($email,$senha){
			$stmt = $this->db->prepare("SELECT id,senha FROM users WHERE email=:email");
			$stmt->bindValue(":email",$email);
			$stmt->execute();

			if($stmt->rowCount() > 0){
				$currentData = $stmt->fetch();
				
				if(password_verify($senha,$currentData['senha'])){
					$this->id_user = $currentData['id'];
					return true;
				}
			}
			return false;
		}
		public function getId(){
			return $this->id_user;
		}

		public function createJWT(){
			$jwt = new Jwt();
			return $jwt->create(array('id_user'=> $this->id_user));
		}
		
		public function emailExists($email){

			$stmt = $this->db->prepare("SELECT * FROM users WHERE email=?");
			$stmt->execute(array($email));

			if($stmt->rowCount() > 0){
				return true;

			}
			return false;
		}

	   public function create($nome,$email,$senha){
	   		if($this->emailExists($email)){
	   			return false;
	   		}
   			$hash = password_hash($senha, PASSWORD_DEFAULT);
   			$stmt = $this->db->prepare("INSERT INTO users (nome,email,senha) VALUES (?,?,?)");
   			$stmt->execute(array($nome,$email,$hash));
			$this->id_user = $this->db->lastInsertId();
   			return true;	

	   }

	   public function validateJwt($token){
	   	$jwt = new Jwt();
	   	$info = $jwt->validate($token);

	   	if(isset($info->id_user)){
	   		$this->id_user = $info->id_user;
	   		return true;
	   	}
	   	return false;

	   }

	   public function getInfo($id){
	   	$array = array();
	   	$stmt = $this->db->prepare("SELECT id,nome,email,avatar FROM users WHERE id=?");
	   	$stmt->execute(array($id));
	   	if($stmt->rowCount() > 0){
	   		$array = $stmt->fetch(\PDO::FETCH_ASSOC);  
	   		$photos = new Photos();
	   		if(!empty($array['avatar'])){
	   			$array['avatar'] = BASE_URL.'media/avatar/'.$array['avatar'];
	   		}else
	   		{
	   			$array['avatar'] = BASE_URL.'media/avatar/default.jpg';
	   		}

	   		$array['following'] = $this->getFollowingCount($id);
	   		$array['followers'] = $this->getFollowersCount($id);
	   		$array['photos_count'] = $photos->getPhotosCount($id);

	   	}
	   	return $array;
	   }

	   public function getFollowingCount($id){

	   	$stmt = $this->db->prepare("SELECT COUNT(*) as c FROM users_following WHERE id_user_active=?");
	   	$stmt->execute(array($id));
	   	$info = $stmt->fetch();

	   	return $info['c'];	

	   }

	   public function getFollowersCount($id){

	   	$stmt = $this->db->prepare("SELECT COUNT(*) as c FROM users_following WHERE id_user_passive=?");
	   	$stmt->execute(array($id));
	   	$info = $stmt->fetch();

	   	return $info['c'];	

	   }

	   public function editInfo($id,$data){

	   	if($id == $this->getId()){
	   		$toChange = array();
	   		if(!empty($data['nome'])){
	   			$toChange['nome'] = $data['nome'];
	   		}
	   		if(!empty($data['email'])){
	   			if(filter_var($data['email'],FILTER_VALIDATE_EMAIL) 
	   			&& !$this->emailExists($data['email'])){
	   				$toChange['email'] = $data['email'];
	   			}else{
	   				return "Email informado já existente";
	   			}
	   		}
	   			
	   		

	   		if(!empty($data['senha'])){
	   			$toChange['senha'] = password_hash($data['senha'],PASSWORD_DEFAULT);
	   		}

	   		if(count($toChange) > 0){

	   			$fields= array();

	   			foreach ($toChange as $key => $value) {
	   				$fields[]=$key.'=:'.$key; 
	   			}

	   			$stmt = $this->db->prepare("UPDATE users SET ".implode(',',$fields)." WHERE id=:id");
	   			$stmt->bindValue(":id",$id);
	   			foreach ($toChange as $key => $value) {
	   				$stmt->bindValue(":".$key,$value);
	   			}
	   			$stmt->execute();
	   			return '';



	   		}else{
	   			return "Preencha os dados corretamente";
	   		}


	   	}else{
	   		return "Não e permitido editar outro usuario";
	   	}
	   }
	   public function delete($id){

	   	 	if($id == $this->getId()){
	   	 		
	   	 		$p = new Photos();
	   	 		$p->deleteAll($id);

	   	 		$stmt = $this->db->prepare("DELETE FROM users_following WHERE id_user_active=:id OR id_user_passive=:id");
				$stmt->bindValue(":id",$id);
				$stmt->execute();
				$stmt = $this->db->prepare("DELETE FROM users WHERE  id=:id");
				$stmt->bindValue(":id",$id);
				$stmt->execute();
				return '';
		   	}else{
		   		return "Não e permitido excluir outro usuario";
		   	}


	   }

	   public function getFeed($offseat=0,$perPage=10){
	   	
	   	$followingUser = $this->getFollowing($this->getId());
	   	$p = new Photos();

	   	return $p->getFeedCollection($followingUser,$offseat,$perPage);
	   }

	   public function getFollowing($id_user){
	   	$array =array();

	   	$stmt=$this->db->prepare("SELECT id_user_passive FROM users_following WHERE id_user_active = :id_user");
	   	$stmt->bindValue(":id_user",$id_user); 
	   	$stmt->execute();

	   	if($stmt->rowCount() > 0){
	   		$data = $stmt->fetchAll();
	   		foreach ($data as $key => $value) {
	   			$array[] = intval($value['id_user_passive']);
	   		}
	   	}

	   	return $array;
	   }

	   public function follow($id_user){

	   	$stmt= $this->db->prepare("SELECT * FROM users_following WHERE id_user_active = :id_user_active AND
	   		id_user_passive = :id_user_passive");
	   	$stmt->bindValue(":id_user_active",$this->getId());
	   	$stmt->bindValue(":id_user_passive",$id_user);
	   	$stmt->execute();

	   	if($stmt->rowCount() == 0){
		   	$stmt= $this->db->prepare("INSERT INTO users_following (id_user_active,id_user_passive) 
		   		VALUES(:id_user_active,:id_user_passive)");
		   	$stmt->bindValue(":id_user_active",$this->getId());
		   	$stmt->bindValue(":id_user_passive",$id_user);
		   	$stmt->execute();
		   	return false;
	   	}

	   	return false;

	   }

	   public function unfollow($id_user){
	   	$stmt= $this->db->prepare("DELETE FROM 	users_following WHERE id_user_active = :id_user_active AND
	   	id_user_passive = :id_user_passive");
	   	$stmt->bindValue(":id_user_active",$this->getId());
	   	$stmt->bindValue(":id_user_passive",$id_user);
	   	$stmt->execute();
	   }

	}



 ?>