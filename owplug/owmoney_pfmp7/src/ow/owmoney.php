<?php

namespace ow;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\particle\PortalParticle;
use pocketmine\level\sound\ClickSound;
use pocketmine\level\sound\AnvilUseSound;
use pocketmine\level\sound\BatSound;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextFormat as F;
use pocketmine\level\particle\AngryVillagerParticle;
use pocketmine\level\particle\WaterDripParticle;
use pocketmine\entity\Effect;
use pocketmine\block\Block;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\level\particle\ExplodeParticle;
use pocketmine\utils\Config;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\level\particle\ItemBreakParticle;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;

class owmoney extends PluginBase implements Listener {
	
	public function onEnable() {
		$this->owp = $this->getServer()->getPluginManager()->getPlugin("owperms");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function onJoin(PlayerJoinEvent $e) {
		$player = $e->getPlayer();
		$this->why($player->getName());
	}
	
	public function why($playerName){
 		if(!is_file($this->getDataFolder()."players/".$playerName.".yml")){
			$this->createData($playerName);
		}
	}
	
	public function createData($playerName) {
        if(!is_file($this->getDataFolder()."players/".$playerName.".yml")){
            @mkdir($this->getDataFolder() . "players/");
		    $data = new Config($this->getDataFolder() . "players/".$playerName.".yml", Config::YAML);
		    $data->set("money", 0);
		    $data->save();
		}
	}
	
	public function getMoney($playerName) {
		$this->why($playerName);
        $sFile = (new Config($this->getDataFolder() . "players/".$playerName.".yml", Config::YAML))->getAll();
        return $sFile["money"];
	}
	
	public function addMoney($playerName, $count) {
		$this->why($playerName);
        $sFile = (new Config($this->getDataFolder() . "players/".$playerName.".yml", Config::YAML))->getAll();
        $sFile["money"] = $sFile["money"] += $count;
      	$fFile = new Config($this->getDataFolder() . "players/".$playerName.".yml", Config::YAML);
	    $fFile->setAll($sFile);
        $fFile->save();
	}
	
	public function removeMoney($playerName, $count) {
		$this->why($playerName);
        $sFile = (new Config($this->getDataFolder() . "players/".$playerName.".yml", Config::YAML))->getAll();
        $sFile["money"] = $sFile["money"] -= $count;
      	$fFile = new Config($this->getDataFolder() . "players/".$playerName.".yml", Config::YAML);
	    $fFile->setAll($sFile);
        $fFile->save();
	}
	
	public function setMoney($playerName, $count) {
		$this->why($playerName);
        $sFile = (new Config($this->getDataFolder() . "players/".$playerName.".yml", Config::YAML))->getAll();
        $sFile["money"] = $count;
      	$fFile = new Config($this->getDataFolder() . "players/".$playerName.".yml", Config::YAML);
	    $fFile->setAll($sFile);
        $fFile->save();
	}
 
}