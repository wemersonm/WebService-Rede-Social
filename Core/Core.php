<?php 
	namespace Core;
	class Core{
		/*
		 Estrutura padrão que faz o carregamento do controler de forma dinamica.
		 Ao acessar uma pagina www.meusite.com/produto
		 Está acessando o produtoController.php <=> Dentro do produtoController e criado funções
		 que e chamado de ACTION: ex. abrir() <=> que e reponsavel por abrir um produto.
		 E tem o parametro, que o i ID do produto.
		 www.meusite.com/produto/abrir/4 
		 => O controller vai ser o produto => produtoController 
		 => O abrir vai ser a ACTION que e a função que será executada
		 => 4 e o parametro <=> id do produto
		 Então nessa url vai ser acessado a pagina de produtos que acessar um produto então =>
		 abrir um produto do id 4
		 1=controller
		 2=action
		 3,4,5 .... são os parametros 

		
		*/
		public function run(){
		
		$url = '/';
		if(isset($_GET['url'])){ //colocando '/' na frente da url == /produto/abrir/54
			$url .= $_GET['url'];
		}

		$url = $this->checkRoutes($url);

		$params = array();
		if(!empty($url) && $url != '/'){
			$url = explode("/",$url);
			array_shift($url);


			$currentController = $url[0]."Controller";
			array_shift($url);


			if(isset($url[0]) && !empty($url[0])){
				$currentAction = $url[0];
				array_shift($url);
			}else{
				$currentAction = 'index';
			}

			if(count($url) > 0){
				$params = $url;
			}
			
		}else{
			$currentController = 'HomeController';
			$currentAction = 'index'; 
		}

   		$currentController = ucfirst($currentController);
   		$prefix = '\Controllers\\';

		if(!file_exists('Controllers/'.$currentController.'.php') || !method_exists($prefix.$currentController, $currentAction)){
			$currentController = $prefix.'NotFoundController';
			$currentAction = 'index';
		}

		$newCurrentController = $prefix.$currentController;
		// instanciar uma variavel == instanciar o conteudo dela que vai ter o nome do controller
		$c = new $newCurrentController();
		//rodar a action, que esta acessando. Não pode acessar direto igual o controller pq nao vai poder passar parametro
		call_user_func_array(array($c,$currentAction), $params);
		// esse metodo  vai executar a classe C e chamar o metodo action
		// e enviar os parametros se precisar, se nao ele envia um array vazio


		}

		public function checkRoutes($url){
			global $routers;

			foreach ($routers as $pt => $newurl) {
				$pattern = preg_replace('(\{[a-z0-9]{1,}\})', '([a-z0-9-]{1,})', $pt);
				// Faz o match da URL 
				if (preg_match('#^('.$pattern.')*$#i', $url, $matches) === 1){
					array_shift($matches);
					array_shift($matches); 
					// Pega todos os argumentos para associar
					$itens = array();
					if (preg_match_all('(\{[a-z0-9]{1,}\})', $pt, $m)){	
						$itens = preg_replace('(\{|\})', '', $m[0]);
					}
					// Faz a associação
					$arg = array();			
					foreach($matches as $key => $match) 
					{ 
						$arg[$itens[$key]] = $match;

					}
					// Monta a nova url
					foreach ($arg as $argkey=> $argvalue) 
					{ 
						$newurl= str_replace(':'.$argkey, $argvalue, $newurl);
					} 
					$url = $newurl;
					break;
				} 
					
			}
			return $url;

		}






	}

 ?>

