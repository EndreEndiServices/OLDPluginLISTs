<?php

namespace nlog\SmartUI\FormHandlers\forms\functions;

use nlog\SmartUI\FormHandlers\SmartUIForm;
use nlog\SmartUI\SmartUI;
use pocketmine\Player;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

class RecieveMoneyFunction extends SmartUIForm{
	
	const error_no_recieve = 0;
	const error_crash_file = 1;
	
	public static function getName(): string{
		return "Vizualizarea banilor primiți";
	}
	
	public static function getIdentifyName(): string{
		return "recievemoney";
	}
	
	public function sendPacket(Player $player) {
		$pk = new ModalFormRequestPacket();
		$formData = $this->getFormData($player);
		if ($formData === self::error_no_recieve) {
			$player->sendMessage(SmartUI::$prefix . "Nu ai primit bani.");
		}elseif ($formData === self::error_crash_file) {
			$player->sendMessage(SmartUI::$prefix . "Datele sunt corupte și nu pot afișa jurnalul.");
			@unlink($this->owner->getDataFolder() . "money/" . $player->getName() . ".json");
		}else{
			$pk->formData = $formData;
			$pk->formId = $this->formId;
			
			$player->dataPacket($pk);
		}
	}
	
	protected function getFormData(Player $player) {
		if (!file_exists($this->owner->getDataFolder() . "money/" . $player->getName() . ".json")) {
			return self::error_no_recieve;
		}elseif (!is_array(json_decode(file_get_contents($this->owner->getDataFolder() . "money/" . $player->getName() . ".json"), true))) {
			return self::error_crash_file;
		}
		$str = "";
		$file = json_decode(file_get_contents($this->owner->getDataFolder() . "money/" . $player->getName() . ".json"), true);
		foreach ($file as $index => $info) {
			$date = date("Y-m-d G:i", $info['time']);
			$str .= "[{$date}] {$info['name']} > {$info['money']}";
		}
		if ($str === "") {
			return self::error_crash_file;
		}
		$json = [];
		$json['type'] = 'modal';
		$json['title'] = "- Vizualizarea banilor primiți";
		$json['content'] = $str;
		$json["button1"] = "≫ Înapoi la meniu ≪";
		$json["button2"] = "≫ Închide fereastra ≪";
		
		return json_encode($json);
	}
	
	public function handleRecieve(Player $player, $result) {
		if ($result) {
			$this->FormManager->getListMenuForm()->sendPacket($player);
		}
	}
	
}