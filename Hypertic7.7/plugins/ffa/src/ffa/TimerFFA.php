<?php
namespace ffa;

use pocketmine\utils\Config;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat as color;
use pocketmine\level\Position;
use pocketmine\Player;
use ffa\Main;
use onebone\economyapi\EconomyAPI;
use ffa\StateFFA;
use pocketmine\item\Item;


class TimerFFA extends PluginTask{
    public $points;
    public $winner;
    public $_t;
    public $_t2;
    public function __construct(Main $plugin) {
        $this->points = array();
       
        $this->plugin = new $plugin;
        parent::__construct($plugin);
        $plugin->getLogger()->info(color::GREEN."timer started!");
    }
    public function onCancel() {
        return;
    }
    public function onRun($Tick) {
        switch (StateFFA::GetState()){
            case 1:
                $this->WAITING();
                break;
            case 2:
                $this->LOADING();
                break;
            case 3:
                $this->INGAME();
                break;
            case 4:
                $this->RESTARTING();
                break;
        }
        
        
    }
    public function WAITING(){
       $inst = $this->owner;
       if($inst instanceof Main){
        $players = $inst->players;
     # # # # #
        foreach ($players as $pl){if($pl instanceof Player){$pl->sendPopup(color::GREEN."§5Waiting for players... ".color::YELLOW.  count($players)."/".Main::MINPLAYERS);}}
     # # # # #
       }
    }
    public function LOADING(){
       $inst = $this->owner;
       if($inst instanceof Main){
        $players = $inst->players;
              $c = $inst->config;
        foreach ($players as $player){
            
            if($player instanceof Player && $c instanceof Config){
              $c->reload();
                $this->points[$player->getName()] = 0;
                $inst->s[$player->getName()] = TRUE;
                $this->GivePlayerItems($player);
                $player->setHealth($player->getMaxHealth());
                $player->sendMessage(color::GREEN."§8> §5Game starting!");
//                $r = rand(1, count($c->getAll()));
//                $ps = $c->get($r);
//                $pos = new Position($ps["x"], $ps["1y"], $ps["z"], $inst->getServer()->getLevelByName($ps["level"]));
//                $player->teleport($pos);
                    $this->TpPlayerToLoc($player);              
            }
        }
        StateFFA::SetState(StateFFA::INGAME);
        
       }
    }
    public function INGAME(){
       $inst = $this->owner;
       if($inst instanceof Main){
        $players = $inst->players;
        $inst->IsLess();
        $this->TryEndGame();
        
        $points = $this->points;
        $point = $this->points;
        arsort($points);
        arsort($point);
        $names = array_keys($point);
        
        foreach ($inst->players as $p){
            if($p instanceof Player){
               $p->sendPopup(color::WHITE."§5Highest§8: ".color::RED.$names[0]."\n".color::RESET."§5You§8: ".color::BLUE.$points[$p->getName()]."  ".color::WHITE."§8[§a30§8] §5Highest§8: ".color::RED.reset($points));
            }
        }
       }
    }
    public function RESTARTING(){
       $inst = $this->owner;
       if($inst instanceof Main){
        $players = $inst->players;
        foreach ($players as $p4){
            if($p4 instanceof Player){
                $inst->s[$p4->getName()] = FALSE;
                
                $p4->setSpawn($inst->getServer()->getDefaultLevel()->getSafeSpawn());
                $p4->kill();
                $p4->getInventory()->clearAll();
            }
        }
      
        ####
       $inst->players = array();
       $this->points = array();
       $this->winner = NULL;
        ####
       
        $inst->getLogger()->info(color::GREEN."game restarted!");
        StateFFA::SetState(1);
       }
    }
    public function TryEndGame(){
        $inst = $this->owner;
        if($inst instanceof Main){
        $w = $this->points;
        arsort($w);
        reset($w);
        foreach ($w as $key => $val){
            if($val >= Main::MAXPOINTS){
                foreach ($inst->players as $pl){
                 if($pl instanceof Player){
                     $pl->teleport($inst->getServer()->getDefaultLevel()->getSafeSpawn());
                     $pl->sendMessage(color::GREEN."§8> §5GG, game finished!");
                     }
                }
                $po = $this->points;
                arsort($po);
                $names = array_keys($po);
                EconomyAPI::getInstance()->addMoney($names[0], Main::REWARD);
                $inst->getServer()->getPlayer($names[0])->sendMessage(color::BLUE."§5rewarded §a$".Main::REWARD);
                $inst->getServer()->broadcastMessage(color::RED."§8> ".color::GREEN.$names[0]."§5 won a FFA game!");
                
                StateFFA::SetState(StateFFA::RESTARTING);
                return;
            }
        }
    }
  }
  public function TpPlayerToLoc(Player $player){
      $inst = $this->owner;
      if($inst instanceof Main){
          $c = $inst->config;
          if($c instanceof Config){
                $r = rand(1, count($c->getAll()));
                $ps = $c->get($r);
                $pos = new Position($ps["x"], $ps["1y"], $ps["z"], $inst->getServer()->getLevelByName($ps["level"]));
                $player->teleport($pos);
                $player->setSpawn($pos);
  }}}
public function GivePlayerItems(Player $player){
    $player->getInventory()->clearAll();
    $i1 = new Item(Item::IRON_SWORD, 0, 1);
    $i2 = new Item(Item::GOLDEN_APPLE, 0, 4);
    $i3 = new Item(Item::BOW, 0, 1);
    $i4 = new Item(Item::ARROW, 0, 20);
    $i5 = new Item(Item::STICK, 0, 1);
    $player->getInventory()->setItem(1, $i1);
    $player->getInventory()->setItem(2, $i2);
    $player->getInventory()->setItem(3, $i3);
    $player->getInventory()->setItem(4, $i4);
    $player->getInventory()->setItem(5, $i5);
    $player->getInventory()->setHelmet(new Item(Item::IRON_HELMET, 0, 1));
    $player->getInventory()->setBoots(new Item(Item::IRON_BOOTS, 0, 1));
    $player->getInventory()->setChestplate(new Item(Item::IRON_CHESTPLATE, 0, 1));
    $player->getInventory()->setLeggings(new Item(Item::IRON_LEGGINGS, 0, 1));
}
}