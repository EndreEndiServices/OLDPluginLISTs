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

class owshop extends PluginBase implements Listener {
	
	public function onEnable() {
		$this->owm = $this->getServer()->getPluginManager()->getPlugin("owmoney");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
        if(!is_file($this->getDataFolder()."shopCfg/prices.yml")){
            @mkdir($this->getDataFolder() . "shopCfg/");
		    $data = new Config($this->getDataFolder() . "shopCfg/prices.yml", Config::YAML);
		    $data->save();
		}
	}
	
    public function getDataPrice($id){
        $sFile = (new Config($this->getDataFolder() . "shopCfg/prices.yml", Config::YAML))->getAll();
        return $sFile[$id];
    }
	
	public function available($param){
 		$sFile = (new Config($this->getDataFolder() . "shopCfg/prices.yml", Config::YAML))->getAll();
		if(isset($sFile[$param])){
			return true;
		} else {
			return false;
		}
	}
	
	public function createPrice($id, $price) {
		$data = new Config($this->getDataFolder() . "shopCfg/prices.yml", Config::YAML);
		$data->set((int) $id, (int) $price);
        $data->save();
	}
	
	public function giveItem($name, $id, $count) {
		$name->getInventory()->addItem(new Item($id, 0, $count));
	}
	
	public function shopping($player, $id, $count) {
		if($this->available($id)){
			if($this->owm->getMoney($player->getName()) >= $this->getDataPrice($id) * $count) {
				$price = $this->getDataPrice($id) * $count;
				$this->owm->removeMoney($player->getName(), $price);
				$this->giveItem($player, $id, $count);
				$player->sendMessage(F::YELLOW. "[OWShop]" .F::GOLD. " Ты купил предмет с ID " .F::DARK_GREEN. $id. F::GOLD. " за " .F::GREEN. $price .F::GOLD. " монет в количестве: " .F::DARK_GREEN. $count);
			} else {
				$player->sendMessage(F::YELLOW. "[OWShop]" .F::GOLD. " На вашем счету недостаточно денег. Указанный предмет стоит: " .F::DARK_GREEN. $this->getDataPrice($id) .F::GOLD. " монет.");
			}
		} else {
			$player->sendMessage(F::YELLOW. "[OWShop]" .F::GOLD. " Предмет с ID " .F::DARK_GREEN. $id. F::GOLD. " не существует.");
		}
	}

 
}