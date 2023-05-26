<?php

namespace mg;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat as color;
use pocketmine\plugin\Plugin;
use pocketmine\Player;
use pocketmine\item\Item;

class BLM extends PluginTask{
    private $player;
    private $charge;
    public function __construct(Plugin $plugin, Player $player) {
        $this->player = $player;
        $this->plugin = new $plugin;
        parent::__construct($plugin);
    }
    public function onRun($tick) {
        if($this->owner->getServer()->getPlayer($this->player->getName())){
            if($this->player->isOnline() && $this->player->getHealth() != 0){
      $it = $this->player->getInventory()->getItemInHand();
      $tw = array(246, 459);

      if($it->getId() == 246 || $it->getId() == 405 || $it->getId() == 459 || $it->getId() == 406){
                    $this->ShowCharge();
         if($this->charge >= 3){
             $this->owner->f[$this->player->getName()] = TRUE;
             $this->charge = 3;
         }  else {
          $this->charge++;
      $this->owner->f[$this->player->getName()] = FALSE;            
         }
         
            }else{
          $this->charge = 0;
      $this->owner->f[$this->player->getName()] = FALSE;
    }}}}
public function SetCharge($chargeAmount){
    
    if($this->charge = $chargeAmount){
        $this->charge = $chargeAmount;
    return TRUE;
    } else {
        return FALSE;
    }
}

public function ShowCharge(){
        switch ($this->charge){
            case 0:
                
            break;
        case 1:
            $this->player->sendTip(color::GREEN.color::BOLD."§7-= [ §a-".color::GRAY."-- §7] =-");
            break;
        case 2:
            $this->player->sendTip(color::GREEN.color::BOLD."§7-= [ §a--".color::GRAY."-§7 ] =-");            
            break;
        case 3:
            $this->player->sendTip(color::RED.color::BOLD."§7-= [ §a--- §7] =-", "§a§l -- CHARGED -- ");           
            break;
//        case 4:
//            $this->player->sendPopup(color::GREEN."----".color::GRAY."----"); 
//            break;
//        case 5:
//            $this->player->sendPopup(color::GREEN."-----".color::GRAY."---");
//            break;
//        case 6:
//            $this->player->sendPopup(color::GREEN."------".color::GRAY."--");         
//            break;
//        case 7:
//            $this->player->sendPopup(color::GREEN."-------".color::GRAY."-");           
//            break;
//        case 8:
//            $this->player->sendPopup(color::RED."--------");           
//            break;
        }
    }
    
         }