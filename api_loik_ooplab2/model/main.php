<?php
/**
 * User Controller
 *
 * @author Serhii Shkrabak
 * @global object $CORE->model
 * @package Model\Main
 */
namespace Model;
class Main
{
	use \Library\Shared;

	public function formsubmitAmbassador(array $data):?array {
		// Тут модель повинна бути допрацьована, щоб використовувати бази даних, тощо

		$credFile = ROOT . 'model/config/credentials.php'; // файл зі встановленими ключем та id отримувача повідомлень
		if (file_exists($credFile)) {
			include $credFile;
		}

		$key = '-'; // Ключ API телеграм

		if (empty($key)) { // ключа нет
			throw new \Exception("Where is your key?");
		}

		$result = null;
		$chat = 689085700;
		$text = "Нова заявка в *Цифрові Амбасадори*:\n" . $data['firstname'] . ' '. $data['secondname']. ', '. $data['position'] . "\n*Зв'язок*: " . $data['phone'];
		$text = urlencode($text);
		$answer = file_get_contents("https://api.telegram.org/bot$key/sendMessage?parse_mode=markdown&chat_id=$chat&text=$text");
		$answer = json_decode($answer, true);
		$result = ['message' => $answer['result']];
		return $result;
	}

	public function __construct() {

	}
}