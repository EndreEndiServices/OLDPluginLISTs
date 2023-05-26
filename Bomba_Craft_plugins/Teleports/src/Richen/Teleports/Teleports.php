<?php

namespace Richen\Teleports;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

use pocketmine\Server;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\permission\Permissible;
use pocketmine\IPlayer;

use pocketmine\utils\Config;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

class Teleports extends PluginBase implements Listener
{
	private $tpplayer, $tpsender, $request;
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->request = array();
		if(!is_dir($this->getDataFolder())) @mkdir($this->getDataFolder());
		$this->config = new Config($this->getDataFolder() . "homes.yml", Config::YAML);
	}
	
	//public function onDisable(){ $this->config->save(); }
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args)
	{
		$player = $sender;
		
		switch($command->getName()){
			case 'spawn':
			$player->teleport(new Vector3(25.5,64.5,342.5));
			break;
			case 'tpa':
			if(!isset($args[0])) return $sender->sendMessage("§cИспользуйте: /tpa <ник_игрока>");
			$nickname = $args[0];
			$tpplayer = $this->getServer()->getPlayer($args[0]);
			if(!($tpplayer))
				return $sender->sendMessage("§cИгрока с ником §6$args[0] §cнет онлайн");
			
			if(isset($this->request[strtolower($tpplayer->getName())]) && in_array(strtolower($sender->getName()), $this->request[strtolower($tpplayer->getName())]))
				return $sender->sendMessage("Вы уже отправили запрос игроку " . $tpplayer->getName());
			
			$tpplayer->sendMessage("§7* §eИгрок §a" . $sender->getName() . " §eотправил вам запрос на телепортацию");
			$tpplayer->sendMessage("§7* §eВведите §a/tpc §e- принять, §c/tpd §e- отменить запрос на телепортацию");
			$sender->sendMessage("§eВы отправили игроку §6" . $tpplayer->getName() . " §eзапрос на телепортацию");
			
			if(!isset($this->request[strtolower($tpplayer->getName())])){
				$this->request[strtolower($tpplayer->getName())] = array(strtolower($sender->getName()));
			}else{
				array_push($this->request[strtolower($tpplayer->getName())], strtolower($sender->getName()));
			}
			
			break;
			
			case 'tpc':
				if(!isset($this->request[strtolower($sender->getName())])) return $sender->sendMessage("§cВам не поступали запросы на телепортацию");
				$names = "";
				foreach($this->request[strtolower($sender->getName())] as $name){
					if(($player = $this->getServer()->getPlayer($name))){
						$player->teleport(new Vector3($sender->getFloorX()+0.5, $sender->getFloorY()+1, $sender->getFloorZ()+0.5));
						$names .= $player->getName() . "§7,§6 ";
						$player->sendMessage("§eВаш запрос на телепортацию был принят игроком, §e" . $sender->getName());
						unset($this->request[strtolower($sender->getName())]);
					}
				}
				$sender->sendMessage("§7* §aИгроки, которые отправили запрос на телепортацию, перемещены к вам.");
				$sender->sendMessage("§7* §aОтправляли запрос: §6" . $names);
			break;
			
			case 'tpd':
				if(!isset($this->request[strtolower($sender->getName())])) return $sender->sendMessage("§cВам не поступали запросы на телепортацию");
				$names = "";
				foreach($this->request[strtolower($sender->getName())] as $name){
					$names .= $name . ", ";
				}
				$sender->sendMessage("§eВы отменили запросы от игроков: ");
			break;
			
			case 'tp':
				if(!$player->hasPermission("cmd.teleport") && !$player->isOp()) return $sender->sendMessage("§8[§cСервер§8] §6У вас нет прав, для выполнения команды");
				if(isset($args[0])){ $name = $args[0]; }else{ return $sender->sendMessage("§cИспользуйте: /utp [игрок]"); }
				if(!($player = $this->getServer()->getPlayer($name))) return $sender->sendMessage("§cИгрок с ником §6{$name} §cне онлайн");
				$sender->teleport(((new Vector3($player->getFloorX(),$player->getFloorY(),$player->getFloorZ()))->add(0.5,0.5,0.5)));
				$sender->sendMessage("§aВы успешно телепортировались к игроку §e{$player->getName()}");
			break;
		}
	}
}