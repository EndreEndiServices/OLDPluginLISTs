<?php

namespace Xaoc;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class MainClass extends Commands
{
	public $mutes = [];
	public $ban, $kick, $mute;
	public function __construct(){
		parent::__construct($this);
	}
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents(new Commands ($this), $this);	@mkdir($this->getServer()->getDataPath()."/plugins/Data");
		$this->bantime = new Config($this->getServer()->getDataPath()."plugins/Data/time.json", Config::JSON);
		$this->bandata = new Config($this->getServer()->getDataPath()."plugins/Data/bans.json", Config::JSON);
		foreach (["kick", "ban", "pardon"] as $cmd){
			if($this->getServer()->getCommandMap()->getCommand($cmd) !== null){
				$this->getServer()->getCommandMap()->getCommand($cmd)->setLabel($cmd."__");
				$this->getServer()->getCommandMap()->getCommand($cmd)->unregister($this->getServer()->getCommandMap());
			}
		}

		$this->getServer()->getCommandMap()->register("", new Commands\PardonCmd ($this));
		$this->getServer()->getCommandMap()->register("", new Commands\KickCmd ($this));
		$this->getServer()->getCommandMap()->register("", new Commands\BanCmd ($this));
	}
	
	public function saveData(){
			$this->bandata->save();
	}
}