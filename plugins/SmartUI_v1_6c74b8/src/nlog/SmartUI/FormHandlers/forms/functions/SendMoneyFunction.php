<?php

namespace nlog\SmartUI\FormHandlers\forms\functions;

use nlog\SmartUI\FormHandlers\SmartUIForm;
use nlog\SmartUI\SmartUI;
use pocketmine\Player;
use nlog\SmartUI\FormHandlers\NeedPluginInterface;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use onebone\economyapi\EconomyAPI;
use pocketmine\utils\Config;

class SendMoneyFunction extends SmartUIForm implements NeedPluginInterface{
	
	public static function getName(): string{
		return "Отправить деньги";
	}
	
	public static function getIdentifyName(): string{
		return "sendmoney";
	}
	
	public function CompatibilityWithPlugin(): bool {
		return class_exists(EconomyAPI::class, true);
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
		$json['title'] = "§6- Отправить деньги";
		$json['content'] = [];
		$json['content'][] = ["type" => "input", "text" => "Укажите получателя.", "placeholder" => "Введите ник..."];
		$json['content'][] = ["type" => "input", "text" => "Введите сумму для отправки", "placeholder" => "Введите сумму..."];
		
		return json_encode($json);
	}
	
	public function handleRecieve(Player $player, $result) {
		if ($result === null) {
			return;
		}
		$name = trim($result[0]);
		$money = trim($result[1]);
		
		$economy = EconomyAPI::getInstance();
		if (!$economy->accountExists($name)) {
			$player->sendMessage(SmartUI::$prefix . "Игрок {$name} никогда не обращался к серверу.");
			return;
		}
		if (!is_numeric($money) || $money < 1) {
			$player->sendMessage(SmartUI::$prefix . "{$money} - не является целым числом.");
			return;
		}
		$money = floor($money);
		if ($economy->myMoney($player) < $money) {
			$player->sendMessage(SmartUI::$prefix . "У Вас мало денег..");
			return;
		}
		$this->sendMoneyLogger($player, $money, $name);
		$orgin = $economy->myMoney($player);
		$economy->reduceMoney($player, $money);
		$economy->addMoney($name, $money);
		$player->sendMessage(SmartUI::$prefix . "Вы отправили {$money} монет. У Вас осталось: {$orgin} монет.");
		if ($recieve = $this->owner->getServer()->getPlayerExact($name) instanceof Player) {
			$recieve->sendMessage(SmartUI::$prefix . "Игрок {$player->getName()} отправил Вам {$money} монет");
		}
	}
	
	public function sendMoneyLogger(Player $player, int $money, string $recipments) {
		$recipments = strtolower($recipments);
		@mkdir($this->owner->getDataFolder() . "money/", 0777, true);
		$conf = new Config($this->owner->getDataFolder() . "money/" . $recipments . ".json", Config::JSON);
		$all = $conf->getAll();
		$all = array_values($all);
		$all[] = ['name' => $player->getName(), 'time' => time(), 'money' => $money];
		$conf->setAll($all);
		$conf->save();
	}
	
}