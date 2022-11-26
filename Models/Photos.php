<?php 
	namespace Models;

	use \Core\Model;

	class Photos extends Model{
		
		public function getPhotosCount($id){

	   	$stmt = $this->db->prepare("SELECT COUNT(*) as c FROM photos WHERE id_user=?");
	   	$stmt->execute(array($id));
	   	$info = $stmt->fetch();

	   	return $info['c'];	
		}

		public function getLikeCount($idPhoto){
		$stmt = $this->db->prepare("SELECT COUNT(*) as c FROM photos_likes WHERE id_photo=?");
	   	$stmt->execute(array($idPhoto));
	   	$info = $stmt->fetch();

	   	return $info['c'];	
		}

		public function deleteAll($id_user){

			$stmt = $this->db->prepare("DELETE FROM photos WHERE id_user=?");
			$stmt->execute(array($id_user));

			$stmt = $this->db->prepare("DELETE FROM photos_comments WHERE id_user=?");
			$stmt->execute(array($id_user));

			$stmt = $this->db->prepare("DELETE FROM photos_likes WHERE id_user=?");
			$stmt->execute(array($id_user));


		}

		public function getFeedCollection($ids,$offset,$perPage)
		{
			$array = array();
			$user= new Users();
			if(count($ids) > 0){
					$stmt = $this->db->query("SELECT * FROM photos
					WHERE id_user IN (".implode(',',$ids).")
					ORDER BY id DESC
					LIMIT ".$offset.", ".$perPage);

					if($stmt->rowCount() > 0){
						$array = $stmt->fetchAll(\PDO::FETCH_ASSOC);
						foreach ($array as $key => $value) {

							$user_info = $user->getInfo($value['id_user']);
							$array[$key]['nome'] = $user_info['nome'];
							$array[$key]['avatar'] = $user_info['avatar'];
							$array[$key]['url'] = BASE_URL."media/photos".$value['url'];

							$array[$key]['like_count'] = $this->getLikeCount($value['id']);
							$array[$key]['comments'] = $this->getComments($value['id']);

						}

					}
			}
			return $array;
		}

		public function getComments($idPhoto){
			$array = array();
				
			$stmt = $this->db->prepare("SELECT photos_comments.*, users.nome FROM photos_comments LEFT JOIN
				users ON users.id = photos_comments.id_user	WHERE photos_comments.id_photo=:id");	
			$stmt->bindValue(":id",$idPhoto);
			$stmt->execute();

			if($stmt->rowCount() > 0){
				$array = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			}
			return $array;	
		}


		public function getPhotosFromUser($idUser,$offset,$per_page){
			$array = array();

			$stmt = $this->db->prepare("SELECT * FROM photos WHERE id_user=:id ORDER BY id DESC LIMIT ".$offset.",".$per_page);
			$stmt->bindValue(":id",$idUser);
			$stmt->execute();

			if($stmt->rowCount() > 0){
				$array = $stmt->fetchAll(\PDO::FETCH_ASSOC);

				foreach ($array as $key => $value) {
					$array[$key]['url'] = BASE_URL."media/photos".$value['url'];
					$array[$key]['like_count'] = $this->getLikeCount($value['id']);		
					$array[$key]['comments'] = $this->getComments($value['id']);
				}
			}

			return $array;
		}

		public function getRandomPhotos($per_page,$excludes = array()){
			$array=array();

			foreach ($excludes as $key => $value) {
				$excludes[$key] = intval($value);
			}

			if(count($excludes) > 0){
				$stmt = $this->db->query("SELECT * FROM photos WHERE id NOT IN (".implode(',',$excludes).")
				 ORDER BY RAND() LIMIT ".$per_page);

			}else{
				$stmt = $this->db->query("SELECT * FROM photos ORDER BY RAND() LIMIT ".$per_page);
			}

			if($stmt->rowCount() > 0){
				$array = $stmt->fetchAll(\PDO::FETCH_ASSOC);

				foreach ($array as $key => $value) {
					$array[$key]['url'] = BASE_URL."media/photos".$value['url'];
					$array[$key]['like_count'] = $this->getLikeCount($value['id']);		
					$array[$key]['comments'] = $this->getComments($value['id']);
				}
			}
			return $array;
			}

		public function getPhoto($id_photo){
				$user = new Users();
				$array = array();
				$stmt = $this->db->prepare("SELECT * FROM photos WHERE id=:id");
				$stmt->bindValue(":id",$id_photo);
				$stmt->execute();
				if($stmt->rowCount() > 0){
					$array = $stmt->fetch(\PDO::FETCH_ASSOC);

					$user_info = $user->getInfo($array['id_user']);

					$array['nome'] = $user_info['nome'];
					$array['avatar'] = $user_info['avatar'];
					$array['url'] = BASE_URL."media/photos".$array['url'];

					$array['like_count'] = $this->getLikeCount($array['id']);
					$array['comments'] = $this->getComments($array['id']);


				}
				return $array;

			}

		public function deletePhoto($id_photo, $id_user){

				 $stmt= $this->db->prepare("SELECT id FROM photos WHERE id=:id AND id_user = :id_user");
				 $stmt->bindValue(":id",$id_photo);
				 $stmt->bindValue(":id_user",$id_user);
				 $stmt->execute();

				 if($stmt->rowCount() > 0){

					$stmt = $this->db->prepare("DELETE FROM photos WHERE id=?");
					$stmt->execute(array($id_photo));

					$stmt = $this->db->prepare("DELETE FROM photos_comments WHERE id_photo=?");
					$stmt->execute(array($id_photo));

					$stmt = $this->db->prepare("DELETE FROM photos_likes WHERE id_photo=?");
					$stmt->execute(array($id_photo));

					return '';

				 }else{

				 	return "Esta foto não existe ou não e sua"; 
				 }

			}

		public function addComment($id_photo,$id_user,$txt){
				if(!empty($txt)){
					$stmt = $this->db->prepare("INSERT INTO photos_comments(id_user,id_photo,date_comment,txt)
					VALUES(:id_user,:id_photo,NOW(),:txt)");
					$stmt->bindValue(":id_user",$id_user);
					$stmt->bindValue(":id_photo",$id_photo);
					$stmt->bindValue(":txt",$txt);
					$stmt->execute();
					return '';
				}else{
					return "Comentario Vazio";
				}
				
			}

		public function deleteComment($id_comment,$id_user){
				$stmt= $this->db->prepare("SELECT id FROM photos_comments WHERE id_user = :id_user AND id=:id");
				$stmt->bindValue(":id_user",$id_user);
				$stmt->bindValue(":id",$id_user);
				$stmt->execute();

				if($stmt->rowCount() > 0){
					$stmt = $this->db->prepare("DELETE FROM photos_comments WHERE id=:id");
					$stmt->bindValue(":id",$id_comment);
					$stmt->execute();

					return '';
				}else{
					return 'Esse comentario não e seu';
				}
			}

		public function like($id_photo,$id_user){
				$stmt = $this->db->prepare("SELECT * FROM photos_likes WHERE id_user = :id_user AND id_photo=:id_photo");
				$stmt->bindValue(":id_user",$id_user);
				$stmt->bindValue(":id_photo",$id_photo);
				$stmt->execute();

				if($stmt->rowCount() == 0){
					$stmt = $this->db->prepare("INSERT INTO photos_likes (id_user,id_photo) VALUES(:id_user,:id_photo)");
					$stmt->bindValue(":id_user",$id_user);
					$stmt->bindValue(":id_photo",$id_photo);
					$stmt->execute();
					return '';
				}
				return 'Voce ja deu like nessa foto';
			}

		public function deslike($id_photo,$id_user){
				$stmt = $this->db->prepare("DELETE FROM photos_likes WHERE id_user=:id_user AND id_photo=:id_photo");
				$stmt->bindValue(":id_user",$id_user);
				$stmt->bindValue(":id_photo",$id_photo);
				$stmt->execute();

				return '';
			}

}

 ?>