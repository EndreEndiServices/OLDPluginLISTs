<?php

namespace nlog\SmartUI\FormHandlers\forms\functions;

use nlog\SmartUI\FormHandlers\FormManager;
use nlog\SmartUI\FormHandlers\SmartUIForm;
use nlog\SmartUI\SmartUI;
use pocketmine\Player;
use nlog\SmartUI\FormHandlers\NeedPluginInterface;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use solo\swarp\SWarp;
use solo\swarp\Warp;
use solo\swarp\WarpException;

class WarpFunction extends SmartUIForm implements NeedPluginInterface{
	
	/** @var array */
	protected $warpList;
	
	public function __construct(SmartUI $owner, FormManager $formManager, int $formId) {
		parent::__construct($owner, $formManager, $formId);
		$this->warpList = [];
	}
	
	public static function getName(): string{
		return "Варпы";
	}
	
	public static function getIdentifyName(): string{
		return "warp";
	}
	
	public function CompatibilityWithPlugin(): bool{
		return class_exists(SWarp::class, true);
	}
	
	public function sendPacket(Player $player) {
		$pk = new ModalFormRequestPacket();
		$pk->formId = $this->formId;
		$pk->formData = $this->getFormData($player);
		
		$player->dataPacket($pk);
	}
	
	protected function getFormData(Player $player) {
		$json = [];
		$json['type'] = 'form';
		$json['title'] = "§6- Варпы";
		$json['content'] = "§b§lНажмите кнопку, чтобы начать создание.";
		$json["buttons"] = [];
		$name = [];
		foreach (SWarp::getInstance()->getAllWarp() as $warp) {
			$name[] = $warp->getName();
			$json['buttons'][] = ['text' => "§7▷ {$warp->getName()}"]; //TODO: add image
		}
		$this->warpList[$player->getName()] = $name;
		
		return json_encode($json);
	}
	
	public function handleRecieve(Player $player, $result) {
		if ($result === null) {
			return;
		}
		if (!isset($this->warpList[$player->getName()])) {
			$this->owner->getLogger()->debug("Аномальный ответ. {$player->getName()}, {$this->getName()}");
			return;
		}
		$warpname = $this->warpList[$player->getName()][$result];
		$warp = SWarp::getInstance()->getWarp($warpname);
		if (!$warp instanceof Warp) {
			$player->sendMessage(SmartUI::$prefix . "{$warpname} варп не существует.");
		}else{
			$player->sendMessage(SmartUI::$prefix . "Вы перемещены на варп {$warpname}");
			try{
                $warp->warp($player);
            }catch (WarpException $e) {
			    $player->sendMessage(SmartUI::$prefix . $e->getMessage());
            }
		}
		unset($this->warpList[$player->getName()]);
	}
	
}