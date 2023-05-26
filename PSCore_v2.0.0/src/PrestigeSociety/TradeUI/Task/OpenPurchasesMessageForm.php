<?php

namespace PrestigeSociety\TradeUI\Task;

use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\Utils\RandomUtils;
use PrestigeSociety\TradeUI\PrestigeSocietyTradeUI;
use PrestigeSociety\UIForms\SimpleForm;

class OpenPurchasesMessageForm extends PluginTask {
	/** @var string */
	public $message;
	/** @var Player */
	public $player;

	/**
	 *
	 * OpenPurchasesMessageForm constructor.
	 *
	 * @param PrestigeSocietyTradeUI $loader
	 * @param string $message
	 * @param Player $player
	 *
	 */
	public function __construct(PrestigeSocietyTradeUI $loader, string $message, Player $player){
		parent::__construct($loader->core);
		$this->message = $message;
		$this->player = $player;
	}

	/**
	 *
	 * Actions to execute when run
	 *
	 * @param int $currentTick
	 *
	 * @return void
	 *
	 */
	public function onRun(int $currentTick){
		$simpleForm = new SimpleForm();
		$simpleForm->setId(0);
		$simpleForm->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lPEOPLE BOUGHT FROM YOU&r&k&e|"));
		$simpleForm->setContent($this->message);
		$simpleForm->setButton("Okay");
		$simpleForm->send($this->player);
	}
}