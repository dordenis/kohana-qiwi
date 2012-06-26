<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Qiwi extends Controller {

	public function action_update() {
		QIWI::factory()->updateBill();
	}


}