<?php
/**
 * User Controller
 *
 * @author Serhii Shkrabak
 * @global object $CORE
 * @package Controller\Main
 */
namespace Controller;
class Main
{
	use \Library\Shared;

	private $model;

	public function exec():?array {
		$result = null;
		$url = $this->getVar('REQUEST_URI', 'e');
		$path = explode('/', $url);

		if (isset($path[2]) && !strpos($path[1], '.')) { // Disallow directory changing
			$file = ROOT . 'model/config/methods/' . $path[1] . '.php';
			if (file_exists($file)) {
				include $file;
				if (isset($methods[$path[2]])) {
					$details = $methods[$path[2]];
					$request = [];
					foreach ($details['params'] as $param) {
						$var = $this->getVar($param['name'], $param['source']); // отримання даних

						if (isset($var)) {
							$patternFile = ROOT . 'model/config/patterns.php';
							include $patternFile;

							if (isset($param['pattern'])) { // якщо є шаблон
								if (preg_match($patterns[$param['pattern']]['regex'], $var)) { 
									if (isset($patterns[$param['pattern']]['callback'])) {
										$var = preg_replace_callback($patterns[$param['pattern']]['replacement'], $patterns[$param['pattern']]['callback'], $var);
									}

									$request[$param['name']] = $var; // додати до параметрів запиту
								} else {
									$state = 2; // встановлення коду помилки, для відловлення однотипних помилок
									if (!isset($result)) {
										$result = [
											'state' => $state,
											'data' => [],
										];
									}

									if ($result['state'] === $state) { // якщо однотипна помилка — додати до списку
										array_push($result['data'], $param['name']);
									}
								}
							} else {
								$request[$param['name']] = $var; 
							}
						} else if ($param['required'] === false) { // якщо параметр не обов'язковий
							$request[$param['name']] = $param['default'] ?: ''; // встановлюється ім'я за замовчуванням
						} else {
							$state = 1;
							if (!isset($result)) {
								$result = [
									'state' => $state,
									'data' => [],
								];
							}
							// додавання до списку помилок
							if ($result['state'] === $state) {
								array_push($result['data'], $param['name']);
							}
						}
					}

					/* Якщо записано код помилки */
					if (isset($result['state'])) {
						return $result;
					}

					if (method_exists($this->model, $path[1] . $path[2])) { // якщо метод реалізовано у контролері
						$method = [$this->model, $path[1] . $path[2]];
						$result = $method($request); // відправити запит
					} else { //  непідтримуваний метод
						$result = [
							'state' => 5
						];
					}
				}
				else { // непідтримуваний метод
					$result = [
						'state' => 5
					];
				}
			}
		}

		return $result;
	}

	public function __construct() {
		// CORS configuration
		$origin = $this -> getVar('HTTP_ORIGIN', 'e');
		$front = $this->getVar('FRONT', 'e');

		foreach ( [$front] as $allowed )
			if ( $origin == "https://$allowed") {
				header( "Access-Control-Allow-Origin: $origin" );
				header( 'Access-Control-Allow-Credentials: true' );
			}
		$this->model = new \Model\Main;
	}
}