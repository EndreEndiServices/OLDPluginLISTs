<?php

namespace nlog\SmartUI\FormHandlers\forms\functions;

use nlog\SmartUI\FormHandlers\FormManager;
use nlog\SmartUI\FormHandlers\SmartUIForm;
use nlog\SmartUI\SmartUI;
use pocketmine\Player;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use onebone\economyapi\EconomyAPI;

class SpeakerFunction extends SmartUIForm{
	
	/** @var int */
	private $limitStrlen;
	
	/** @var int */
	private $money;
	
	public function __construct(SmartUI $owner, FormManager $formManager, int $formId) {
		parent::__construct($owner, $formManager, $formId);
		
		$this->limitStrlen = intval($this->owner->getSettings()->getSetting(self::getIdentifyName(), 'limit-message')) ?? 50;
		$this->limitStrlen = $this->limitStrlen < 1 ? 50 : $this->limitStrlen;
		$this->money = intval($this->owner->getSettings()->getSetting(self::getIdentifyName(), 'need-money')) ?? 1000;
		$this->money = $this->money < 1 ? 1000 : $this->money;
	}
	
	public static function getName(): string{
		return "Сообщение всем";
	}
	
	public static function getIdentifyName(): string{
		return "speaker";
	}
	
	public function sendPacket(Player $player) {
		$pk = new ModalFormRequestPacket();
		$pk->formData = $this->getFormData($player);
		$pk->formId = $this->formId;
		
		$player->dataPacket($pk);
	}
	
	protected function getFormData(Player $player) {
		$json = [];
		$json['type'] = 'custom_form';
		$json['title'] = "§6- Сообщение всем";
		$json['content'] = [];
		$json['content'][] = ["type" => "label", "text" => "Вы можете ввести {$this->limitStrlen} символов.\nЧтоб написать всем вам нужно {$this->money} за одно сообщение."];
		$json['content'][] = ["type" => "input", "text" => "Введите свой контент, чтобы написать всем.", "placeholder" => "Введите ваше сообщение..."];
		
		return json_encode($json);
	}
	
	public function handleRecieve(Player $player, $result) {
		if ($result === null) {
			return;
		}
		$message = trim($result[1]);
		if ($message === "") {
			$player->sendMessage(SmartUI::$prefix . "Вы ничего не написали.");
			return;
		}
		if (mb_strlen($message, 'utf8') > $this->limitStrlen) {
			$player->sendMessage(SmartUI::$prefix . "Вы превысили лимит символов, лимит {$this->limitStrlen} символов");
			return;
		}
		if (EconomyAPI::getInstance()->myMoney($player) < $this->money) {
			$player->sendMessage(SmartUI::$prefix . "Вам не хватает монет");
			return;
		}
		EconomyAPI::getInstance()->reduceMoney($player, $this->money);
		$this->owner->getServer()->broadcastMessage("\n§c§l[СООБЩЕНИЯ] §7{$player->getName()} > {$message}");
	}
	
}