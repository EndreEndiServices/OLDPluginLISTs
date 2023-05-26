<?php

namespace MX;

use MX\libraries\MinecraftQuery;
use MX\libraries\MinecraftQueryException;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Zombie;
use pocketmine\entity\Enderman;
use pocketmine\entity\Stray;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\scheduler\CallbackTask;
use pocketmine\Player;

class MainClass extends PluginBase implements Listener {

    private $Query;
    private $server = array();
//Настройка
	public $timeout = 3; //каждые сколько секунд будет обновление онлайна
	public $name1 = Lol; //название 1 сервера
	public $ip1 = localhost; //айпи 1
	public $port1 = 19132; //порт 1
	public $name2 = Lol2; //название 2 сервера
	public $ip2 = localhost; //айпи 2
	public $port2 = 19132; //порт 2
	public $name3 = Lol3; //название 3 сервера
	public $ip3 = localhost; // айпи 3
	public $port3 = 19132; //порт 3
	
    public function onEnable() {
        $this->timeout = 3;
        $this->Query = new MinecraftQuery();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "setNameTag")), 201);
    }
	
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
		$p = $sender;
		if($cmd->getName() == "set"){
			if(isset($args)){
				if($args[0] == "1"){
					
					$npc = new Human($p->getLevel(),
					new CompoundTag("", [
						"Pos" => new ListTag("Pos", [
							new DoubleTag("", $p->getX()),
							new DoubleTag("", $p->getY()),
							new DoubleTag("", $p->getZ())
						]),
						"Motion" => new ListTag("Motion", [
							new DoubleTag("", 0),
							new DoubleTag("", 0),
							new DoubleTag("", 0)
						]),
						"Rotation" => new ListTag("Rotation", [
							new FloatTag("", $p->yaw),
							new FloatTag("", $p->pitch)
						]),
						"Skin" => new CompoundTag("Skin", [
							"Data" => new StringTag("Data", $p->getSkinData()),
							"Name" => new StringTag("Name", $p->getSkinId())])
						]));
					$npc->spawnToAll();
					$npc->setNameTag("§a§l»§b ".$name1.", §a«§r\n§fИграют §b{$this->getPlayers("".$ip1."", $port1)} §fигрока");
					$npc->setNameTagVisible(true); 
					$npc->setNameTagAlwaysVisible(true);
						
				}elseif($args[0] == "2"){
					
					$npc = new Human($p->getLevel(),
					new CompoundTag("", [
						"Pos" => new ListTag("Pos", [
							new DoubleTag("", $p->getX()),
							new DoubleTag("", $p->getY()),
							new DoubleTag("", $p->getZ())
						]),
						"Motion" => new ListTag("Motion", [
							new DoubleTag("", 0),
							new DoubleTag("", 0),
							new DoubleTag("", 0)
						]),
						"Rotation" => new ListTag("Rotation", [
							new FloatTag("", $p->yaw),
							new FloatTag("", $p->pitch)
						]),
						"Skin" => new CompoundTag("Skin", [
							"Data" => new StringTag("Data", $p->getSkinData()),
							"Name" => new StringTag("Name", $p->getSkinId())])
						]));
					$npc->spawnToAll();
					$npc->setNameTag("§a§l»§b ".$name2." §a«§r\n§fИграют §b{$this->getPlayers("".$ip2."", $port2)} §fигрока");
					$npc->setNameTagVisible(true); 
					$npc->setNameTagAlwaysVisible(true);
						
				}elseif($args[0] == "3"){
					
					$npc = new Human($p->getLevel(),
					new CompoundTag("", [
						"Pos" => new ListTag("Pos", [
							new DoubleTag("", $p->getX()),
							new DoubleTag("", $p->getY()),
							new DoubleTag("", $p->getZ())
						]),
						"Motion" => new ListTag("Motion", [
							new DoubleTag("", 0),
							new DoubleTag("", 0),
							new DoubleTag("", 0)
						]),
						"Rotation" => new ListTag("Rotation", [
							new FloatTag("", $p->yaw),
							new FloatTag("", $p->pitch)
						]),
						"Skin" => new CompoundTag("Skin", [
							"Data" => new StringTag("Data", $p->getSkinData()),
							"Name" => new StringTag("Name", $p->getSkinId())])
						]));
					$npc->spawnToAll();
					$npc->setNameTag("§a§l» ".$name3." §a«§r\n§fИграют §b{$this->getPlayers("".$ip3."", $port3)} §fигрока");
					$npc->setNameTagVisible(true); 
					$npc->setNameTagAlwaysVisible(true);					
				}
			}
		}
	}
	
	
	public function setNameTag(){
		foreach($this->getServer()->getDefaultLevel()->getEntities() as $npc){
			$nt = explode(" ", $npc->getNameTag());
			if(isset($nt[1])){
				if($nt[1] == '$name1'){
					$npc->setNameTag("§a§l»§9 ".$name1." §a«§r\n§fИграют §b{$this->getPlayers("".$ip1."", $port1)} §fигрока");
					$npc->setNameTagAlwaysVisible(true);
				}
			}
			if(isset($nt[1])){
				if($nt[1] ==  '$name2'){
					$npc->setNameTag("§a§l» ".$name2." §a«§r\n§fИграют §b{$this->getPlayers("".$ip2."", $port2)} §fигрока");
					$npc->setNameTagAlwaysVisible(true);
				}
			}
			if(isset($nt[1])){
				if($nt[1] == '$name3'){
					$npc->setNameTag("§a§l»§b ".$name3." §a«§r\n§fИграют §b{$this->getPlayers("".ip."", $port3)} §fигрока");
					$npc->setNameTagAlwaysVisible(true);
				}
			}			
		}
	}
	
	
	public function getPlayers($ip, $port){
		try {
            $this->Query->Connect($ip, $port, $this->timeout);
            $array = ($this->Query->GetInfo());
			$a = $array['Players'];
			return $a;
        } catch (MinecraftQueryException $e) {
            $this->getLogger()->critical($e->getMessage());
			return "§cОфлайн:";
        }
	}
	
	
	public function noDamage(EntityDamageEvent $event){
		$entity = $event->getEntity();
		if($entity instanceof Human){
			$nt = explode(" ", $entity->getNameTag());
		}
		if($event instanceof EntityDamageByEntityEvent){
			if($event->getDamager() instanceof Player){
				if(isset($nt[1])){
					
					if($entity instanceof Human and $nt[1] == '$name1'){	
						$this->getServer()->dispatchCommand($event->getDamager(), "transferserver ".$ip1." ".$port1."");
						$event->setCancelled();	
					}
					if($entity instanceof Human and $nt[1] == '$name2'){		
						$this->getServer()->dispatchCommand($event->getDamager(), "transferserver ".$ip2." ".$port2."");
						$event->setCancelled();
					}
					if($entity instanceof Human and $nt[1] == '$name3'){
						$this->getServer()->dispatchCommand($event->getDamager(), "transferserver ".$ip3." ".$port3."");
						$event->setCancelled();
					}
				}
			}
		}
	}
}
