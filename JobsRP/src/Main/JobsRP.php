<?php
# Check ReadMe =)

  namespace Main;
  
use pocketmine\plugin\PluginBase;
    use pocketmine\event\Listener;
    
/* **** Events ****  */

use pocketmine\event\player\PlayerDeathEvent;
    use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent;
    use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntiyDamageByEntityEvent;
    
/* **** Math **** */

use pocketmine\math\Vector3;

/* **** Items and e.t.c. **** */

use pocketmine\item\Item;
    use pocketmine\block\Block;
use pocketmine\inventory\Inventory;

/* **** NBT TAGS **** */

  use pocketmine\nbt\tags\NameTag;
  
  /* **** Cfg **** 

use pocketmine\utils\Config; */

/* **** Commands **** */
use pocketmine\command\CommandSender;
    use pocketmine\command\Command;
  
/* **** Others **** */

use pocketmine\level\Level;
    use pocketmine\Player;
use pocketmine\Server;
    use pocketmine\permissible\Permissible;
use pocketmine\enchantment\Enchantment;
    use pocketmine\level\Position;
    /* **** End Imports **** */
    
    class JobsRP extends PluginBase implements Listener {
    	
const LEAVE = "§f(§6Работы§f) §a✔ §7Ты §fуволился §7с работы!";
const DJ = "§f(§6Работы§f) §c✖ §7Ты ещё §4не §7устроился!";
    public $eco, $cfg, $miner = [], $treecutter = [], $killer = [], $builder = [], $gardener = [];
    
public function onEnable() {
	
	$this->getLogger()->info("§f<§eEvilJobs§f> §7успешный запуск плагина! Автор: artem_kosh сделано специально для ExtraPlugois");
	    $this->getLogger()->warning("§f<§eJobsRP§f> §7Данная версия совершенно бесплатна!");
	$this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->eco = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
                $this->wg = $this->getServer()->getPluginManager()->getPlugin("WorldGuardian");
                    if(empty($this->wg)) {
                    	 $this->getLogger()->warning("§f<§eJobsRP§f> §7Плагин WorldGuardian §cне найден, Проверка на приват отключена");
                    }
    /*        @mkdir($this->getDataFolder());
        $this->cfg = new Config($this->getDataFolder(). "jobitems.yml", Config::YAML);
        $this->cfg->save(); */
}

  public function Break(BlockBreakEvent $e) {
      $p = $e->getPlayer();
      $b = $e->getBlock();
$x = round($b->getX());
        $y = round($b->getY());
        $z = round($b->getZ());
        $level = $b->getLevel()->getName();
     if(isset($this->miner[strtolower($p->getName())])) {
      	        $ids = [1, 2, 3, 12, 13];
              $ids2 = [14, 15, 16, 56, 73, 129];
        	if(!$p->isCreative() && in_array($b->getId(), $ids)) {
             if(empty($this->wg->regionHere($x, $y, $z, $level)) || $this->wg == null) {
        $this->eco->addMoney($p, 1);
            $p->sendPopup("§e+ 1§6$");
                }
            } elseif(!$p->isCreative() && in_array($b->getId(), $ids2)) {
          	 if(empty($this->wg->regionHere($x, $y, $z, $level)) || $this->wg == null) {
            	$this->eco->addMoney($p, 5);
            $p->sendPopup("§e+ 5§6$");
               }
            }
        }
          
       if(isset($this->gardener[strtolower($p->getName())])) {
     
if(!$p->isCreative() && $b->getId() == "18") {
   if(empty($this->wg->regionHere($x, $y, $z, $level)) || $this->wg == null) {
     $this->eco->addMoney($p, 4);
          $p->sendTip("§e+ 4§6$");
     }
   }
       }
     if(isset($this->treecutter[strtolower($p->getName())])) {
        if(!$p->isCreative() && $b->getId() == "17") {
        	 if(empty($this->wg->regionHere($x, $y, $z, $level)) || $this->wg == null) {
        	  $this->eco->addMoney($p, 3);
        $p->sendTip("§e+ 3§6$");
      }
   } 
      }
      }
  public function Place(BlockPlaceEvent $e) {
  	
  foreach($this->getServer()->getOnlinePlayers() as $p) {
  $b = $e->getBlock();
$x = round($b->getX());
        $y = round($b->getY());
        $z = round($b->getZ());
        $level = $b->getLevel()->getName();
    
    if(!$p->isCreative()) {
if(isset($this->builder[strtolower($p->getName())])) {
	  $id = [1, 2, 3, 4, 5, 24, 35, 41, 42, 43, 44, 45, 97, 98, 99, 100];
if(!$p->isCreative() && in_array($b->getId(), $id)) {
	 if(empty($this->wg->regionHere($x, $y, $z, $level)) || $this->wg == null) {
	  $this->eco->addMoney($p, 2);
	$p->sendTip("§e+ 2§6$");
	}
}
  }
    }
    }
    }
	public function onDeath(PlayerDeathEvent $e) {
				
	$entity = $e->getEntity();
  if(isset($this->killer[strtolower($p->getName())])) {
	$cause = $entity->getLastDamageCause();
	if($cause instanceof EntityDamageByEntityEvent) {
			
		$p = $cause->getDamager();
			
		if($p instanceof Player) {
			
			  if(!$p->isCreative() && $entity instanceof Player) {
				$this->eco->addMoney($p, 50);
			$p->sendTip("§e+ 50§6$");
			}
	    }
	  }
  }
}
		
		
 public function onCommand(CommandSender $p, Command $cmd, $label, array $args) {
 	switch($cmd->getName()) {
 	
case "job":
if(!isset($args[0])) {
  $p->sendMessage("§f(§6Работы§f) §7Список команд: §e/job info");
  }
  if(isset($args[0])) {
  	if($args[0] == "info") {
  $p->sendMessage("§7========= §cРаботы §7=========");
  $p->sendMessage(" §b/job list §f- Список работ");
  $p->sendMessage(" §b/job help §f- Подробнее о работах");
  }
  	if($args[0] == "list") {
  	    $p->sendMessage("§e✳ §3/police §f– §7Устроиться полицейским\n§e✳ §3/terrorist §f– §7Стать террористом\n§e✳ §3/killer §f– §7Устроиться убийцей\n§e✳ §3/dvornik §f– §7Устроиться Дворником\n§e✳ §3/ychitel §f– §7Устроиться Учителем\n§e✴ §3/job leave §f– §7Уволиться с работы");
  }
      if($args[0] == "help") {
   $p->sendMessage("§6police §7(Полиция) §fУбивайте плохих людей и за это вы будете получать деньги");
   $p->sendMessage("§6Terrorist §7(Террорист) §f Подорви какую нибудь школу и получи за это деньги. ТНТ можно украсть в магазине!");
   $p->sendMessage("§6Dvornik §7(Дворник) §fУбирай снег и траву с дорог и получай за это деньги.");
   $p->sendMessage("§6Killer §7(Убийца) §f– Убивай людей по своему желанию или закажу, и получай за это деньги");
     $p->sendMessage("§6Ychitel §7(Учитель) §fОбучай детей как выживать в этом сувором мире!");
  }
  if($args[0] == "leave") {
$nick = strtolower($p->getName());
if(isset($this->police[$nick]) || isset($this->terrorist[$nick]) || isset($this->ychitel[$nick]) || isset($this->killer[$nick]) || isset($this->dvornik[$nick])) {
	unset($this->police[$nick], $this->terrorist[$nick], $this->dvornik[$nick], $this->killer[$nick], $this->gardener[$nick]);
	$p->sendMessage(self::LEAVE);
  } else {
  	$p->sendMessage(self::DJ);
  }
    }
       }
    break;

  case "police":

if(isset($this->police[strtolower($p->getName())]) || isset($this->killer[strtolower($p->getName())]) || isset($this->ychitel[strtolower($p->getName())]) || isset($this->terrorist[strtolower($p->getName())])) {
 $p->sendMessage("§f(§6Работы§f) §c✖ §7Сначала уволься с другой работы!");
}

elseif(isset($this->police[strtolower($p->getName())])) {
	  $p->sendMessage("§f(§6Работы§f) §e✳ §7Ты уже работаешь §aВ полиции!");
	} else {
		$this->police[strtolower($p->getName())] = true;
		  $p->sendMessage("§f(§6Работы§f) §a✔ §7Ты устроился §fв полицию!");
		}
		break;
		
    case "dvornik":
    
 if($this-police[strtolower($p->getName())] != null || $this->killer[strtolower($p->getName())] != null || $this->ychitel[strtolower($p->getName())] != null || $this->terrorist[strtolower($p->getName())] != null) {
	 $p->sendMessage("§f(§6Работы§f) §c✖ §7Сначала уволься с другой работы!");
}

elseif(isset($this->dvornik[strtolower($p->getName())])) {
	$p->sendMessage("§f(§6Работы§f) §e✳ §7Ты уже работаешь §aТеррористом!");

	} else {
		$this->dvornik[strtolower($p->getName())] = true;
		  $p->sendMessage("§f(§6Работы§f) §a✔ §7Ты устроился §fДворником!");
		}
		break;
		
		case "killer":
		
if(isset($this->dvornik[strtolower($p->getName())]) || isset($this->police[strtolower($p->getName())]) || isset($this->ychitel[strtolower($p->getName())]) || isset($this->terrorist[strtolower($p->getName())])) {
	 $p->sendMessage("§f(§6Работы§f) §c✖ §7Сначала уволься с другой работы!");
}

elseif(isset($this->killer[strtolower($p->getName())])) {
	$p->sendMessage("§f(§6Работы§f) §e✴ §7Ты уже работаешь §aкиллером!");
	} else {
		$this->killer[strtolower($p->getName())] = true;
		  $p->sendMessage("§f(§6Работы§f) §a✔ §7Ты устроился §fКиллером!");
		}
		break;
 
     case "terrorist":
    
  if(isset($this->dvornik[strtolower($p->getName())]) || isset($this->killer[strtolower($p->getName())]) || isset($this->ychitel[strtolower($p->getName())]) || isset($this->police[strtolower($p->getName())])) {
	 $p->sendMessage("§f(§6Работы§f) §c✖ §7Сначала уволься с другой работы!");
	}
elseif(isset($this->terrorist[strtolower($p->getName())])) {
	$p->sendMessage("§f(§6Работы§f) §e✴ §7Ты уже работаешь §aТеррористом!");
	} else {
		$this->terrorist[strtolower($p->getName())] = true;
		  $p->sendMessage("§f(§6Работы§f) §a✔ §7Ты устроился §fТерристом!");
		}
		break;
		
		case "ychitel":
		
if(isset($this->terrorist[strtolower($p->getName())]) || isset($this->killer[strtolower($p->getName())]) || isset($this->killer[strtolower($p->getName())]) || isset($this->terrorist[strtolower($p->getName())])) {
	 $p->sendMessage("§f(§6Работы§f) §c✖ §7Сначала уволься с другой работы!");
}

elseif(isset($this->ychitel[strtolower($p->getName())])) {
	$p->sendMessage("§f(§6Работы§f) §e✴ §7Ты уже работаешь §aучителем!");
	} else {
		$this->ychitel[strtolower($p->getName())] = true;
		  $p->sendMessage("§f(§6Работы§f) §a✔ §7Ты устроился §fУчителем!");
		}
		break;
		}
    }
  }
   
		
?>
            