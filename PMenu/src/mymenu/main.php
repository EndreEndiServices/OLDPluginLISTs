<?php

namespace mymenu;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\entity\Effect;
use pocketmine\command\{Command, CommandSender};
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Item;
use pocketmine\scheduler\CallbackTask;
use pocketmine\utils\Color;
use pocketmine\math\Vector3;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\entity\Entity;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\event\server\ServerShutdownEvent;
use pocketmine\event\player\{PlayerJoinEvent, PlayerPreLoginEvent, PlayerQuitEvent, PlayerChatEvent, PlayerDeathEvent, PlayerRespawnEvent, PlayerMoveEvent};
use pocketmine\event\entity\{EntityDamageByEntityEvent, EntityDamageEvent};
use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\particle\WaterParticle;
use pocketmine\level\particle\ExplodeParticle;

class main extends PluginBase implements Listener{
	
	public $prefix;
	public $part;
   public $particle;
   public $partic;
    public $crit;
    public $sss;
    public $ppp;
    public $ppps;

	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
     $this->pp = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
		$this->prefix = "§bПартиклы §e» §f ";
		$this->part[$name] = false;
     $this->particle[$name] = false;
     $this->partic[$name] = false;  
     $this->ppps[$name] = false;
      $this->ppp[$name] = false;  
      $this->crit[$name] = false;  
      $this->sss[$name] = false;  
	}

public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
		switch($cmd->getName()){
			case "menu":
			$name = $sender->getName();
			if(count($args) == 0){
				$sender->sendMessage($this->prefix."/menu open, /menu close");
 }else{
					switch($args[0]){
						case "open":
           if($sender->hasPermission("vip.open")){
          $sender->sendMessage("§fВы §aуспешно §fоткрыли меню за §4500$");
						$it = Item::get(388,0,1)->setCustomName("§4Hearth!");
       $enchant = Enchantment::getEnchantment(0);
       $enchant->setLevel(10);
       $it->addEnchantment($enchant);
       $sender->getInventory()->addItem($it);
$it = Item::get(279,0,1)->setCustomName("§4Critical!");
       $enchant = Enchantment::getEnchantment(0);
       $enchant->setLevel(10);
       $it->addEnchantment($enchant);
       $sender->getInventory()->addItem($it);
   $itm = Item::get(265,0,1)->setCustomName("§4Flame!");
       $enchant = Enchantment::getEnchantment(0);
       $enchant->setLevel(10);
       $itm->addEnchantment($enchant);
       $sender->getInventory()->addItem($itm);
      $ite = Item::get(264,0,1)->setCustomName("§4NoParticle!");
       $enchant = Enchantment::getEnchantment(0);
       $enchant->setLevel(10);
       $ite->addEnchantment($enchant);
       $sender->getInventory()->addItem($ite);
      $ite = Item::get(266,0,1)->setCustomName("§4Redstone!");
       $enchant = Enchantment::getEnchantment(0);
       $enchant->setLevel(10);
       $ite->addEnchantment($enchant);
       $sender->getInventory()->addItem($ite);
      
$ite = Item::get(35,0,1)->setCustomName("§4Water!");
       $enchant = Enchantment::getEnchantment(0);
       $enchant->setLevel(10);
       $ite->addEnchantment($enchant);
       $sender->getInventory()->addItem($ite);
$ite = Item::get(278,0,1)->setCustomName("§4Explode!");
       $enchant = Enchantment::getEnchantment(0);
       $enchant->setLevel(10);
       $ite->addEnchantment($enchant);
       $sender->getInventory()->addItem($ite);
         $its = Item::get(276,0,1)->setCustomName("§4Dust!");
       $enchant = Enchantment::getEnchantment(0);
       $enchant->setLevel(10);
       $its->addEnchantment($enchant);
       $sender->getInventory()->addItem($its);
}
						break;
						case "close":
   if($sender->hasPermission("vip.close")){
				        $sender->getInventory()->removeItem(Item::get(388,0,1));
   $sender->getInventory()->removeItem(Item::get(264,0,1));
  $sender->getInventory()->removeItem(Item::get(276,0,1));
  $sender->getInventory()->removeItem(Item::get(265,0,1));
 $sender->getInventory()->removeItem(Item::get(266,0,1));
$sender->getInventory()->removeItem(Item::get(35,0,1));
$sender->getInventory()->removeItem(Item::get(279,0,1));
$sender->getInventory()->removeItem(Item::get(278,0,1));
}
}
}
}
}


public function on(PlayerInteractEvent $e){
 $p = $e->getPlayer();
 $name = $p->getName();
  $item = $p->getInventory()->getItemInHand();
      if($item->getId() == 388 && $item->getDamage() == 0 && $item->getCustomName() == "§4Hearth!") {
     $this->part[$name] = true;
     $p->sendMessage("§fПартикл §4Hearth §fвключен!");
}
  if($item->getId() == 279 && $item->getDamage() == 0 && $item->getCustomName() == "§4Critical!") {
     $this->crit[$name] = true;
     $p->sendMessage("§fПартикл §4Critical §fвключен!");
}
  if($item->getId() == 278 && $item->getDamage() == 0 && $item->getCustomName() == "§4Explode!") {
     $this->sss[$name] = true;
     $p->sendMessage("§fПартикл §4Explode §fвключен!");
}
  if($item->getId() == 35 && $item->getDamage() == 0 && $item->getCustomName() == "§4Water!") {
     $this->ppps[$name] = true;
     $p->sendMessage("§fПартикл §4Water §fвключен!");
}
   if($item->getId() == 266 && $item->getDamage() == 0 && $item->getCustomName() == "§4Redstone!") {
     $this->ppp[$name] = true;
     $p->sendMessage("§fПартикл §4Redstone §fвключен!");
}
    if($item->getId() == 276 && $item->getDamage() == 0 && $item->getCustomName() == "§4Dust!") {
      $this->partic[$name] = true;
      $p->sendMessage("§fПартикл §4Dust §fвключен!");
}
    if($item->getId() == 265 && $item->getDamage() == 0 && $item->getCustomName() == "§4Flame!") {
     $this->particle[$name] = true;
     $p->sendMessage("§fПартикл §4Flame §fвключен!");
}
      if($item->getId() == 264 && $item->getDamage() == 0 && $item->getCustomName() == "§4NoParticle!") {
     $p->sendMessage("§fПартиклы §4выключены!");
      $this->part[$name] = false;
      $this->particle[$name] = false;
      $this->partic[$name] = false;
      $this->ppp[$name] = false;
      $this->ppps[$name] = false;
       $this->sss[$name] = false;  
       $this->crit[$name] = false;
}
}

public function onMove(PlayerMoveEvent $e){
		$p = $e->getPlayer();
		$name = $p->getName();
		if($this->part[$name] == true){
			for($x=0;$x<=15;$x++) {
                $pos = new Vector3($p->getX()+rand(0,0.55), $p->getY() + 0.50, $p->getZ()+rand(0,0.55));
                $sendercale = rand(0,3);
                $paricle = new HeartParticle($pos, $sendercale); 
                $p->getLevel()->addParticle($paricle);
             }
}
   if($this->particle[$name] == true){
			for($x=0;$x<=15;$x++) {
                $pos = new Vector3($p->getX()+rand(0,0.55), $p->getY() + 0.50, $p->getZ()+rand(0,0.55));
                $sendercale = rand(0,3);
                $paricle = new FlameParticle($pos, $sendercale); 
                $p->getLevel()->addParticle($paricle);
}
}
      if($this->crit[$name] == true){
			for($x=0;$x<=15;$x++) {
                $pos = new Vector3($p->getX()+rand(0,0.55), $p->getY() + 0.50, $p->getZ()+rand(0,0.55));
                $sendercale = rand(0,3);
                $paricle = new CriticalParticle($pos, $sendercale); 
                $p->getLevel()->addParticle($paricle);
}
}
      if($this->sss[$name] == true){
			for($x=0;$x<=15;$x++) {
                $pos = new Vector3($p->getX()+rand(0,0.55), $p->getY() + 0.50, $p->getZ()+rand(0,0.55));
                $sendercale = rand(0,3);
                $paricle = new ExplodeParticle($pos, $sendercale); 
                $p->getLevel()->addParticle($paricle);
}
}
    if($this->ppp[$name] == true){
			for($x=0;$x<=15;$x++) {
                $pos = new Vector3($p->getX()+rand(0,0.55), $p->getY() + 0.50, $p->getZ()+rand(0,0.55));
                $sendercale = rand(0,3);
                $paricle = new RedstoneParticle($pos, $sendercale); 
                $p->getLevel()->addParticle($paricle);
}
}
      if($this->ppps[$name] == true){
			for($x=0;$x<=15;$x++) {
                $pos = new Vector3($p->getX()+rand(0,0.55), $p->getY() + 0.50, $p->getZ()+rand(0,0.55));
                $sendercale = rand(0,3);
                $paricle = new WaterParticle($pos, $sendercale); 
                $p->getLevel()->addParticle($paricle);
}
}
    if($this->partic[$name] == true){
			for($x=0;$x<=15;$x++) {
                $pos = new Vector3($p->getX()+rand(0,0.55), $p->getY() + 0.50, $p->getZ()+rand(0,0.55));
                $sendercale = rand(0,3);
                $paricle = new DustParticle($pos, $sendercale); 
                $p->getLevel()->addParticle($paricle);
}
}
     }
 }