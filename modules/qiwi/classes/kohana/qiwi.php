<?php defined('SYSPATH') or die('No direct script access.');

/**
 * @example example/example.php
 * @see http://ishop.qiwi.ru
 * @see https://ishop.qiwi.ru/docs/OnlineStoresProtocols_SOAP_EN.pdf
 */
class Kohana_QIWI {

	/**
	 * Shop login (ID)
	 * @var integer
	 */
	public $login;

	/**
	 * Shop password
	 * @var string
	 */
	public $password;


	public function __construct() {
		if (! extension_loaded('soap')) {
			throw new Kohana_Exception('SOAP::You must have SOAP enabled in order to use this extension.');
		}

		$config = Kohana::$config->load('qiwi');
		$this->login = $config->login;
		$this->password = $config->password;
	}

	static public function factory() {
		return new QIWI();
	}

	/**
	 * Создание счета
	 *
	 * @param string $phone 	phone user
	 * @param float  $amount 	amount of bill
	 * @param string $txn		unique bill ID
	 * @param string $comment	comment to the bill displayed to the user
	 * @param integer $alarm
	 * @param boolean $create	flag to create a new user (if he’s not registered in the system yet)
	 * @throws Kohana_Exception
	 * @return number
	 */
	public function createBill($phone, $amount, $txn, $comment, $alarm=0, $create=true) {
		$service = $this->setService();

		$params = new createBill();
		$params->login = $this->login; // логин
		$params->password = $this->password; // пароль
		$params->user = $phone; // пользователь, которому выставляется счет
		$params->amount = $amount; // сумма
		$params->comment = $comment; // комментарий
		$params->txn = $txn; // номер заказа
		$params->lifetime = date('d.m.Y H:i:s', strtotime('+1 day ago')); // время жизни (если пусто, используется по умолчанию 30 дней)

		// уведомлять пользователя о выставленном счете (0 - нет, 1 - послать СМС, 2 - сделать звонок)
		// уведомления платные для магазина, доступны только магазинам, зарегистрированным по схеме "Именной кошелёк"
		$params->alarm = $alarm;

		// выставлять счет незарегистрированному пользователю
		// false - возвращать ошибку в случае, если пользователь не зарегистрирован
		// true - выставлять счет всегда
		$params->create = $create;

		return $service->createBill($params)->createBillResult;
	}


	/**
	 * Отмена счета
	 *
	 * @param string $txn – unique bill ID
	 * @throws Kohana_Exception
	 * @return number
	 */
	public function cancelBill($txn) {
		$service = $this->setService();
		$params = new cancelBill();
		$params->login = $this->login;
		$params->password = $this->password;
		$params->txn = $txn;
		return $service->cancelBill($params)->cancelBillResult;
	}

	/**
	 * Проверка состояния и получение информации о счете
	 *
	 * @param string $txn – unique bill ID
	 * @throws Kohana_Exception
	 * @return object
	 */
	public function checkBill($txn) {
		$service = $this->setService();
		$params = new checkBill();
		$params->login = $this->login;
		$params->password = $this->password;
		$params->txn = $txn;
		return $service->checkBill($params);
	}

	/**
	 * Оповещение об изменении статуса счета. Вызывается при оплате/отмене счета.
	 */
	public function updateBill() {
		$server = $this->setServer();
		exit(); // SOAP ответ должен отдаватьса без http headers, это происход по причине что метод вызываем в контроллере
	}

	public function update($param) {
		// логика приложения
		$f = fopen('phpdump.txt', 'a+');
		fwrite($f, $param->login);
		fwrite($f, ', ');
		fwrite($f, $param->password);
		fwrite($f, ', ');
		fwrite($f, $param->txn);
		fwrite($f, ', ');
		fwrite($f, $param->status);
		fwrite($f, ', ');
		fwrite($f, $this->checkBill($param->txn)->status);
		fwrite($f, "\n");
		fclose($f);
		return 0;
	}

	public function check_signature($param) {
		$is_login    = ( $param->login == $this->login );
		$is_password = ( $param->password ==  $this->signature($param) );
		return ($is_login and $is_password);
	}

	private function signature($param) {
		return strtoupper(md5($param->txn + strtoupper(md5($this->password))));
	}

	private function setService() {
		include_once Kohana::find_file('vendor', 'IShopServerWSService');

		$wsdl = Kohana::find_file('vendor', 'IShopServerWS', 'wsdl');
		$service = new IShopServerWSService($wsdl, array(
			'location' => 'https://ishop.qiwi.ru/services/ishop',
			'trace' => TRUE
		));
		return $service;
	}

	private function setServer() {
		include_once Kohana::find_file('vendor', 'IShopServer');

		$wsdl = Kohana::find_file('vendor', 'IShopClientWS', 'wsdl');
		$server = new SoapServer($wsdl, array(
			'classmap' => array(
				'tns:updateBill' => 'IShopParam',
				'tns:updateBillResponse' => 'IShopResponse'
			)
		));
		$server->setClass('IShopServer');
		$server->handle();
	}
}