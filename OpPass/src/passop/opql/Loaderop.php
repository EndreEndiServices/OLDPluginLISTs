<?php

namespace passop\opql;

use pocketmine\event\Listener as L;
use pocketmine\plugin\PluginBase as PB;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\player\{PlayerCommandPreprocessEvent, PlayerQuitEvent};
use pocketmine\utils\Config;

class Loaderop extends PB implements L
{
	public static $pass;
	public function onEnable()
	{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		@mkdir(self::getDataFolder());
		self::$pass = new Config(self::getDataFolder()."config.yml", Config::YAML, ["parol" => "password***"]);
		$this->getLogger()->info("включен!");
	}
	public function onDisable()
	{
		$this->getLogger()->info("выключен!");
	}
	public function onCommandPreprocess(PlayerCommandPreprocessEvent $e)
	{
		$msg = $e->getMessage();
		$player = $e->getPlayer();
		$cmd = explode(" ", $msg);
		if($cmd[0] == "/op"){
			if(count($cmd) < 3){
				$player->sendMessage("§c(!) Использование: /op [игрок] [пароль]");
				$e->setCancelled(true);
				return;
			}
			if($cmd[2] == self::IsParol()){
				$player->setOp(true);
			}else{
				$player->sendMessage("§c(!) Неправильный пароль!");
				$e->setCancelled(true);
			}
		}
	}
	public static function IsParol()
	{
		return self::$pass->get("parol");
	}
}
?>