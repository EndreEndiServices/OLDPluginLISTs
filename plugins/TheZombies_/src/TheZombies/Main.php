<?php

namespace TheZombies;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\command\{Command, CommandSender};

class Main extends PluginBase implements Listener
{
	public $arenas = [];
	
	public function onEnable(){
		if(!is_dir($this->getDataFolder())){
			@mkdir($this->getDataFolder());
			@mkdir($this->getDataFolder() ."arenas");
		}
		if(!is_file($this->getDataFolder() ."list.yml")){
			$list = new Config($this->getDataFolder() ."list.yml", Config::YAML);
			
			$list->set("arenas", []);
			$list->save();
		}
		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new Task($this), 20);
		
		$this->getServer()->getPluginManager()->registerEvents(new Events($this), $this);
		
		$this->commands = new Commands($this);
		
		$config = new Config($this->getDataFolder() ."list.yml", Config::YAML);
		
		$arenas = $config->getNested("arenas");
		foreach($arenas as $arena){
			$this->loadArena($arena);
		}
	}
	
	public function loadArena($arena){
		unset($this->arenas[$arena]);
		
		$config = (new Config($this->getDataFolder() ."arenas/". $arena .".yml", Config::YAML, ["arena" => $arena, "server" => "mini-z1", "min_players" => 2, "max_players" => 12, "lobby" => ["x" => 0, "y" => 0, "z" => 0, "yaw" => 0, "pitch" => 0], "spawn" => ["x" => 0, "y" => 0, "z" => 0, "yaw" => 0, "pitch" => 0]]))->getAll();
		
		if(!$this->getServer()->isLevelLoaded($config["arena"])){
			$this->getServer()->loadLevel($config["arena"]);
			$this->getServer()->getLevelByName($config["arena"])->setAutoSave(false);
		}else{
			$this->getServer()->unloadLevel($this->getServer()->getLevelByName($config["arena"]));
			$this->getServer()->loadLevel($config["arena"]);
			$this->getServer()->getLevelByName($config["arena"])->setAutoSave(false);
		}
					
		$this->arenas[$config["arena"]] = new Arena($this, $config);
	}
	
	public function onCommand(CommandSender $player, Command $command, $label, array $args): bool{
		if($command->getName() == "zombies"){
			$this->commands->onCommand($player, $command, $label, $args);
			return true;
		}
	}
}
