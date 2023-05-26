<?php

namespace MF;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\Inventory;
use pocketmine\item\Item;
use pocketmine\entity\Entity;
use pocketmine\entity\Effect;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\block\BlockBreakEvent;
use onebone\economyapi\EconomyAPI;
use _64FF00\PurePerms\PurePerms;

Class KitPvP extends PluginBase implements Listener{

       public $eco;
       public $pp;
       public $cmd;

public function onEnable(){
  $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->eco = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
    $this->pp = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
   $this->cmd = [];
}
public function onCommand(CommandSender $sender, Command $command, $label, array $args){
  switch($command->getName()){
  case "freecoins":
  $name = $sender->getName();
  if($this->cmd[$name] != 1){
  $this->cmd[$name] = 1;
  $rand = mt_rand(5, 100);
  $this->eco->addMoney($name, $rand);
  $sender->sendMessage("§8(§cKitPvP§8) §fВам было выдано §e$rand монет.");
  }else{
   $sender->sendMessage("§8(§cKitPvP§8) §fТы уже §cвводил команду! §eПопробуй после рестарта сервера!");
   $sender->sendPopup("§eПопробуй после рестарта сервера...");
   break;
        }
   case "pvp":
   $sender->getInventory()->clearAll();
   $sender->getInventory()->addItem(Item::get(306, 0, 1));
   $sender->getInventory()->addItem(Item::get(307, 0, 1));
   $sender->getInventory()->addItem(Item::get(308, 0, 1));
   $sender->getInventory()->addItem(Item::get(309, 0, 1));
                  $sword = Item::get(276, 0, 1);
   $sword->addEnchantment(Enchantment::getEnchantment(9)->setLevel(1));
   $sender->getInventory()->addItem($sword);
   $sender->getInventory()->addItem(Item::get(322, 0, 64));
   $sender->sendMessage("§8(§cKitPvP§8) §fВы §aполучили §eнабор §3pvp");
   break;
   case tank:
   $sender->getInventory()->clearAll();
   $sender->getInventory()->addItem(Item::get(310, 0, 1));
   $sender->getInventory()->addItem(Item::get(311, 0, 1));
   $sender->getInventory()->addItem(Item::get(312, 0, 1));
   $sender->getInventory()->addItem(Item::get(313, 0, 1));
   $sender->getInventory()->addItem(Item::get(272, 0, 1));
   $sender->getInventory()->addItem(Item::get(322, 0, 64));
   $sender->sendMessage("§8(§cKitPvP§8) §fВы §aполучили §eнабор §3tank");
   break;
   case ninja:
   $sender->getInventory()->clearAll();
   $sender->getInventory()->addItem(Item::get(306, 0, 1));
   $sender->getInventory()->addItem(Item::get(315, 0, 1));
   $sender->getInventory()->addItem(Item::get(300, 0, 1));
   $sender->getInventory()->addItem(Item::get(301, 0, 1));
   $sender->getInventory()->addItem(Item::get(276, 0, 1));
   $speeda = Item::get(353, 0, 1);
   $speeda->setCustomName("§r§bСкорость §8[§5Тапни§8]");
   $sender->getInventory()->addItem($speeda);
   $sender->getInventory()->addItem(Item::get(322, 0, 64));
   $sender->sendMessage("§8(§cKitPvP§8) §fВы §aполучили §eнабор §3ninja");
   break;
   case wizard:
   $sender->getInventory()->clearAll();
   $sender->getInventory()->addItem(Item::get(310, 0, 1));
   $sender->getInventory()->addItem(Item::get(307, 0, 1));
   $sender->getInventory()->addItem(Item::get(300, 0, 1));
   $sender->getInventory()->addItem(Item::get(305, 0, 1));

   $jump = Item::get(377, 0, 1);
   $jump->setCustomName("§r§eПрыжок вверх §8[§5Тапни§8]");
   $sender->getInventory()->addItem($jump);
   $invis = Item::get(369, 0, 1);
   $invis->setCustomName("§r§cНеведимость §8[§5Тапни§8]");
   $sender->getInventory()->addItem($invis);
   $speed = Item::get(353, 0, 1);
   $speed->setCustomName("§r§bСкорость §8[§5Тапни§8]");
   $sender->getInventory()->addItem($speed);
   $sender->getInventory()->addItem(Item::get(322, 0, 64));
   $sender->sendMessage("§8(§cKitPvP§8) §fВы §aполучили §eнабор §3wizard");
   break;
   case pyro:
   $sender->getInventory()->clearAll();
   $armor1 = Item::get(306, 0, 1);
   $armor1->addEnchantment(Enchantment::getEnchantment(1)->setLevel(5));
   $sender->getInventory()->addItem($armor1);
   $armor2 = Item::get(307, 0, 1);
   $armor2->addEnchantment(Enchantment::getEnchantment(1)->setLevel(5));
   $sender->getInventory()->addItem($armor2);
   $armor3 = Item::get(308, 0, 1);
   $armor3->addEnchantment(Enchantment::getEnchantment(1)->setLevel(5));
   $sender->getInventory()->addItem($armor3);
   $armor4 = Item::get(309, 0, 1);
   $armor4->addEnchantment(Enchantment::getEnchantment(1)->setLevel(5));
   $sender->getInventory()->addItem($armor4);
   $pyrosword = Item::get(267, 0, 1);
   $pyrosword->addEnchantment(Enchantment::getEnchantment(13)->setLevel(3));
   $sender->getInventory()->addItem($pyrosword);
   $sender->getInventory()->addItem(Item::get(322, 0, 64));
   $sender->sendMessage("§8(§cKitPvP§8) §fВы §aполучили §eнабор §3pyro");
   break;
   case tptkpvp:
   $sender->teleport(new Position(4, 68, 143, $this->getServer()->getLevelByName("KitPvP")));
   break;
    }
}
public function KillMoney(PlayerDeathEvent $event){
   $cause = $event->getEntity()->getLastDamageCause();
   $player = $event->getEntity();
   $damager = $event->getEntity()->getLastDamageCause()->getDamager();
   $name = $damager->getName();
   $nick = $player->getName();
       if($cause instanceof EntityDamageByEntityEvent){
   $this->eco->addMoney($damager, 5);
   $this->getServer()->broadcastMessage("§8(§cKitPvP§8) §fИгрок §d$name §cубил §fигрока §d$nick");
   $damager->sendPopup("§e+5 монет за убийство");
   $damager->sendMessage("§8(§cKitPvP§8) §fВы §cубили §fигрока §d$nick");
    }
 }
public function Money(PlayerItemHeldEvent $e){
   $p = $e->getPlayer();
   $hand = $p->getInventory()->getItemInHand()->getId();
          if($hand == 175){
   $p->sendPopup("§eВаши монеты §8[§5Тапни§8]");
    }
}
public function TapMoney(PlayerInteractEvent $e){
   $p = $e->getPlayer();
   $n = $p->getName();
          if($p->getInventory()->getItemInHand()->getId() == 175){
   $coins = $this->eco->myMoney($n);
   $p->sendMessage("§8(§cKitPvP§8) §fВаши монеты: §6$coins");
    }
}
public function JoinDonater(PlayerJoinEvent $e){
   $p = $e->getPlayer();
   $n = $p->getName();
   $group = $this->pp->getUserDataMgr()->getGroup($p)->getName();
          if($group == "Knight"){
   $this->getServer()->broadcastMessage("§8(§bРыцарь§8) §b$n §aприсоединился §fк игре");
}
          if($group == "Prince"){
   $this->getServer()->broadcastMessage("§8(§aПринц§8) §a$n §aприсоединился §fк игре");
}
          if($group == "Angel"){
   $this->getServer()->broadcastMessage("§8(§dАнгел§8) §d$n §aприсоединился §fк игре");
}
          if($group == "Legend"){
   $this->getServer()->broadcastMessage("§8(§eЛегенда§8) §e$n §aприсоединился §fк игре");
    }
}
public function SpecialItems(PlayerInteractEvent $e){
   $p = $e->getPlayer();
   $n = $p->getName();
          if($p->getInventory()->getItemInHand()->getId() == 377){
   $p->setMotion(new Vector3(0, 1, 0));
}
          if($p->getInventory()->getItemInHand()->getId() == 369){
   $p->addEffect(Effect::getEffect(14)->setAmplifier(1)->setDuration(20 * 5));
}
          if($p->getInventory()->getItemInHand()->getId() == 353){
   $p->addEffect(Effect::getEffect(1)->setAmplifier(5)->setDuration(20 * 60));
    }
}
public function onDisable(){}
}
?>