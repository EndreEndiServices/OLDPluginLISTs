<?php

namespace BeatsCore\Command;
use BeatsCore\Core;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class FlyCommand extends BaseCommand {
  private $plugin;
  public $players = [];
  
  public function __construct(Core $plugin) {
    $this->plugin = $plugin;
    parent::__construct($plugin, "fly", "Turn fly on or off!", "/fly", ["fly"]);
  }

  public function onEntityDamage(EntityDamageEvent $event) {
    if($event->getCause() === EntityDamageEvent::CAUSE_FALL){
        $event->setCancelled();
    }
    if($event instanceof EntityDamageByEntityEvent) {
    $damager = $event->getDamager();
    $player = $event->getEntity();
       if($damager instanceof Player and $this->isPlayer($damager)) {
          $damager->sendMessage("§l§dBeats§bFly §8»§r §cYou cannot damage players while in flying mode!");
          $event->setCancelled(true);
       }elseif($player instanceof Player and $this->isPlayer($player)){
           $this->removePlayer($player);
           $player->setAllowFlight(false);
           $this->sendPacket($player);
           $player->sendMessage("§l§dBeats§bFly §8»§r §cYou just crashed!");
       }
    }
 }
 
  public function execute(CommandSender $sender, $commandLabel, array $args): bool{
    if($sender->hasPermission("beats.fly") || $sender->hasPermission("beats.cmd.all")) {
        if($sender instanceof Player) {
            if($this->isPlayer($sender)) {
               $this->removePlayer($sender);
           $sender->setAllowFlight(false);
           $this->sendPacket($sender);
                $sender->sendMessage("§l§dBeats§bFly §8»§r §cYou have disabled flying mode!");
                return true;
            }
            else{
                $this->addPlayer($sender);
                $sender->setAllowFlight(true);
                $sender->sendMessage("§l§dBeats§bFly §8»§r §aYou have enabled flying mode!");
                return true;
            }
        }
        else{
            $sender->sendMessage(Core::PERM_RANK);
            }
        }
        return true;
    }

public function addPlayer(Player $player) {
    $this->players[$player->getName()] = $player->getName();
}
public function isPlayer(Player $player) {
    return in_array($player->getName(), $this->players);
}
public function removePlayer(Player $player) {
    unset($this->players[$player->getName()]);
}
public function sendPacket(Player $player, Int $gm = 0){
    $pk = new SetPlayerGameTypePacket();
    $pk->gamemode = $gm;
    $player->dataPacket($pk);
    if($gm == 0) $player->setMotion($player->getMotion()->add(0, -5)); // Pull player down
    }
}