<?php

namespace Core;

//TODO:
//use pocketmine\event\player\PlayerMoveEvent;
//use pocketmine\level\particle\FlameParticle;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\Plugin;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\IntTag;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\item\Item;
use pocketmine\event\entity\EntityTeleportEvent;

class Main extends PluginBase implements Listener {
	
	public $prefix = C::GRAY."[".C::AQUA."Lobby".C::GRAY."] ";

	public function onEnable() : void{
		$this->ZMusicBox = $this->getServer()->getPluginManager()->getPlugin("ZMusicBox");
		if($this->ZMusicBox instanceof Plugin){
			@mkdir($this->getDataFolder());
			$this->saveResource("config.yml");
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
			$this->getLogger()->info("Core v0.5.0 by FreeGamingHere Enabled!");
			$this->getServer()->getDefaultLevel()->setTime(1000);
			$this->getServer()->getDefaultLevel()->stopTime();
		} else {
			$this->getLogger()->info("ZMusicBox not found! Disabling Core v0.5.0...");
			$this->setEnabled(false);
		}
	}

	public function onDisable() : void{
		$this->getLogger()->info("Core v0.5.0 by FreeGamingHere Disabled!");
	}

	public function mainItems(Player $player) : void{
		$player->getInventory()->clearAll();
		$player->getArmorInventory()->clearAll();
		$player->getInventory()->setItem(0, Item::get(345)->setCustomName(C::RESET.C::BOLD.C::GREEN."Teleporter"));
		$player->getInventory()->setItem(2, Item::get(339)->setCustomName(C::RESET.C::BOLD.C::GOLD."Info"));
		$player->getInventory()->setItem(4, Item::get(288)->setCustomName(C::RESET.C::BOLD.C::GREEN."Enable your Fly"));
		$player->getInventory()->setItem(6, Item::get(280)->setCustomName(C::RESET.C::BOLD.C::YELLOW."Hide ".C::GREEN."Players"));
		$player->getInventory()->setItem(8, Item::get(360)->setCustomName(C::RESET.C::BOLD.C::GREEN."Next Song"));
		$player->removeAllEffects();
		$player->setHealth(20);
		$player->setFood(20);
	}
	
	public function teleportItems(Player $player) : void{
		$player->getInventory()->clearAll();
		$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$game1 = $cfg->get("Game-1-Name");
		$game2 = $cfg->get("Game-2-Name");
		$game3 = $cfg->get("Game-3-Name");
		$player->getInventory()->setItem(0, Item::get(399)->setCustomName(C::RESET.$game1));
		$player->getInventory()->setItem(2, Item::get(378)->setCustomName(C::RESET.$game2));
		$player->getInventory()->setItem(4, Item::get(381)->setCustomName(C::RESET.$game3));
		$player->getInventory()->setItem(8, Item::get(355)->setCustomName(C::RESET.C::BOLD.C::RED."Back"));
		$player->removeAllEffects();
		$player->setHealth(20);
		$player->setFood(20);
	}

	public function onJoin(PlayerJoinEvent $event) : void{
		$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$player = $event->getPlayer();
		$ds = $this->getServer()->getDefaultLevel()->getSafeSpawn();
		$x = $ds->getX() + 0.5;
		$y = $ds->getY() + 0.5;
		$z = $ds->getZ() + 0.5;
		$player->setGamemode((int)$cfg->get("DefaultGamemode"));
		$player->teleport(new Vector3($x, $y, $z));
		$this->mainItems($player);
		if($player->isOp()){
			$event->setJoinMessage(C::GREEN.$player->getName().C::AQUA." has joined the game!");
		} else {
			$event->setJoinMessage("");
		}
	}

	public function onQuit(PlayerQuitEvent $event) : void{
		$player = $event->getPlayer();
		if($player->isOp()){
			$event->setQuitMessage(C::GREEN.$player->getName().C::AQUA." has left the game!");
		} else {
			$event->setQuitMessage("");
		}
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{

		$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$server = $cfg->get("info-server-cmd");
		$ranks = $cfg->get("info-ranks-cmd");
		if($command->getName() === "info"){
			if(!empty($args[0]) && count($args) === 1){
				if($args[0] === "ranks"){
					$sender->sendMessage($this->prefix . $ranks);
					return true;
				}
				if($args[0] === "server"){
					$sender->sendMessage($this->prefix . $server);
					return true;
				} else {
					$sender->sendMessage($this->prefix . C::GREEN . "Usage: /info <ranks|server>");
					return true;
				}
			} else {
				$sender->sendMessage($this->prefix . C::GREEN . "Usage: /info <ranks|server>");
				return true;
			}
		} 
	}

	public function onInteract(PlayerInteractEvent $event) : void{
		$player = $event->getPlayer();
		$name = $player->getName();
		$item = $player->getInventory()->getItemInHand();
		$itemid = $item->getID();
		$block = $event->getBlock();
		$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$game1 = $cfg->get("Game-1-Name");
		$game2 = $cfg->get("Game-2-Name");
		$game3 = $cfg->get("Game-3-Name");

		if($item->getName() === C::RESET.C::BOLD.C::GREEN."Teleporter"){
			$this->teleportItems($player);
			
		} elseif ($item->getName() === C::RESET.C::BOLD . C::GOLD."Info"){
			$player->sendMessage($this->prefix . C::GREEN . "Usage: /info <ranks|server>");
			
		} elseif ($item->getName() === C::RESET.C::BOLD.C::GREEN."Enable your Fly"){
			$player->setAllowFlight(true);
			$player->setFlying(true);
			$player->sendMessage($this->prefix . C::GREEN . "You disabled your fly!");
			$player->getInventory()->setItem(4, Item::get(288)->setCustomName(C::RESET.C::BOLD.C::RED."Disable your Fly"));
			
		} elseif ($item->getName() === C::RESET.C::BOLD.C::RED."Disable your Fly"){
			$player->setAllowFlight(false);
			$player->setFlying(false);
			$player->sendMessage($this->prefix . C::RED . "You disabled your fly!");
			$player->getInventory()->setItem(4, Item::get(288)->setCustomName(C::RESET.C::BOLD.C::GREEN."Enable your Fly"));
			
		} elseif ($item->getName() === C::RESET.C::BOLD.C::RED."Back"){
			$this->mainItems($player);
			
		} elseif ($item->getName() === C::RESET.$game1){
			$this->mainItems($player);
			$x = $cfg->get("Game-1-X");
			$y = $cfg->get("Game-1-Y");
			$z = $cfg->get("Game-1-Z");
			$player->teleport(new Vector3($x, $y, $z));
			
		} elseif ($item->getName() === C::RESET.$game2){
			$this->mainItems($player);
			$x = $cfg->get("Game-2-X");
			$y = $cfg->get("Game-2-Y");
			$z = $cfg->get("Game-2-Z");
			$player->teleport(new Vector3($x, $y, $z));
			
		} elseif ($item->getName() === C::RESET.$game3){
			$this->mainItems($player);
			$x = $cfg->get("Game-3-X");
			$y = $cfg->get("Game-3-Y");
			$z = $cfg->get("Game-3-Z");
			$player->teleport(new Vector3($x, $y, $z));
			
		} elseif ($item->getName() === C::RESET.C::BOLD.C::YELLOW."Hide ".C::GREEN."Players") {
			$player->getInventory()->remove(Item::get(280)->setCustomName(C::RESET.C::BOLD.C::YELLOW."Hide ".C::GREEN."Players"));
			$player->getInventory()->setItem(6, Item::get(369)->setCustomName(C::RESET.C::BOLD.C::YELLOW."Show ".C::GREEN."Players"));
			$player->sendMessage($this->prefix . C::RED . "Disabled Player Visibility!");
			$this->hideall[] = $player;
			foreach ($this->getServer()->getOnlinePlayers() as $p2) {
				$player->hideplayer($p2);
			}
			
		} elseif ($item->getName() === C::RESET.C::BOLD.C::YELLOW."Show ".C::GREEN."Players"){
			$player->getInventory()->remove(Item::get(369)->setCustomName(C::RESET.C::BOLD.C::YELLOW."Show ".C::GREEN."Players"));
			$player->getInventory()->setItem(6, Item::get(280)->setCustomName(C::RESET.C::BOLD.C::YELLOW."Hide ".C::GREEN."Players"));
			$player->sendMessage($this->prefix . C::GREEN . "Enabled Player Visibility!");
			unset($this->hideall[array_search($player, $this->hideall)]);
			foreach ($this->getServer()->getOnlinePlayers() as $p2) {
				$player->showplayer($p2);
			}
			
		} elseif ($item->getName() === C::RESET.C::BOLD.C::GREEN."Next Song"){
			$this->ZMusicBox->StartNewTask();
			
		}
	}

	public function onItemHeld(PlayerItemHeldEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$item = $player->getInventory()->getItemInHand()->getID();
		switch($item){
			case 10:
				$player->getInventory()->setItemInHand(Item::get(Item::AIR, 0, 0));
				$player->sendMessage($this->prefix . C::RED . "You are not allowed to use lava");
			break;
			case 11:
				$player->getInventory()->setItemInHand(Item::get(Item::AIR, 0, 0));
				$player->sendMessage($this->prefix . C::RED . "You are not allowed to use lava");
			break;
		}
	}
}
