<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Qiwi extends Controller {

	/**
	 * Справочник кодов завершения
	 *
	 *  0 Успех
	 *  13 Сервер занят, повторите запрос позже
	 *  150 Ошибка авторизации (неверный логин/пароль)
	 *  210 Счет не найден
	 *  215 Счет с таким txn уже существует
	 *  241 Сумма слишком мала
	 *  242 Превышена максимальная сумма платежа – 15 000р.
	 *  278 Превышение максимального интервала получения списка счетов
	 *  298 Агента не существует в системе
	 *  300 Неизвестная ошибка
	 *  330 Ошибка шифрования
	 *  370 Превышено максимальное кол-во одновременно выполняемых запросов
	 */

	public function action_create() {
		$phone = '7777777777';
		$amount = '0.01';
		$txn = 77;
		$comment = 'test billd';
		$alarm = 0;

		$rc = QIWI::factory()->createBill($phone, $amount, $txn, $comment, $alarm);
		var_dump($rc);
	}

	public function action_cancel() {
		$txn = 4;
		$rc = QIWI::factory()->cancelBill($txn);
		var_dump($rc);
	}

	/**
	 * Справочник статусов счетов
	 *
	 * 50 Выставлен
	 * 52 Проводится
	 * 60 Оплачен
	 * 150 Отменен (ошибка на терминале)
	 * 151 Отменен (ошибка авторизации: недостаточно средств на балансе, отклонен абонентом при оплате с лицевого счета оператора сотовой связи и т.п.).
	 * 160 Отменен
	 * 161 Отменен (Истекло время)
	 */

	public function action_check() {
		$txn = 4;
		$rc = QIWI::factory()->checkBill($txn);
		var_dump($rc);
	}

	public function action_update() {
		QIWI::factory()->updateBill();
	}


}