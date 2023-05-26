<?php

namespace Quest;

use pocketmine\tile\Tile; 
use pocketmine\entity\Entity;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server; 
use pocketmine\event\Listener; 
use pocketmine\event\entity\EntityDamageEvent; 
use pocketmine\event\entity\EntityDamageByEntityEvent; 
use pocketmine\level\Position;
use pocketmine\entity\Human;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag; 
use pocketmine\nbt\tag\DoubleTag; 
use pocketmine\nbt\tag\FloatTag; 
use pocketmine\nbt\tag\StringTag;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\command\Command; 
use pocketmine\command\CommandSender; 
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;

class Main extends PluginBase implements Listener {
	private $p = array(); 
	
	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);   
	} 

	public function onDmg(EntityDamageEvent $e){
		if($e instanceof EntityDamageByEntityEvent){
			$en = $e->getEntity(); 
			$p = $e->getDamager(); 
			if($en->getNameTag() == "§8(§6Квесты §cPvP§8)"){ 
				$e->setCancelled(); 
				if(isset($this->p[$p->getName()])){
					if($this->p[$p->getName()]["count"] > 0){
						$p->sendMessage("§8(§6Квесты §cPvP§8) §fМаловато ты §aубил§7!"); 
						return; 
					}
					unset($this->p[$p->getName()]); 
					$p->sendMessage("§8(§6Квесты §cPvP§8) §fТы выполнил §aквест§7! Получай §aвознаграждение§7!");
					$p->getInventory()->addItem(Item::get(264, 0, 2));
				}else{
					$count  = mt_rand(1, 5); 
					$this->p[$p->getName()] = ["count" => $count]; 
					$p->sendMessage("§8(§6Квесты §cPvP§8) §fВот новый PvP-квест для тебя: убей мне §a".$count." игроков§7!"); 
				}
			}
		}
	}

	public function onDeath(PlayerDeathEvent $e){
		$p = $e->getEntity();
		if($p instanceof Player){
			$c = $p->getLastDamageCause();
			if($c instanceof EntityDamageByEntityEvent){
				$d = $c->getDamager();
				if($d instanceof Player){
					$this->p[$d->getName()]["count"]--;
				}
			}
		}
	}

	public function onCommand(CommandSender $p, Command $cmd, $label, array $args){
		{
			if($cmd->getName() == "fqpvp"){
				if(!$p->isOp()){
					$p->sendMessage("§8(§6Квесты §cPvP§8) §fНедостаточно §aправ§7!"); 
					return; 
				}
				$p->sendMessage("§8(§6Квесты §cPvP§8) §aСоздано."); 
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
							new FloatTag("", $p->getYaw()),
							new FloatTag("", $p->getPitch())
							]),
						"Skin" => new CompoundTag("Skin", [
							"Data" => new StringTag("Data", $p->getSkinData()),
							"Name" => new StringTag("Name", $p->getSkinId())])
						]
						));
				$npc->spawnToAll();
				$npc->setDataProperty(Entity::DATA_NAMETAG, Entity::DATA_TYPE_STRING, "§8(§6Квесты §cPvP§8)");
				$npc->setNameTagVisible(true);
			}
		}
	}

}