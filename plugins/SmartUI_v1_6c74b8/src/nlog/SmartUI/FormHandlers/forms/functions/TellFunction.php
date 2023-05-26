<?php

namespace nlog\SmartUI\FormHandlers\forms\functions;

use nlog\SmartUI\FormHandlers\FormManager;
use nlog\SmartUI\FormHandlers\SmartUIForm;
use nlog\SmartUI\SmartUI;
use nlog\SmartUI\util\Utils;
use pocketmine\Player;
use nlog\SmartUI\FormHandlers\NeedPluginInterface;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use onebone\economyapi\EconomyAPI;
use pocketmine\utils\Config;

class TellFunction extends SmartUIForm {
	
	public static function getName(): string{
		return "Личные сообщения";
	}
	
	public static function getIdentifyName(): string{
		return "tell";
	}

	/** @var array */
	private $recip;

	public function __construct(SmartUI $owner, FormManager $formManager, int $formId) {
        parent::__construct($owner, $formManager, $formId);
        $this->recip = [];
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
		$json['title'] = "§6- Личные сообщения";
		$json['content'] = [];
		if (isset($this->recip[$player->getName()])) {
            $json['content'][] = ["type" => "input", "text" => "Укажите получателя.", "placeholder" => "Введите ник...", "default" => $this->recip[$player->getName()]];
        }else{
            $json['content'][] = ["type" => "input", "text" => "Укажите получателя.", "placeholder" => "Введите ник..."];
        }
		$json['content'][] = ["type" => "input", "text" => "Введите сообщение для отправки", "placeholder" => "Введите ваше сообщение..."];
		//$json['content'][] = ["type" => "toggle", "text" => "Включить псевдоним", "default" => true];
		
		return json_encode($json);
	}
	
	public function handleRecieve(Player $player, $result) {
		if ($result === null) {
			return;
		}
		$name = trim($result[0]);
		$message = trim($result[1]);
		$nickname = $result[2];

		if (!$this->owner->getServer()->getPlayerExact($name) instanceof Player) {
			$player->sendMessage(SmartUI::$prefix . "Игрок {$name} не обнаружен в сети");
			return;
		}
		if ($message === "") {
			$player->sendMessage(SmartUI::$prefix . "Вы не ввели сообщение.");
			return;
		}
		if (isset($this->recip[$player->getName()])) {
		    unset($this->recip[$player->getName()]);
        }
        if ($nickname) {
            $this->recip[$player->getName()] = $name;
        }
        $player->sendMessage(SmartUI::$prefix . "Вы отправили личное сообщение игроку {$name}");
        $this->owner->getServer()->getPlayerExact($name)->sendMessage("§c[§6{$player->getName()}§c] §7: §f{$message}");
        foreach ($this->owner->getServer()->getOnlinePlayers() as $player) {
            if (strcasecmp($name, $player->getName()) === 0 || strcasecmp($player->getName(), $player->getName()) === 0) {
                continue;
            }
            if ($player->isOp()) {
                $player->sendMessage("§c[§6{$player->getName()} => [{$this->owner->getServer()->getPlayerExact($name)->getName()}§c] §7: §f{$message}"); //TODO: 로그 설정
            }
        }
	}
}