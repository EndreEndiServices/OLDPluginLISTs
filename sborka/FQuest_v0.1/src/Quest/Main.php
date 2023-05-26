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
if($en->getNameTag() == "§f > §aКвесты §f < "){ 
$e->setCancelled(); 
if(isset($this->p[$p->getName()])){
$i = $p->getItemInHand(); 
switch($i->getId()){
case 0: 
$p->sendMessage("§7Принеси мне §a". $this->p[$p->getName()]["name"]."§7!"); 
break;
case $this->p[$p->getName()]["id"]: 
if($i->getCount() < $this->p[$p->getName()]["count"]){
$p->sendMessage("§7Маловато ты §aпринес§7!"); 
return; 
}
$it = Item::get($this->p[$p->getName()]["id"], 0, $this->p[$p->getName()]["count"]); 
$p->getInventory()->removeItem($it); 
unset($this->p[$p->getName()]); 
$p->sendMessage("§7Ты выполнил §aквест§7! Получай §aвознаграждение§7!"); 
if(mt_rand(0, 1) == 0) $ite = 264; else $ite = Item::IRON_INGOT;
$p->getInventory()->addItem(Item::get($ite, 0, mt_rand(5, 15))); 
break; 
default: 
$p->sendMessage("§7Я не это §aпросил§7!"); 
break; 
}
}else{
$ids = ["земли" => Item::DIRT, "булыжника" => Item::COBBLESTONE, "угля" => Item::COAL, "железной руды" => Item::IRON_ORE]; 
$na = array_rand($ids); 
$id = $ids[$na]; 
$count  = mt_rand(10, 64); 
$this->p[$p->getName()] = ["name" => $na, "id" => $id, "count" => $count]; 
$p->sendMessage("§7Вот новый квест для тебя: Добудь и отдай мне §a". $count." ".$na."§7!"); 
}
}
}
}

   public function onCommand(CommandSender $p, Command $cmd, $label, array $args){
{
if($cmd->getName() == "fq"){
if(!$p->isOp()){
$p->sendMessage("§7Недостаточно §aправ§7!"); 
return; 
}
      $p->sendMessage("§aСоздано."); 
           $npc = new Human($p->chunk,
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
$npc->setDataProperty(Entity::DATA_NAMETAG, Entity::DATA_TYPE_STRING, "§f > §aКвесты §f < ");
$npc->setDataProperty(Entity::DATA_SHOW_NAMETAG, Entity::DATA_TYPE_BYTE, 1);
    }
    }
}

}