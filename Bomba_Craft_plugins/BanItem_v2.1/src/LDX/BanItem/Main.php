<?php

namespace LDX\BanItem;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {

  public function onEnable() {
    $this->saveItems();
    $this->getServer()->getPluginManager()->registerEvents($this,$this);
	$this->getLogger()->info("§aBanItem от §924serv.pro §aуспешно загружен!");
  }

    public function onDisable(){
        $this->getLogger()->info("§cBanItem от §924serv.pro §cуспешно выключен!");
    }

  public function onTouch(PlayerInteractEvent $event) {
    $p = $event->getPlayer();
    if($this->isBanned($event->getItem())) {
      if(!($p->hasPermission("banitem") || $p->hasPermission("banitem.*") || $p->hasPermission("banitem.bypass"))) {
        $p->sendMessage(TextFormat::RED . "§7(§bGreat§cWorld§7) §aДанный предмет заблокирован!");
        $event->setCancelled();
      }
    }
  }

  public function onBlockPlace(BlockPlaceEvent $event) {
    $p = $event->getPlayer();
    if($this->isBanned($event->getItem())) {
      if(!($p->hasPermission("banitem") || $p->hasPermission("banitem.*") || $p->hasPermission("banitem.bypass"))) {
        $event->setCancelled();
      }
    }
  }

  public function onHurt(EntityDamageEvent $event) {
    if($event instanceof EntityDamageByEntityEvent && $event->getDamager() instanceof Player) {
      $p = $event->getDamager();
      if($this->isBanned($p->getInventory()->getItemInHand())) {
        if(!($p->hasPermission("banitem") || $p->hasPermission("banitem.*") || $p->hasPermission("banitem.bypass"))) {
          $p->sendMessage(TextFormat::RED . "§7(§bGreat§cWorld§7) §aДанный предмет заблокирован!");
          $event->setCancelled();
        }
      }
    }
  }

  public function onEat(PlayerItemConsumeEvent $event) {
    $p = $event->getPlayer();
    if($this->isBanned($event->getItem())) {
      if(!($p->hasPermission("banitem") || $p->hasPermission("banitem.*") || $p->hasPermission("banitem.bypass"))) {
        $p->sendMessage(TextFormat::RED . "§7(§bGreat§cWorld§7) §bДанный предмет заблокирован!");
        $event->setCancelled();
      }
    }
  }

  public function onShoot(EntityShootBowEvent $event) {
    if($event->getEntity() instanceof Player) {
      $p = $event->getEntity();
      if($this->isBanned($event->getBow())) {
        if(!($p->hasPermission("banitem") || $p->hasPermission("banitem.*") || $p->hasPermission("banitem.bypass"))) {
          $p->sendMessage(TextFormat::RED . "§7(§bGreat§cWorld§7) §aДанный предмет заблокирован!");
          $event->setCancelled();
        }
      }
    }
  }

  public function onCommand(CommandSender $p,Command $cmd,$label,array $args) {
    if(!isset($args[0]) || !isset($args[1])) {
      return false;
    }
    $item = explode(":",$args[1]);
    if(!is_numeric($item[0]) || (isset($item[1]) && !is_numeric($item[1]))) {
      $p->sendMessage(TextFormat::GREEN . "§7(§bGreat§cWorld§7) Пожалуйста,используйте ID предмета и повреждения, если это необходимо.");
      return true;
    }
    if($args[0] == "ban") {
      $i = $item[0];
      if(isset($item[1])) {
        $i = $i . "#" . $item[1];
      }
      if(in_array($i,$this->items)) {
        $p->sendMessage(TextFormat::RED . "§7(§bGreat§cWorld§7) §eДанный предмет уже заблокирован!");
      } else {
        array_push($this->items,$i);
        $this->saveItems();
        $p->sendMessage(TextFormat::RED . "§7(§aFree§cCraft§7) §aДанный предмет " . str_replace("#",":",$i) . " был заблокирован.");
      }
    } else if($args[0] == "unban") {
      $i = $item[0];
      if(isset($item[1])) {
        $i = $i . "#" . $item[1];
      }
      if(!in_array($i,$this->items)) {
        $p->sendMessage(TextFormat::RED . "§7(§bGreat§cWorld§7) Данный предмет не был заблокирован!");
      } else {
        array_splice($this->items,array_search($i,$this->items),1);
        $this->saveItems();
        $p->sendMessage(TextFormat::GREEN . "§7(§bGreat§cWorld§7) Данный предмет " . str_replace("#",":",$i) . " был разблокирован.");
      }
    } else {
      return false;
    }
    return true;
  }

  public function isBanned($i) {
    if(in_array(strval($i->getID()),$this->items,true) || in_array(($i->getID() . "#" . $i->getDamage()),$this->items,true)) {
      return true;
    }
    return false;
  }

  public function saveItems() {
    if(!isset($this->items)) {
      if(!file_exists($this->getDataFolder() . "items.json")) {
        @mkdir($this->getDataFolder());
        file_put_contents($this->getDataFolder() . "items.json",json_encode(array()));
      }
      $this->items = json_decode(file_get_contents($this->getDataFolder() . "items.json"),true);
    }
    @mkdir($this->getDataFolder());
    file_put_contents($this->getDataFolder() . "items.json",json_encode($this->items));
  }

}
?>
