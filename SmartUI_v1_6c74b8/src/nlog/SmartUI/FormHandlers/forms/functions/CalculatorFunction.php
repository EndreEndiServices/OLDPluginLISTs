<?php

namespace nlog\SmartUI\FormHandlers\forms\functions;

use nlog\SmartUI\FormHandlers\SmartUIForm;
use nlog\SmartUI\SmartUI;
use pocketmine\entity\projectile\Throwable;
use pocketmine\Player;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

class CalculatorFunction extends SmartUIForm{
	
	public static function getName(): string{
		return "Калькулятор";
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
		$json['title'] = "§6- Калькулятор";
		$json['content'] = [];
		$json['content'][] = ["type" => "label", "text" => "Пожалуйста, напишите формулу точно.\nПлюс: +, Минус: -,\nУмножение: *, Деление: /\nКвадратный корень: √\nВ квадрате: ^"];
		$json['content'][] = ["type" => "input", "text" => "Введите формулу для вычисления.", "placeholder" => "Введите формулу..."];
		
		return json_encode($json);
	}
	
	public function handleRecieve(Player $player, $result) {
		if ($result === null) {
			return;
		}
		$formula = trim($result[1]);
		$formula = str_replace(["√", "^"], ["sqrt", "**"], $formula);
		if ($formula === "") {
			$player->sendMessage(SmartUI::$prefix . "Вы ничего не ввели.");
			return;
		}
		
		$realpath = $this->owner->getDataFolder() . "temp.yml";
		if (file_exists($realpath)) {
			@unlink($realpath);
		}
		eval('try{ file_put_contents("'. $realpath . '", yaml_emit(["calc" => ' . $formula . '])); }catch(\Throwable $e){ }');
		if (!file_exists($realpath)) {
			$player->sendMessage(SmartUI::$prefix . "Недопустимая формула.");
			return;
		}
		$result = yaml_parse(file_get_contents($realpath))['calc'];
		if (!is_float($result) && !is_int($result)) {
			$player->sendMessage(SmartUI::$prefix . "Недопустимая формула.");
			return;
		}
		$formula = str_replace(["sqrt", "**"] , ["√", "^"], $formula);
		$player->sendMessage(SmartUI::$prefix . "Результат: {$formula} = {$result}");
	}
	
}