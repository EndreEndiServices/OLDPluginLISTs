<?php

namespace Richen\WorldEditor;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

class WorldEditor extends PluginBase implements Listener
{
	public $pos1, $pos2;
	
	public function onEnable(){
		Server::getInstance()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args)
	{
		$command = strtolower($command->getName());
		$player = $sender;
		
		if(!$player->isOp() && !$player->hasPermission("cmd.set"))
			return $player->sendMessage("§8[§cСервер§8] §6У Вас нет прав, для выполнения данной команды.");
		
		$x = $player->getFloorX();
		$y = $player->getFloorY();
		$z = $player->getFloorZ();
		
		/*if($command[0] == "//pos1" or $command[0] == "//pos2"){
			if(!$player->hasPermission("we.all") && !$player->isOp())
			{
				$result = false;
				
				$x = round($player->getFloorX());
				$z = round($player->getFloorZ());
				$y = 0;
				
				$db = new \SQLite3($this->getDataFolder(). "regions.sqlite3");
				$db->exec("CREATE TABLE IF NOT EXISTS AREAS(Region TEXT,Owner TEXT NOT NULL,Pos1X INTEGER NOT NULL,Pos1Y INTEGER NOT NULL,Pos1Z INTEGER NOT NULL,Pos2X INTEGER NOT NULL,Pos2Y INTEGER NOT NULL,Pos2Z INTEGER NOT NULL,Level TEXT NOT NULL);CREATE TABLE IF NOT EXISTS MEMBERS(Name TEXT NOT NULL,Region TEXT NOT NULL);CREATE TABLE IF NOT EXISTS FLAGS(Region TEXT NOT NULL,Flag TEXT NOT NULL,Value TEXT NOT NULL);");
	
				while($result !== false){
					$y = $y + 1;
					$username = strtolower($player->getName());
					$result = $db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = 'world';")->fetchArray(SQLITE3_ASSOC);
					$member = $db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Region = '".$result['Region']."' AND Name = '$username'")->fetchArray(SQLITE3_ASSOC);
					$flag = $db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '".$result['Region']."' AND Flag = 'build' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
					if(
						$result !== false// 				and
						//$username != $result['Owner'] 	and 
						//!$event->getPlayer()->isOp() 	and 
						//!$member['count'] 				and 
						//!$flag['count']
					){
						$player->sendMessage("Вы не можете сетать чужие приваты! Переотметьте точки.");
						return $event->setCancelled(true);
					}
				}
			}
		}*/
		
		if($command == "/pos1"){
			$this->pos1["minX"][strtolower($player->getName())] = $x;
			$this->pos1["minY"][strtolower($player->getName())] = $y;
			$this->pos1["minZ"][strtolower($player->getName())] = $z;
			$player->sendMessage("§8[§3World§bEditor§8] §fПозиция §e1 §fустановлена.");
			$this->pos1[strtolower($player->getName())] = true;
			return;
		}
		if($command == "/pos2"){
			$this->pos2["maxX"][strtolower($player->getName())] = $x;
			$this->pos2["maxY"][strtolower($player->getName())] = $y;
			$this->pos2["maxZ"][strtolower($player->getName())] = $z;
			$player->sendMessage("§8[§3World§bEditor§8] §fПозиция §e2 §fустановлена.");
			$this->pos2[strtolower($player->getName())] = true;
			return;
		}
		
		if($command == "/set"){
			if(!isset($this->pos1[strtolower($player->getName())]) or !isset($this->pos2[strtolower($player->getName())]))
				return $player->sendMessage("§8[§3World§bEditor§8] §6Вы должны сначала отметить точки! §e//pos1 §6и §e//pos2§f!");
			
			$minX = min($this->pos1["minX"][strtolower($player->getName())],$this->pos2["maxX"][strtolower($player->getName())]);
			$maxX = max($this->pos1["minX"][strtolower($player->getName())],$this->pos2["maxX"][strtolower($player->getName())]);
			
			$minY = min($this->pos1["minY"][strtolower($player->getName())],$this->pos2["maxY"][strtolower($player->getName())]);
			$maxY = max($this->pos1["minY"][strtolower($player->getName())],$this->pos2["maxY"][strtolower($player->getName())]);
			
			$minZ = min($this->pos1["minZ"][strtolower($player->getName())],$this->pos2["maxZ"][strtolower($player->getName())]);
			$maxZ = max($this->pos1["minZ"][strtolower($player->getName())],$this->pos2["maxZ"][strtolower($player->getName())]);
			
			$count = abs(($maxX - $minX + 1) * ($maxY - $minY + 1) * ($maxZ - $minZ + 1));
			
			if($count == null)
				return $player->sendMessage("§8[§3World§bEditor§8] §6Вы должны сначала отметить точки! §e//pos1 §6и §e//pos2§f!");
			
			if($count > 100000 or $count <= 0)
				return $player->sendMessage("§8[§3World§bEditor§8] §6Вы не можете сетнуть больше 10000 блоков! Вы отметили {$count}§f!");
			
			if(!isset($args[0])) return $player->sendMessage("§8[§cСервер§8] §6Используйте: §e//set ID");
			
			for($x = $minX; $x <= $maxX; ++$x){
				for($y = $minY; $y <= $maxY; ++$y){
					for($z = $minZ; $z <= $maxZ; ++$z){
						
						$block = explode(":", $args[0]);
						if($block[0] == 57 or $block[0] == 7 or $block[0] == 14 or $block[0] == 15 or $block[0] == 16 or $block[0] == 129 or $block[0] == 21 or $block[0] == 153)
							return $player->sendMessage("§8[§3World§bEditor§8] §6Вы не можете разместить этот блок.");
						if(isset($block[1])){
							Server::getInstance()->getDefaultLevel()->setBlock(new Vector3($x, $y, $z), Block::get($block[0], $block[1]));
						}else{
							Server::getInstance()->getDefaultLevel()->setBlock(new Vector3($x, $y, $z), Block::get($block[0], 0));
						}
					}
				}
			}
			$player->sendMessage("§8[§3World§bEditor§8] §e$count §fблоков размещено!");
		}
	}
}