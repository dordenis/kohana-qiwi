<?php
/**
 * @package IShopServer
 */
class IShopResponse {
	public $updateBillResult;
}

class IShopParam {
	public $login;
	public $password;
	public $txn;
	public $status;
}

class IShopServer {

	public function updateBill($param) {
		$status = QIWI::factory()->update($param);

		// формируем ответ на уведомление
		// если все операции по обновлению статуса заказа в магазине прошли успешно, отвечаем кодом 0
		// $temp->updateBillResult = 0
		// если произошли временные ошибки (например, недоступность БД), отвечаем ненулевым кодом
		// в этом случае QIWI Кошелёк будет периодически посылать повторные уведомления пока не получит код 0
		// или не пройдет 24 часа
		$responce = new IShopResponse();
		$responce->updateBillResult = $status;
		return $responce;
	}

}