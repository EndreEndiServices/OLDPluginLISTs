<?php
namespace FaigerSYS\MultiProtocol;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\TextFormat as CLR;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

use pocketmine\network\protocol\Info;
use FaigerSYS\MultiProtocol\packet\ModernLoginPacket;

class MultiProtocol extends PluginBase {
	
	public function onEnable() {
		$this->getLogger()->info(CLR::GOLD . 'MultiProtocol загружается...');
		
		$data = $this->prepareData();
		ModernLoginPacket::$SUPPORTED_PROTOCOLS = $data['protocols'];
		
		$this->getServer()->getNetwork()->registerPacket(ModernLoginPacket::NETWORK_ID, ModernLoginPacket::class);
		
		$this->getLogger()->info(CLR::GOLD . 'MultiProtocol загружен!');
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		$protocol = Info::CURRENT_PROTOCOL;
		if (is_array($protocol)) {
			$protocol = implode(', ', $protocol);
		}
		$sender->sendMessage('Протокол(ы) этой версии ядра: ' . $protocol);
	}
	
	private function prepareData() {
		@mkdir($path = $this->getDataFolder());
		$defaultConfig = stream_get_contents($this->getResource($file = 'settings.yml'));
		$defaultData = yaml_parse($defaultConfig);
		if (!file_exists($path .= $file)) {
			file_put_contents($path, $defaultConfig);
			return $defaultData;
		} else {
			$newData = @yaml_parse(file_get_contents($path));
			if (!is_array($newData) || empty($newData)) {
				$this->getLogger()->warning('Файл с настройками был повреждён. Он будет ввостановлен в начальный вид');
				file_put_contents($path, $defaultConfig);
				return $defaultData;
			} else {
				return array_replace_recursive($defaultData, $newData);
			}
		}
	}
	
}
