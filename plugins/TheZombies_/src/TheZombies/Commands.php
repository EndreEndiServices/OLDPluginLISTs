<?php

namespace TheZombies;

use pocketmine\command\{Command, CommandSender};
use pocketmine\utils\Config;

class Commands
{
	public $plugin, $arena = "no", $lobby = "no", $spawn = "no";
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}
	
	public function onCommand(CommandSender $player, Command $command, $label, array $args): bool{
		if(!isset($args[0])){
			$player->sendMessage("§eИспользование: §a/zombies join");
			return true;
		}elseif(!$args[0] == "join" or !$args[0] == "create" or !$args[0] == "setlobby" or !$args[0] == "setspawn"){
			$player->sendMessage("§eИспользование: §a/zombies join");
			return true;
		}elseif($args[0] == "join"){
			foreach($this->plugin->arenas as $arena){
				if($arena->inArena($player) != 1 && $arena->inArena($player) != 2){
					if($arena->status == Arena::STATUS_WAITING && count($arena->survivors) < $arena->max_players){
						$arena->addPlayer($player);
						return true;
					}
				}else{
					$player->sendMessage("§cВы уже на арене!");
					return true;
				}
			}
			$player->sendMessage("§cВсе арены заняты!");
			return true;
		}elseif($args[0] == "create"){
			if(!$player->isOp()){
				$player->sendMessage("§eИспользование: §a/zombies join");
				return true;
			}else{
				if(count($args) < 5){
					$player->sendMessage("§eИспользование: §a/zombies create <название арены> <название сервера> <мин. игроков> <макс. игроков>");
					return true;
				}
				if(array_search($args[1], $this->plugin->arenas) !== false){
					$player->sendMessage("§cАрена §f{$args[1]} §cуже существует!");
					return true;
				}
				if(!is_numeric($args[3]) or !is_numeric($args[4])){
					$player->sendMessage("§cМинимальное или максимальное количество должно быть в цифрах!");
					return true;
				}
				if($args[3] > 8 or $args[3] < 2){
					$player->sendMessage("§cМинимальное количество игроков должно быть выше 2 и ниже 8!");
					return true;
				}
				if($args[4] > 36 or $args[4] < 12){
					$player->sendMessage("§cМаксимальное количество игроков должно быть выше 12 и ниже 36!");
					return true;
				}
				
				$config = new Config($this->plugin->getDataFolder() ."arenas/". $args[1] .".yml", Config::YAML);
				
				$config->set("arena", $args[1]);
				$config->set("server", $args[2]);
				$config->set("min_players", (int) $args[3]);
				$config->set("max_players", (int) $args[4]);
				
				$config->save();
				
				$this->arena = $args[1];
				$this->lobby = "select";
				
				$player->sendMessage("§aАрена §7{$args[1]} §aуспешно создана!\n§7Теперь поставь точку лобби - §b/zombies setlobby");
				return true;
			}
		}elseif($args[0] == "setlobby"){
			if(!$player->isOp()){
				$player->sendMessage("§eИспользование: §a/zombies join");
				return true;
			}else{
				if($this->arena == "no" or $this->lobby == "no"){
					$player->sendMessage("§cСначала создайте арену - §7/zombies create");
					return true;
				}else{
					$config = new Config($this->plugin->getDataFolder() ."arenas/". $this->arena .".yml", Config::YAML);
					
					$config->setNested("lobby.x", $player->getX());
					$config->setNested("lobby.y", $player->getY());
					$config->setNested("lobby.z", $player->getZ());
					$config->setNested("lobby.yaw", $player->getYaw());
					$config->setNested("lobby.pitch", $player->getPitch());
					
					$config->save();
					
					$this->lobby = "no";
					$this->spawn = "select";
					
					$player->sendMessage("§aВы успешно поставили лобби для арены §7{$this->arena}§a!\n§7Теперь поставь точку спавна игроков - §b/zombies setspawn");
					return true;
				}
			}
		}elseif($args[0] == "setspawn"){
			if(!$player->isOp()){
				$player->sendMessage("§eИспользование: §a/zombies join");
				return true;
			}else{
				if($this->arena == "no" or $this->spawn == "no"){
					$player->sendMessage("§cСначала создайте арену - §7/zombies create");
					return true;
				}else{
					$config = new Config($this->plugin->getDataFolder() ."arenas/". $this->arena .".yml", Config::YAML);
					
					$config->setNested("spawn.x", $player->getX());
					$config->setNested("spawn.y", $player->getY());
					$config->setNested("spawn.z", $player->getZ());
					$config->setNested("spawn.yaw", $player->getYaw());
					$config->setNested("spawn.pitch", $player->getPitch());
					
					$config->save();
					
					$this->plugin->loadArena($this->arena);
					
					$player->sendMessage("§aВы успешно поставили спавн для арены §7{$this->arena}§a!\n§bАрена готова к использованию!");
					
					$this->arena = "no";
					$this->spawn = "no";
					return true;
				}
			}
		}
	}
}
