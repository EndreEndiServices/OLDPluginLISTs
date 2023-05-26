<?php

namespace bridge;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\utils\Config;
use pocketmine\Player;

use bridge\task\BridgeTask;
use bridge\utils\arena\Arena;
use bridge\utils\arena\ArenaManager;

class Main extends PluginBase{
	
	public $arenas = [];
	
	private $pos1 = [];
	private $pos2 = [];
	private $pos = [];
	private $spawn1 = [];
	private $spawn2= [];
	private $respawn1= [];
	private $respawn2= [];
	public $api;

	public function onEnable(){
		$this->initResources();
		$this->initArenas();
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new BridgeTask($this), 20);
		$this->getServer()->getPluginManager()->registerEvents(new Arena($this), $this);

        $this->api = $this->getServer()->getPluginManager()->getPlugin("TheBridgeAPI");
	}
	
	public function onDisable(){
		$this->close();
	}
	
	private function initResources(){
		@mkdir($this->getDataFolder());
		@mkdir($this->getDataFolder() . "maps/");
		@mkdir($this->getDataFolder() . "arenas/");
	}
	
	private function initArenas(){
		$src = $this->getDataFolder() . "arenas/";
		$count = 0;
		foreach(scandir($src) as $file){
			if($file !== ".." and $file !== "."){
				if(file_exists("$src" . $file)){
					$data = (new Config("$src" . $file, Config::YAML))->getAll();
					if(!isset($data["name"])){
						@unlink("$src" . $file);
						continue;
					}
					$this->arenas[strtolower($data["name"])] = new ArenaManager($this, $data);
					$count++;
				}
			}
		}
		return $count;
	}
	
	public function getPlayerArena(Player $p){
		$arenas = $this->arenas;
		if(count($arenas) <= 0){
			return null;
		}
		foreach($arenas as $arena){
			if($arena->isInArena($p)){
				return $arena;
			}
		}
		return null;
	}
	
	public function updateArenas($value = false){
		if(count($this->arenas) <= 0){
			return false;
		}
		foreach($this->arenas as $arena){
			$arena->onRun($value);
		}
	}
	
	private function close(){
		foreach($this->arenas as $name => $arena){
			$arena->close();
		}
	}
	
	public function join($player, $mode = "solo"){
		foreach($this->arenas as $name => $arena){
			if($arena->getData()["mode"] == $mode){
				if($arena->join($player)){
					return true;
				}
			}
		}
		return false;
	}
	
	public function createBridge($name, $p, $pos1, $pos2, $spawn1, $spawn2, $respawn1, $respawn2, $pos, $mode = "solo"){
		$src = $this->getDataFolder();
		if(file_exists($src . "arenas/" . strtolower($name) . ".yml")){
			$p->sendMessage("§cЭта арена готова для игры!");
			return false;
		}
		$config = new Config($src . "arenas/" . $name . ".yml", Config::YAML);
		
		$data = ["name" => $name, "mode" => $mode, "world" => $p->getLevel()->getName(), "info" => $pos, "pos1" => $pos1, "pos2" => $pos2, "spawn1" => $spawn1, "spawn2" => $spawn2, "respawn1" => $respawn1, "respawn2" => $respawn2];
		
		$arena = new ArenaManager($this, $data);
		
		$this->arenas[strtolower($name)] = $arena;
				
		$config->setDefaults($data);
		$config->save();
		return true;
	}
	
	public function deleteBridge($name){
		if(file_exists($src . "arenas/" . strtolower($name) . ".yml")){
			if(unlink($src . "arenas/" . strtolower($name) . ".yml")){
				if(isset($this->arenas[strtolower($name)])){
					unset($this->arenas[strtolower($name)]);
				}
				return true;
			}
		}
		return false;
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
		if(strtolower($cmd->getName()) == "bridge"){
			if(!$sender instanceof Player){
				return true;
			}
			if(isset($args[0])){
				switch(strtolower($args[0])){
					case "pos1":
					if(!$sender->hasPermission("bridge.cmd")){
				   $sender->sendMessage("§a——-§6The§cBridge §a——-“);
                             $sender->sendMessage(‘’/bridge spawn - Поставить лобби’’);
                             $sender->sendMessage(‘’/bridge spawn1 and spawn2 - Поставить точку ямы’’);
                             $sender->sendMessage(‘’/bridge respawn1 and respawn2 - Поставить респавн игроков’’);
                             $sender->sendMessage(“/bridge pos1 and pos2 - Поставить точку спавна игроков”);
                            $sender->sendMessage(“/bridge create - {Имя арены} {team/solo}”);
                           $sender->sendMessage(“/bridge join solo или team - Присоединится к игре”);
	                       return true;
	                       }
					$x = $sender->getFloorX();
					$y = $sender->getFloorY();
					$z = $sender->getFloorZ();
					$this->pos1[$sender->getName()] = ["x" => $x, "y" => $y, "z" => $z];
					$sender->sendMessage("§2BRIDGE§7 Позиция 1 поставлена на координатах X: $x Y: $y Z: $z");
					break;
					case "pos2":
					if(!$sender->hasPermission("bridge.cmd")){
						$sender->sendMessage("§2Use:§f /bridge help - все команды");
						return true;
					}
					$x = $sender->getFloorX();
					$y = $sender->getFloorY();
					$z = $sender->getFloorZ();
					$this->pos2[$sender->getName()] = ["x" => $x, "y" => $y, "z" => $z];
					$sender->sendMessage("§2BRIDGE§7 Позиция 2 поставлена по координатам X: $x Y: $y Z: $z");
					break;
					case "spawn1":
					if(!$sender->hasPermission("bridge.cmd")){
						$sender->sendMessage("§2Use:§f /bridge help§7 - все команды");
						return true;
					}
					$x = $sender->getFloorX();
					$y = $sender->getFloorY();
					$z = $sender->getFloorZ();
					$this->spawn1[$sender->getName()] = ["x" => $x, "y" => $y, "z" => $z];
					$sender->sendMessage("§2BRIDGE§7 Спавн 1 поставлен на кордах X: $x Y: $y Z: $z");
					break;
					case "spawn2":
					if(!$sender->hasPermission("bridge.cmd")){
						$sender->sendMessage("§2Use:§f /bridge help§7 - все команды");
						return true;
					}
					$x = $sender->getFloorX();
					$y = $sender->getFloorY();
					$z = $sender->getFloorZ();
					$this->spawn2[$sender->getName()] = ["x" => $x, "y" => $y, "z" => $z];
					$sender->sendMessage("§2BRIDGE§7 Спавн 2 поставлен на кордах X: $x Y: $y Z: $z");
					break;
					case "respawn1":
					if(!$sender->hasPermission("bridge.cmd")){
						$sender->sendMessage("§2Use:§f /bridge help§7 - все команды");
						return true;
					}
					$x = $sender->getFloorX();
					$y = $sender->getFloorY();
					$z = $sender->getFloorZ();
					$this->respawn1[$sender->getName()] = ["x" => $x, "y" => $y, "z" => $z];
					$sender->sendMessage("§2BRIDGE§7 Позиция респавна 1 поставлена  X: $x Y: $y Z: $z");
					break;
          case "respawn2":
					if(!$sender->hasPermission("bridge.cmd")){
						$sender->sendMessage("§2Use:§f /bridge help§7 все команды");
						return true;
					}
					$x = $sender->getFloorX();
					$y = $sender->getFloorY();
					$z = $sender->getFloorZ();
					$this->respawn2[$sender->getName()] = ["x" => $x, "y" => $y, "z" => $z];
					$sender->sendMessage("§2BRIDGE§7 Позиция респавна 2 поставлена X: $x Y: $y Z: $z");
					break;
					case "spawn":
					if(!$sender->hasPermission("bridge.cmd")){
						$sender->sendMessage("§2Use:§f /bridge help§7 все команды");
						return true;
					}
					$x = $sender->getFloorX();
					$y = $sender->getFloorY();
					$z = $sender->getFloorZ();
					$this->pos[$sender->getName()] = ["x" => $x, "y" => $y, "z" => $z, "level" => $sender->getLevel()->getName()];
					$sender->sendMessage("§2BRIDGE§7 Лобби установлено X: $x Y: $y Z: $z");
					break;
          case "create":
					if(!$sender->hasPermission("bridge.cmd")){
						$sender->sendMessage("§2Use:§f /bridge help§7  все команды");
						return true;
					}
					if(isset($args[1])){
						$name = $sender->getName();
						if(!isset($this->pos1[$name]) or !isset($this->pos2[$name])){
							$sender->sendMessage("§l§cError§r§c Позиция не поставлена!");
							return true;
						}
						if(!isset($this->spawn1[$name]) or !isset($this->spawn2[$name])){
							$sender->sendMessage("§l§cError§r§c Позиция спавна не установлена!");
							return true;
						}
						if(!isset($this->respawn1[$name]) or !isset($this->respawn2[$name])){
							$sender->sendMessage("§l§cError§r§c Позиция респавна не поставлена!");
							return true;
						}
						if(!isset($this->pos[$name])){
							$sender->sendMessage("§l§cError§r§c Лобби не установлен!");
							return true;
						}
						$level = $sender->getLevel();
						if(strlen($args[1]) > 15){
							$sender->sendMessage("§cИмя для мира мало");
							return true;
						}
						$mode = "solo";
						if(isset($args[2])){
							switch(strtolower($args[2])){
								case "solo":
								case "team":
								case "squad":
								$mode = strtolower($args[2]);
								break;
								default:
								$sender->sendMessage("§l§cError§r§c Мод игры нужен быть по дефолту");
								return true;
							}
						}
						if($this->createBridge($args[1], $sender, $this->pos1[$name], $this->pos2[$name], $this->spawn1[$name], $this->spawn2[$name], $this->respawn1[$name], $this->respawn2[$name], $this->pos[$name], $mode)){
							$sender->sendMessage("§aArena §f" . $args[1] . "Арена успешна зарегистрировалось !");
						}
					} else {
						$sender->sendMessage("§2Use: §f/bridge create§7 {аренд} {мод игры}");
						return true;
					}
					break;
					case "delete":
					if(!$sender->hasPermission("bridge.cmd")){
						$sender->sendMessage("§2Use:§f /bridge help§7 все команды");
						return true;
					}
					if(isset($args[1])){
						if($this->deleteBridge($args[1])){
							$sender->sendMessage("§2BRIDGE§7 Arena §f" . $args[1] . "§7 успешна удалена!");
						} else {
							$sender->sendMessage("§2BRIDGE§7 Арена не найдена!");
						}
					}
					break;
					case "join":
					$mode = "solo";
					if(isset($args[1])){
						switch(strtolower($args[1])){
							case "solo":
							case "team":
							case "squad":
							$mode = strtolower($args[1]);
							break;
							default:
							$sender->sendMessage("§l§cError§r§c Такого мода нету!");
							return true;
						}
					}
					if($this->join($sender, $mode)){
						$sender->sendMessage("§f- §aПодключение...");
					} else {
						$sender->sendMessage("§cСервера с режимом§7 {$mode}§c, заняты!");
					}
					break;
					default:
					$sender->sendMessage("§2Use:§f /bridge help§7 все команды");
					break;
				}
			} else {
				$sender->sendMessage("§2Use:§f /bridge help§7 все команды");
			}
		}
return true;
	}
}