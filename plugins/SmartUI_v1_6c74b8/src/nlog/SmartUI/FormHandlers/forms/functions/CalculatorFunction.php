<?php

namespace nlog\SmartUI\FormHandlers\forms\functions;

use nlog\SmartUI\FormHandlers\SmartUIForm;
use nlog\SmartUI\SmartUI;
use pocketmine\entity\projectile\Throwable;
use pocketmine\Player;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

class CalculatorFunction extends SmartUIForm{
	
	public static function getName(): string{
		return "calculator";
	}
	
	public static function getIdentifyName(): string{
		return "calc";
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
		$json['title'] = "§6- calculator";
		$json['content'] = [];
		$json['content'][] = ["type" => "label", "text" => "va rog, scrie formula sigur.\nplus: +, Mai putin: -,\nУмножение: *, diviziune: /\npatrat radacina: √\nin cutie: ^"];
		$json['content'][] = ["type" => "input", "text" => "introduceti formula pentru calculator.", "placeholder" => "Introduceti formula..."];
		
		return json_encode($json);
	}
	
	public function handleRecieve(Player $player, $result) {
		if ($result === null) {
			return;
		}
		$formula = trim($result[1]);
		$formula = str_replace(["√", "^"], ["sqrt", "**"], $formula);
		if ($formula === "") {
			$player->sendMessage(SmartUI::$prefix . "Nu ai intrat în nimic.");
			return;
		}
		
		$realpath = $this->owner->getDataFolder() . "temp.yml";
		if (file_exists($realpath)) {
			@unlink($realpath);
		}
		eval('try{ file_put_contents("'. $realpath . '", yaml_emit(["calc" => ' . $formula . '])); }catch(\Throwable $e){ }');
		if (!file_exists($realpath)) {
			$player->sendMessage(SmartUI::$prefix . "Formula nevalidă.");
			return;
		}
		$result = yaml_parse(file_get_contents($realpath))['calc'];
		if (!is_float($result) && !is_int($result)) {
			$player->sendMessage(SmartUI::$prefix . "Formula nevalidă.");
			return;
		}
		$formula = str_replace(["sqrt", "**"] , ["√", "^"], $formula);
		$player->sendMessage(SmartUI::$prefix . "rezultat: {$formula} = {$result}");
	}
	
}