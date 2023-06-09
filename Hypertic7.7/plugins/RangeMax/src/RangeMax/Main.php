<?php
namespace RangeMax;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as color;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class Main extends PluginBase implements Listener{
    public $config;
    public function onEnable() {
        @mkdir($this->getDataFolder());
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, array("range" => 5));
        $this->getServer()->getPluginManager()->registerEvents($this, $this);   
    }
    public function onDisable() {
        $this->config->save();
    }
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        switch ($command){
            case 'mr':
                if(count($args) == 1){
                    $this->config->set("range", $args[0]);
                    $this->config->save();
                    $sender->sendMessage(color::GREEN."range set!");
                    $this->config->reload();
                }  else {
                $sender->sendMessage(color::RED."error in command!");    
                }
            break;
        }
      }
    public function EDE(EntityDamageEvent $event){
   
        if($event instanceof EntityDamageByEntityEvent){
          $attacker = $event->getDamager();
          $victim = $event->getEntity();
          $dist = $attacker->distance($victim->getPosition());
          if($dist > $this->config->get("range") && $attacker->getLevel()->getName() == $victim->getLevel()->getName()){
              if($attacker instanceof \pocketmine\Player){
                  if($attacker->getInventory()->getItemInHand()->getId() == \pocketmine\item\Item::BOW){
                                            return;}else{    
              $event->setCancelled();
        //         $attacker->kick(color::RED."ANTI-REACH caught you");
                  
              
              }}
        }
        }
    }
}