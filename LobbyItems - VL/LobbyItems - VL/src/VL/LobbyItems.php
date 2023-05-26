<?php

namespace VL;

use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\level\Location;
use pocketmine\level\particle\BubbleParticle;
use pocketmine\level\particle\ExplodeParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\HugeExplodeParticle;
use pocketmine\level\Position;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\utils\Terminal;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\Player;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\utils\Config;
use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\utils\TextFormat;
use pocketmine\item\Item;
use pocketmine\entity\Item as ItemEntity;
use pocketmine\math\Vector3;
use pocketmine\math\Vector2;

use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\particle\PortalParticle;

use pocketmine\level\sound\PopSound;
use pocketmine\level\sound\GhastSound;

use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class LobbyItems extends PluginBase implements Listener
{

	public $prefix = TextFormat::YELLOW . "Vastlabs" . TextFormat::GRAY . " | " . TextFormat::WHITE;
	public $heart = array("Hearth111");
	public $jump = array("Jump222");
	public $speed = array("Speed333");
	public $water = array("Water444");

	public $showall = array("1234567890PLAYER");
	public $showvips = array("1234567890PLAYER");
	public $shownone = array("1234567890PLAYER");

	// Paticles

	public $particle1 = array("ParticleRot");
	public $particle2 = array("ParticleGelb");
	public $particle3 = array("ParticleGruen");
	public $particle4 = array("ParticleBlau");
	public $particle5 = array("ParticleRegenbogen");
	public $particle6 = array("ParticleFire");
	public $particle7 = array("Particle");
	public $particle8 = array("ParticleDrops");
	public $particle9 = array("ParticleEnderDrops");

	public $buildmode = array("CONSOLE");

	/*
	public $particle = array("Particle");
	*/

	public function onEnable()
	{

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info(TextFormat::GREEN . "Aktiviert");
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new ItemsLoad($this), 10);

		$this->getServer()->getScheduler()->scheduleRepeatingTask(new TypeType($this), 20);

		$this->getServer()->getNetwork()->setName(TextFormat::BOLD . TextFormat::GREEN . "VastLabs");

		$this->getServer()->getDefaultLevel()->setTime(1000);
		$this->getServer()->getDefaultLevel()->stopTime();

		@mkdir($this->getDataFolder());
		$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

		$config->set("OpenChest1", false);
		$config->set("OpenChest2", false);
		$config->save();

	}

	public function onDisable()
	{

		$this->getLogger()->info(TextFormat::RED . "Deaktiviert");

	}

	public function onProjectileLaunch(ProjectileLaunchEvent $event)
	{

		$event->setCancelled();

	}

	public function onCommandUse(PlayerCommandPreprocessEvent $event): bool
	{
		$player = $event->getPlayer();
		$name = $player->getName();

		$msg = strtolower($event->getMessage());
		$args = explode(" ", $event->getMessage());

		if ($msg{0} == "/") {

			if (strtolower($args[0]) == "/help" || strtolower($args[0]) == "/?") {

				$event->setCancelled();

				$player->sendMessage(TextFormat::GRAY . "=====]" . TextFormat::RED . "Commands" . TextFormat::GRAY . "[=====");
				$cmd = array("help" => "Die Hilfsseite",
					"login" => "Der Login Command",
					"register" => "Der Register Command",
					"lobby" => "Teleportiere dich zur Lobby",
				);
				foreach ($cmd as $command => $info) {
					$player->sendMessage(TextFormat::GRAY . "/" . TextFormat::GOLD . $command . TextFormat::GRAY . " | " . TextFormat::GREEN . $info);
				}
				$player->sendMessage(TextFormat::GRAY . "=====]" . TextFormat::RED . "Commands" . TextFormat::GRAY . "[=====");
				return true;

			}


		}
		return true;
	}

	public function onPickup(InventoryPickupItemEvent $event)
	{
		$player = $event->getInventory()->getHolder();
		$defaultlevel = $this->getServer()->getDefaultLevel();
		$event->setCancelled();
	}

	public function onDrop(PlayerDropItemEvent $event)
	{
		$player = $event->getPlayer();
		$defaultlevel = $this->getServer()->getDefaultLevel();
		$event->setCancelled();
	}

	public function onHunger(PlayerExhaustEvent $event)
	{
		$event->setCancelled(true);
	}

	public function getParticleItems(Player $player)
	{
		$inv = $player->getInventory();
		$inv->clearAll();

		$rot = Item::get(351, 1, 1);
		$rot->setCustomName(TextFormat::RESET . TextFormat::RED . "Rote " . TextFormat::GOLD . "Partikel");

		$blau = Item::get(351, 4, 1);
		$blau->setCustomName(TextFormat::RESET . TextFormat::BLUE . "Blaue " . TextFormat::GOLD . "Partikel");

		$gelb = Item::get(351, 11, 1);
		$gelb->setCustomName(TextFormat::RESET . TextFormat::YELLOW . "Gelbe " . TextFormat::GOLD . "Partikel");

		$gruen = Item::get(351, 2, 1);
		$gruen->setCustomName(TextFormat::RESET . TextFormat::GREEN . "Gruene " . TextFormat::GOLD . "Partikel");

		$rg = Item::get(351, 14, 1);
		$rg->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Orange " . TextFormat::GOLD . "Partikel");

		$fire = Item::get(377, 0, 1);
		$fire->setCustomName(TextFormat::RESET . TextFormat::RED . "Feuer " . TextFormat::GOLD . "Partikel");

		$site2 = Item::get(281, 0, 1);
		$site2->setCustomName(TextFormat::RESET . TextFormat::GRAY . "Partikel Seite 2 | " . TextFormat::RED . "BALD");

		$exit = Item::get(351, 1, 1);
		$exit->setCustomName(TextFormat::RESET . TextFormat::RED . "Exit");

		$inv->setItem(0, $rot);
		$inv->setItem(1, $blau);
		$inv->setItem(2, $gruen);
		$inv->setItem(3, $gelb);
		$inv->setItem(4, $rg);
		$inv->setItem(5, $fire);
		$inv->setItem(7, $site2);
		$inv->setItem(8, $exit);

	}

	public function getTeleporter(Player $player)
	{
		$inv = $player->getInventory();
		$inv->clearAll();

		$exit = Item::get(351, 1, 1);
		$exit->setCustomName(TextFormat::RESET . TextFormat::RED . "Exit");

		$Skyblock = Item::get(2, 1, 1);
		$Skyblock->setCustomName(TextFormat::RESET . TextFormat::GOLD . "SkyBlock");

		$Faction = Item::get(322, 1, 1);
		$Faction->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Faction");

		$Citybuild = Item::get(138, 1, 1);
		$Citybuild->setCustomName(TextFormat::RESET . TextFormat::GOLD . "CityBuild");

		$sur = Item::get(103, 1, 1);
		$sur->setCustomName(TextFormat::RESET . TextFormat::GREEN . "Survival");

		$inv->setItem(8, $exit);
		$inv->setItem(0, $Citybuild);
		$inv->setItem(1, $Skyblock);
		$inv->setItem(2, $Faction);
		//$inv->setItem(4, $sur);

	}

	public function getLobbys(Player $player)
	{
		$inv = $player->getInventory();
		$inv->clearAll();

		$lobby1 = Item::get(42, 0, 1);
		$lobby1->setCustomName(TextFormat::GRAY . "survival " . TextFormat::BOLD . TextFormat::GOLD . "1");

		$lobby2 = Item::get(42, 0, 1);
		$lobby2->setCustomName(TextFormat::GRAY . "soon " . TextFormat::BOLD . TextFormat::GOLD . "2");

		$prelobby = Item::get(41, 0, 1);
		$prelobby->setCustomName(TextFormat::GOLD . "faction Lobby");

		$exit = Item::get(351, 1, 1);
		$exit->setCustomName(TextFormat::RESET . TextFormat::RED . "Exit");

		$inv->setItem(0, $CityBuild);
		$inv->setItem(1, $Skyblock);
		$inv->setItem(7, $prelobby);

		$inv->setItem(8, $exit);

	}

	public function getBigItems(Player $player)
	{
		$inv = $player->getInventory();
		$inv->clearAll();

		$item1 = Item::get(131, 0, 1);
		$item1->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Klein");

		$item2 = Item::get(131, 0, 1);
		$item2->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Normal");

		$item3 = Item::get(131, 0, 1);
		$item3->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Groß");

		$exit = Item::get(351, 1, 1);
		$exit->setCustomName(TextFormat::RESET . TextFormat::RED . "Exit");

		$inv->setItem(1, $item1);
		$inv->setItem(4, $item2);
		$inv->setItem(7, $item3);
		$inv->setItem(8, $exit);

	}

	public function onPreLogin(PlayerPreLoginEvent $event)
	{
		$player = $event->getPlayer();
		$name = $player->getName();
		$ip = $player->getAddress();
		$cid = $player->getClientId();

		if (!$player->isWhitelisted($name)) {
			$msg =
				TextFormat::BOLD . TextFormat::GRAY . "+++-----------+++-----------+++\n" .
				TextFormat::RESET . TextFormat::GOLD . "VastLabs.eu " . TextFormat::GRAY . "|" . TextFormat::RED . " WhiteListed\n" .
				TextFormat::GOLD . "Wir sind in WartungsArbeiten...";
			$player->close("", $msg);
		}

	}

	public function onHit(EntityDamageEvent $event)
	{
		$entity = $event->getEntity();

		if ($entity instanceof Player) {
			if ($event instanceof EntityDamageByEntityEvent) {
				$damager = $event->getDamager();
				if ($damager instanceof Player) {
					if ($entity->getLevel()->getFolderName() == $this->getServer()->getDefaultLevel()->getFolderName()) {
						$event->setCancelled();
					}
				}
			}
		}
	}

	public function noInvMove(InventoryTransactionEvent $event)
	{
		$event->setCancelled(true);
	}

	public function onDamage(EntityDamageEvent $event)
	{
		$player = $event->getEntity();

		if ($player->getLevel()->getFolderName() == $this->getServer()->getDefaultLevel()->getFolderName()) {
			if ($player instanceof Player) {

				$event->setCancelled();

			}

		}
	}

	public function getCosmetics(Player $player)
	{
		$inv = $player->getInventory();
		$inv->clearAll();

		$item1 = Item::get(317, 0, 1);
		$item1->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Boots");

		$item2 = Item::get(372, 0, 1);
		$item2->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Partikel");

		$item3 = Item::get(131, 0, 1);
		$item3->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Groeße");

		$item4 = Item::get(288, 0, 1);
		$item4->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Fly");

		$item5 = Item::get(421, 0, 1);
		$item5->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Nick");

		$exit = Item::get(351, 1, 1);
		$exit->setCustomName(TextFormat::RESET . TextFormat::RED . "Exit");

		$inv->setItem(0, $item1);
		$inv->setItem(1, $item2);
		$inv->setItem(2, $item3);
		$inv->setItem(3, $item4);
		$inv->setItem(4, $item5);
		$inv->setItem(8, $exit);

	}

	public function getRangMenu(Player $player)
	{
		$inv = $player->getInventory();
		$inv->clearAll();

		$item1 = Item::get(265, 0, 1);
		$item1->setCustomName(TextFormat::RESET . TextFormat::GOLD . "VIP - 10€ - 6 Monate");

		$item2 = Item::get(266, 0, 1);
		$item2->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Hero - 15€");

		$item3 = Item::get(264, 0, 1);
		$item3->setCustomName(TextFormat::RESET . TextFormat::GOLD . "VIP - 15€ - LifeTime");

		$exit = Item::get(351, 1, 1);
		$exit->setCustomName(TextFormat::RESET . TextFormat::RED . "Exit");

		$inv->setItem(0, $item1);
		$inv->setItem(1, $item2);
		$inv->setItem(2, $item3);
		$inv->setItem(8, $exit);

	}

	public function getItems(Player $player)
	{
		$name = $player->getName();
		$inv = $player->getInventory();
		$inv->clearAll();

		$item1 = Item::get(368, 0, 1);
		$item1->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Teleporter");

		$item2 = Item::get(130, 0, 1);
		$item2->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Cosmetics");

		$item3 = Item::get(264, 0, 1);
		$item3->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Rang Info");

		if (!in_array($name, $this->showall) && !in_array($name, $this->showvips) && !in_array($name, $this->shownone)) {

			$this->showall[] = $name;

		}

		if (in_array($name, $this->showall)) {

			$item4 = Item::get(351, 10, 1);
			$item4->setCustomName(TextFormat::RESET . TextFormat::GREEN . "Alle Spieler sichtbar");

		} elseif (in_array($name, $this->showvips)) {

			$item4 = Item::get(351, 5, 1);
			$item4->setCustomName(TextFormat::RESET . TextFormat::DARK_PURPLE . "Nur VIPs sichtbar");

		} elseif (in_array($name, $this->shownone)) {

			$item4 = Item::get(351, 8, 1);
			$item4->setCustomName(TextFormat::RESET . TextFormat::GRAY . "Keine Spieler sichtbar");

		}
		$inv->setItem(1, $item2);
		$inv->setItem(3, $item1);
		$inv->setItem(5, $item3);
		$inv->setItem(7, $item4);

	}

	public function onPlace(BlockPlaceEvent $event)
	{
		$name = $event->getPlayer()->getName();
		if (!in_array($name, $this->buildmode)) {

			$event->setCancelled();

		}
	}

	public function onBreak(BlockBreakEvent $event)
	{
		$name = $event->getPlayer()->getName();
		if (!in_array($name, $this->buildmode)) {

			$event->setCancelled();

		}
	}

	public function onJoin(PlayerJoinEvent $event)
	{
		$player = $event->getPlayer();
		$name = $player->getName();
		$this->getItems($player);

		$event->setJoinMessage("");
		$event->getPlayer()->setFood("20");
		$player->setGamemode(2);

		//$this->getItems($player);

		$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
		$this->getServer()->getDefaultLevel()->loadChunk($spawn->getFloorX(), $spawn->getFloorZ());
		$player->teleport($spawn, 0, 0);

		if (in_array($name, $this->particle1)) {
			unset($this->particle1[array_search($name, $this->particle1)]);
		} elseif (in_array($name, $this->particle2)) {
			unset($this->particle2[array_search($name, $this->particle2)]);
		} elseif (in_array($name, $this->particle3)) {
			unset($this->particle3[array_search($name, $this->particle3)]);
		} elseif (in_array($name, $this->particle4)) {
			unset($this->particle4[array_search($name, $this->particle4)]);
		} elseif (in_array($name, $this->particle5)) {
			unset($this->particle5[array_search($name, $this->particle5)]);
		} elseif (in_array($name, $this->particle6)) {
			unset($this->particle6[array_search($name, $this->particle6)]);
		} elseif (in_array($name, $this->particle7)) {
			unset($this->particle7[array_search($name, $this->particle7)]);
		} elseif (in_array($name, $this->particle8)) {
			unset($this->particle8[array_search($name, $this->particle8)]);
		} elseif (in_array($name, $this->particle9)) {
			unset($this->particle9[array_search($name, $this->particle9)]);
		}

	}

	public function onQuit(PlayerQuitEvent $event)
	{

		$event->setQuitMessage("");

	}


	public function onInteract(PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		$name = $player->getName();
		$in = $event->getPlayer()->getInventory()->getItemInHand()->getCustomName();
		$inv = $player->getInventory();
		$blockid = $event->getBlock()->getID();
		$block = $event->getBlock();
		$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

		// ChestOpening

		if ($blockid == 54) {

			$event->setCancelled(true);

			if ($block->x == 260 && $block->y == 6 && $block->z == 238) {

				if (!$config->get("OpenChest1")) {

					$config->set("OpenChest1", true);
					$config->save();

					$player->sendMessage($this->prefix . TextFormat::GREEN . "Öffne Kiste ...");

				} else {

					$player->sendMessage($this->prefix . TextFormat::RED . "Hier wird bereits eine Kiste geöffnet.");

				}

			}

			if ($block->x == 260 && $block->y == 6 && $block->z == 270) {

				if (!$config->get("OpenChest2")) {

					$config->set("OpenChest2", true);
					$config->save();

					$player->sendMessage($this->prefix . TextFormat::GREEN . "Öffne Kiste ...");

				} else {

					$player->sendMessage($this->prefix . TextFormat::RED . "Hier wird bereits eine Kiste geöffnet.");

				}

			}


		}

		// Sichtbarkeit Der Spieler

		if ($in == TextFormat::RESET . TextFormat::GREEN . "Alle Spieler sichtbar") {
			$item = Item::get(351, 5, 1);
			$item->setCustomName(TextFormat::RESET . TextFormat::DARK_PURPLE . "Nur VIPs sichtbar");

			$inv->setItem(7, $item);

			$this->showvips[] = $name;
			unset($this->showall[array_search($name, $this->showall)]);

		}

		if ($in == TextFormat::RESET . TextFormat::DARK_PURPLE . "Nur VIPs sichtbar") {
			$item = Item::get(351, 8, 1);
			$item->setCustomName(TextFormat::RESET . TextFormat::GRAY . "Keine Spieler sichtbar");

			$inv->setItem(7, $item);

			$this->shownone[] = $name;
			unset($this->showvips[array_search($name, $this->showvips)]);

		}

		if ($in == TextFormat::RESET . TextFormat::GRAY . "Keine Spieler sichtbar") {
			$item = Item::get(351, 10, 1);
			$item->setCustomName(TextFormat::RESET . TextFormat::GREEN . "Alle Spieler sichtbar");

			$inv->setItem(7, $item);

			$this->showall[] = $name;
			unset($this->shownone[array_search($name, $this->shownone)]);

		}
		//run
		if ($in == TextFormat::RESET . TextFormat::GOLD . "SkyBlock") {
			$event->getPlayer()->transfer("54.37.166.50", "33");
		}
		if ($in == TextFormat::RESET . TextFormat::GOLD . "Faction") {
			$event->getPlayer()->transfer("54.37.166.50", "90");
		}
		if ($in == TextFormat::RESET . TextFormat::GOLD . "CityBuild") {
			$event->getPlayer()->transfer("54.37.166.50", "44");
		}

		if ($in == TextFormat::RESET . TextFormat::GOLD . "Rang Info") {

			//$this->getRangMenu($player);
			$event->getPlayer()->sendMessage($this->prefix . TextFormat::RED . "Soon");
		}

		if ($in == TextFormat::RESET . TextFormat::GOLD . "Nick") {
			var_dump("geht");
			$event->getPlayer()->sendMessage($this->prefix . TextFormat::RED . "Soon");
		}

		if ($in == TextFormat::RESET . TextFormat::GOLD . "Fly") {
			var_dump("geht");
			$sender = $event->getPlayer();
			if ($event->getPlayer() instanceof Player) {
				if ($event->getPlayer()->hasPermission("fly.lobby") or $event->getPlayer()->isOp()) {
					if (!$sender->getAllowFlight()) {
						$sender->setAllowFlight(true);
						$sender->sendMessage($this->prefix . TextFormat::GREEN . "Du kannst jetzt Fliegen.");
						return true;
					} else {
						if ($sender->getAllowFlight()) {
							$sender->setAllowFlight(false);
							$sender->sendMessage($this->prefix . TextFormat::RED . "Du kannst jetzt micht mehr fliegen.");
							return true;
						}
					}
				} else {
					$event->getPlayer()->sendMessage($this->prefix . TextFormat::RED . "Du musst einen Rang besitzen");
				}
			} else {
				$sender->sendMessage($this->prefix . TextFormat::DARK_RED . "This command is only available in-game!");
				return true;
			}
		}


		if ($in == TextFormat::GRAY . "Survival " . TextFormat::BOLD . TextFormat::GOLD . "2") {
            $this->getServer()->dispatchCommand($event->getPlayer(), "transferserver SkyEvolution.tk 19100");
			}
		
		if ($in == TextFormat::GOLD . "Faction Lobby") {
			if($event->getPlayer()->hasPermission("lobby.premium")) {
				
				$this->getServer()->dispatchCommand($event->getPlayer(), "transferserver SkyEvolution.tk 19135");
				
			} else {
				
				$player = $event->getPlayer();
				
				$player->sendMessage($this->prefix . TextFormat::RED . "Du kannst nicht in die " . TextFormat::GOLD . "Premium Lobby" . TextFormat::RED . "!");
				
			}
        }
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Teleporter") {
			
			$this->getTeleporter($player);
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "VIP - 10€") {
			
			$player->sendMessage(TextFormat::GRAY . "===] " . TextFormat::GOLD . "VIP" . TextFormat::GRAY . " [===");
			$player->sendMessage(TextFormat::GOLD . "Preis" . TextFormat::GRAY . ": " . TextFormat::GREEN . "10 Euro");
			$player->sendMessage(TextFormat::GOLD . "Features" . TextFormat::GRAY . ": ");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Cosmetics");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Farbiger Nametag");
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Hero - 15€") {
			
			$player->sendMessage(TextFormat::GRAY . "===] " . TextFormat::GOLD . "Hero" . TextFormat::GRAY . " [===");
			$player->sendMessage(TextFormat::GOLD . "Preis" . TextFormat::GRAY . ": " . TextFormat::GREEN . "15 Euro");
			$player->sendMessage(TextFormat::GOLD . "Features" . TextFormat::GRAY . ": ");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Cosmetics");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Doppelte Coins in MiniGames");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "OP Items in Factions" . TextFormat::GRAY . " | " . TextFormat::RED . "BALD");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Farbiger Nametag");
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Master - 25€") {
			
			$player->sendMessage(TextFormat::GRAY . "===] " . TextFormat::GOLD . "Master" . TextFormat::GRAY . " [===");
			$player->sendMessage(TextFormat::GOLD . "Preis" . TextFormat::GRAY . ": " . TextFormat::GREEN . "25 Euro");
			$player->sendMessage(TextFormat::GOLD . "Features" . TextFormat::GRAY . ": ");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Cosmetics");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Doppelte Coins in MiniGames");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "OP Items in Factions" . TextFormat::GRAY . " | " . TextFormat::RED . "BALD");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Doppelte Suppen in FFA" . TextFormat::GRAY . " | " . TextFormat::RED . "BALD");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Farbiger Nametag");
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Cosmetics") {
			if($player->hasPermission("lobby.cosmetics")) {
				
				$this->getCosmetics($player);
				
			} else {
				
				$player->sendMessage($this->prefix . TextFormat::RED . "Du kannst keine Cosmetics benutzen!");
				
			}
			
			
		}
		
		$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		
		//Groeße
		if($in == TextFormat::RESET . TextFormat::GOLD . "Groeße") {
			
			$this->getBigItems($player);
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Klein") {
			
			$player->sendMessage($this->prefix . TextFormat::GREEN . "Du bist jetzt " . TextFormat::GOLD . "Klein");
			$player->setDataProperty(Entity::DATA_SCALE, Entity::DATA_TYPE_FLOAT, 0.5);
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Normal") {
			
			$player->sendMessage($this->prefix . TextFormat::GREEN . "Du bist jetzt " . TextFormat::GOLD . "Normal");
			$player->setDataProperty(Entity::DATA_SCALE, Entity::DATA_TYPE_FLOAT, 1);
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Groß") {
			
			$player->sendMessage($this->prefix . TextFormat::GREEN . "Du bist jetzt " . TextFormat::GOLD . "Groß");
			$player->setDataProperty(Entity::DATA_SCALE, Entity::DATA_TYPE_FLOAT, 1.5);
			
		}
		
		// Partikel //
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Partikel") {
			
			$this->getParticleItems($player);
			
		}
		
		if($in == TextFormat::RESET . TextFormat::RED . "Feuer " . TextFormat::GOLD . "Partikel") {
			
			if(!in_array($name, $this->particle6)) {
				
				$this->particle6[] = $name;
				$player->sendMessage($this->prefix . TextFormat::GREEN . "Du hast " . TextFormat::GOLD . "Feuer" . TextFormat::GREEN . " Partikel aktiviert");
				
				if(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				}
				
				
				
			} else {
				
				unset($this->particle6[array_search($name, $this->particle6)]);
				
				$player->sendMessage($this->prefix . TextFormat::RED . "Du hast " . TextFormat::GOLD . "Feuer" . TextFormat::RED . " Partikel deaktiviert");
				
				if(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				}
				
				
				
			}
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Orange " . TextFormat::GOLD . "Partikel") {
			
			if(!in_array($name, $this->particle5)) {
				
				$this->particle5[] = $name;
				$player->sendMessage($this->prefix . TextFormat::GREEN . "Du hast " . TextFormat::GOLD . "Orange" . TextFormat::GREEN . " Partikel aktiviert");
				
				if(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				}
				
				
				
			} else {
				
				unset($this->particle5[array_search($name, $this->particle5)]);
				
				$player->sendMessage($this->prefix . TextFormat::RED . "Du hast " . TextFormat::GOLD . "Orange" . TextFormat::RED . " Partikel deaktiviert");
				
				if(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				}
				
				
				
			}
			
		}
		
		if($in == TextFormat::RESET . TextFormat::RED . "Rote " . TextFormat::GOLD . "Partikel") {
			
			if(!in_array($name, $this->particle1)) {
				
				$this->particle1[] = $name;
				$player->sendMessage($this->prefix . TextFormat::GREEN . "Du hast " . TextFormat::GOLD . "Rote" . TextFormat::GREEN . " Partikel aktiviert");
				
				if(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				}
				
				
				
			} else {
				
				unset($this->particle1[array_search($name, $this->particle1)]);
				
				$player->sendMessage($this->prefix . TextFormat::RED . "Du hast " . TextFormat::GOLD . "Rote" . TextFormat::RED . " Partikel deaktiviert");
				
				if(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				}
				
				
				
			}
			
		}
		
		
		if($in == TextFormat::RESET . TextFormat::YELLOW . "Gelbe " . TextFormat::GOLD . "Partikel") {
			
			if(!in_array($name, $this->particle2)) {
				
				$this->particle2[] = $name;
				$player->sendMessage($this->prefix . TextFormat::GREEN . "Du hast " . TextFormat::GOLD . "Gelbe" . TextFormat::GREEN . " Partikel aktiviert");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				}
				
				
				
			} else {
				
				unset($this->particle2[array_search($name, $this->particle2)]);
				
				$player->sendMessage($this->prefix . TextFormat::RED . "Du hast " . TextFormat::GOLD . "Gelbe" . TextFormat::RED . " Partikel deaktiviert");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				}
				
				
				
			}
			
		}
		
		if($in == TextFormat::RESET . TextFormat::BLUE . "Blaue " . TextFormat::GOLD . "Partikel") {
			
			if(!in_array($name, $this->particle4)) {
				
				$this->particle4[] = $name;
				$player->sendMessage($this->prefix . TextFormat::GREEN . "Du hast " . TextFormat::GOLD . "Blaue" . TextFormat::GREEN . " Partikel aktiviert");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				}
				
				
				
			} else {
				
				unset($this->particle4[array_search($name, $this->particle4)]);
				
				$player->sendMessage($this->prefix . TextFormat::RED . "Du hast " . TextFormat::GOLD . "Blaue" . TextFormat::RED . " Partikel deaktiviert");
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				}
				
				
				
			}
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GREEN . "Gruene " . TextFormat::GOLD . "Partikel") {
			
			if(!in_array($name, $this->particle3)) {
				
				$this->particle3[] = $name;
				$player->sendMessage($this->prefix . TextFormat::GREEN . "Du hast " . TextFormat::GOLD . "Gruene" . TextFormat::GREEN . " Partikel aktiviert");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				}
				
				
				
			} else {
				
				unset($this->particle3[array_search($name, $this->particle3)]);
				
				$player->sendMessage($this->prefix . TextFormat::RED . "Du hast " . TextFormat::GOLD . "Gruene" . TextFormat::RED . " Partikel deaktiviert");
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				}
				
				
				
			}
			
		}
		
		
		
		// Boots //
		
		$bot1 = Item::get(301, 0, 1);
		$bot1->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Heart Boots");
		
		$bot2 = Item::get(317, 0, 1);
		$bot2->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Jump Boots");
		
		$bot3 = Item::get(309, 0, 1);
		$bot3->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Speed Boots");
		
		$bot4 = Item::get(313, 0, 1);
		$bot4->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Explosion");
		
		$exit = Item::get(351, 1, 1);
		$exit->setCustomName(TextFormat::RESET . TextFormat::RED . "Exit");
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Boots") {
			$inv = $player->getInventory();
			$inv->clearAll();
			
			$inv->setItem(0, $bot1);
			$inv->setItem(1, $bot2);
			$inv->setItem(2, $bot3);
			$inv->setItem(3, $bot4);
			$inv->setItem(8, $exit);
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Heart Boots") {
			
			if(in_array($name, $this->heart)) {
				
				unset($this->heart[array_search($name, $this->heart)]);
				$player->sendMessage($this->prefix . TextFormat::RED . "Du hast jetzt keine " . TextFormat::GOLD . "Herz" . TextFormat::RED . " Stiefel mehr an.");
				$inv->setBoots(Item::get(0, 0, 1));
				$player->removeAllEffects();
				
				if(in_array($name, $this->speed)) {
					unset($this->speed[array_search($name, $this->speed)]);
				} elseif(in_array($name, $this->jump)) {
					unset($this->jump[array_search($name, $this->jump)]);
				} elseif(in_array($name, $this->water)) {
					unset($this->water[array_search($name, $this->water)]);
				}
				
			} else {
				
				$this->heart[] = $name;
				$player->sendMessage($this->prefix . TextFormat::GREEN . "Du hast jetzt " . TextFormat::GOLD . "Herz" . TextFormat::GREEN . " Stiefel an.");
				$player->removeAllEffects();
				$effect = Effect::getEffect(10);
				$effect->setDuration(999);
				$effect->setAmplifier(1);
				$effect->setVisible(false);
				$inv->setBoots(Item::get(301, 0, 1));
				$player->addEffect($effect);
				
				if(in_array($name, $this->speed)) {
					unset($this->speed[array_search($name, $this->speed)]);
				} elseif(in_array($name, $this->jump)) {
					unset($this->jump[array_search($name, $this->jump)]);
				} elseif(in_array($name, $this->water)) {
					unset($this->water[array_search($name, $this->water)]);
				}
				
			}
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Jump Boots") {
			
			if(in_array($name, $this->jump)) {
				
				unset($this->jump[array_search($name, $this->jump)]);
				$player->sendMessage($this->prefix . TextFormat::RED . "Du hast jetzt keine " . TextFormat::GOLD . "Spring" . TextFormat::RED . " Stiefel mehr an.");
				
				$player->removeAllEffects();
				$inv->setBoots(Item::get(0, 0, 1));
				if(in_array($name, $this->speed)) {
					unset($this->speed[array_search($name, $this->speed)]);
				} elseif(in_array($name, $this->heart)) {
					unset($this->heart[array_search($name, $this->heart)]);
				} elseif(in_array($name, $this->water)) {
					unset($this->water[array_search($name, $this->water)]);
				}
				
			} else {
				$player->removeAllEffects();
				$inv->setBoots(Item::get(317, 0, 1));
				$effect = Effect::getEffect(8);
				$effect->setDuration(999);
				$effect->setAmplifier(1);
				$effect->setVisible(false);
				
				$player->addEffect($effect);
				
				$this->jump[] = $name;
				$player->sendMessage($this->prefix . TextFormat::GREEN . "Du hast jetzt " . TextFormat::GOLD . "Spring" . TextFormat::GREEN . " Stiefel an.");
				
				if(in_array($name, $this->speed)) {
					unset($this->speed[array_search($name, $this->speed)]);
				} elseif(in_array($name, $this->heart)) {
					unset($this->heart[array_search($name, $this->heart)]);
				} elseif(in_array($name, $this->water)) {
					unset($this->water[array_search($name, $this->water)]);
				}
				
			}
			
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Speed Boots") {
			
			if(in_array($name, $this->speed)) {
				
				unset($this->speed[array_search($name, $this->speed)]);
				$player->sendMessage($this->prefix . TextFormat::RED . "Du hast jetzt keine " . TextFormat::GOLD . "Schnelligkeits" . TextFormat::RED . " Stiefel mehr an.");
				$inv->setBoots(Item::get(0, 0, 1));
				$player->removeAllEffects();
				
				if(in_array($name, $this->jump)) {
					unset($this->jump[array_search($name, $this->jump)]);
				} elseif(in_array($name, $this->heart)) {
					unset($this->heart[array_search($name, $this->heart)]);
				} elseif(in_array($name, $this->water)) {
					unset($this->water[array_search($name, $this->water)]);
				}
				
			} else {
				$inv->setBoots(Item::get(309, 0, 1));
				$this->speed[] = $name;
				$player->sendMessage($this->prefix . TextFormat::GREEN . "Du hast jetzt " . TextFormat::GOLD . "Schnelligkeits" . TextFormat::GREEN . " Stiefel an.");
				$player->removeAllEffects();
				$effect = Effect::getEffect(1);
				$effect->setDuration(999);
				$effect->setAmplifier(1);
				$effect->setVisible(false);
				
				$player->addEffect($effect);
				
				
				if(in_array($name, $this->jump)) {
					unset($this->jump[array_search($name, $this->jump)]);
				} elseif(in_array($name, $this->heart)) {
					unset($this->heart[array_search($name, $this->heart)]);
				} elseif(in_array($name, $this->water)) {
					unset($this->water[array_search($name, $this->water)]);
				}
				
			}
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Explosion") {
			
			if(in_array($name, $this->water)) {
				$inv->setBoots(Item::get(0, 0, 1));
				unset($this->water[array_search($name, $this->water)]);
				$player->sendMessage($this->prefix . TextFormat::RED . "Du hast jetzt keine " . TextFormat::GOLD . "Explosions" . TextFormat::RED . " Stiefel mehr an.");
				
				$player->removeAllEffects();
				
				if(in_array($name, $this->speed)) {
					unset($this->speed[array_search($name, $this->speed)]);
				} elseif(in_array($name, $this->heart)) {
					unset($this->heart[array_search($name, $this->heart)]);
				} elseif(in_array($name, $this->jump)) {
					unset($this->jump[array_search($name, $this->jump)]);
				}
				
			} else {
				$inv->setBoots(Item::get(313, 0, 1));
				$this->water[] = $name;
				$player->sendMessage($this->prefix . TextFormat::GREEN . "Du hast jetzt " . TextFormat::GOLD . "Explosions" . TextFormat::GREEN . " Stiefel an.");
				$player->removeAllEffects();
				$effect = Effect::getEffect(13);
				$effect->setDuration(999);
				$effect->setAmplifier(1);
				$effect->setVisible(false);
				
				$player->addEffect($effect);
				
				if(in_array($name, $this->speed)) {
					unset($this->speed[array_search($name, $this->speed)]);
				} elseif(in_array($name, $this->heart)) {
					unset($this->heart[array_search($name, $this->heart)]);
				} elseif(in_array($name, $this->jump)) {
					unset($this->jump[array_search($name, $this->jump)]);
				}
				
			}
			
		}
		
		if($in == TextFormat::RESET . TextFormat::RED . "Exit") {
			
			$inv = $player->getInventory();
			$inv->clearAll();
			
			$this->getItems($player);
			
		}
		
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args):bool {
		$name = $sender->getName();
		
        if ($cmd->getName() == "build" && $sender->hasPermission("lobby.build")) {
            if (!in_array($name, $this->buildmode)) {
                
				$this->buildmode[] = $name;
				$sender->sendMessage($this->prefix . TextFormat::GREEN . "Du kannst nun bauen.");
				
            } else {
				
                unset($this->buildmode[array_search($name, $this->buildmode)]);
				
                $sender->sendMessage($this->prefix . TextFormat::RED . "Du kannst nun nicht mehr bauen.");
                
            }
            
        }
		
		if ($cmd->getName() == "lobby") {
			
			$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn(); 
			$this->getServer()->getDefaultLevel()->loadChunk($spawn->getFloorX(), $spawn->getFloorZ());
			$sender->teleport($spawn, 0, 0);
			
        }
        return true;
		}
	
}

class TypeType extends PluginTask {
	
	public function __construct($plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin);
		
		$this->time1 = 0;
		$this->time2 = 0;
		
    }

    public function onRun($tick) {
		
		$level = $this->plugin->getServer()->getDefaultLevel();
		
		$center1 = new Vector3(260.5, 6.5, 238.5);
		$center2 = new Vector3(260.5, 6.5, 270.5);
		
		$config = new Config($this->plugin->getDataFolder() . "config.yml", Config::YAML);
		
		if(!$config->get("OpenChest1")) {
			for($yaw = 0;  $yaw <= 10; $yaw += (M_PI * 2) / 20){
				$x = -sin($yaw) + $center1->x;
				$z = cos($yaw) + $center1->z;
				$y = $center1->y;
				
				$level->addParticle(new FlameParticle(new Vector3($x, $y, $z)));
			}
		} else {
			if($this->time1 == 4) {
				$this->time1 = 0;
			}
			
			$this->time1++;
			
			if($this->time1 < 4) {
				
				for($yaw = 0;  $yaw <= 10; $yaw += (M_PI * 2) / 20){
					$x = -sin($yaw) + $center1->x;
					$z = cos($yaw) + $center1->z;
					$y = $center1->y;
					
					$level->addParticle(new RedstoneParticle(new Vector3($x, $y, $z)));
					
				}
				
			} else {
				
				for($yaw = 0;  $yaw <= 10; $yaw += (M_PI * 2) / 20){
					$x = -sin($yaw) + $center1->x;
					$z = cos($yaw) + $center1->z;
					$y = $center1->y;
					
					$level->addParticle(new RedstoneParticle(new Vector3($x, $y + 1, $z)));
					
				}
				
				$config->set("OpenChest1", false);
				$config->save();
				
			}
			
		}
		
		if(!$config->get("OpenChest2")) {
			for($yaw = 0;  $yaw <= 10; $yaw += (M_PI * 2) / 20){
				$x = -sin($yaw) + $center2->x;
				$z = cos($yaw) + $center2->z;
				$y = $center2->y;
				
				$level->addParticle(new FlameParticle(new Vector3($x, $y, $z)));
			}
		} else {
			if($this->time2 == 4) {
				$this->time2 = 0;
			}
			
			$this->time2++;
			
			if($this->time2 < 4) {
				
				for($yaw = 0;  $yaw <= 10; $yaw += (M_PI * 2) / 20){
					$x = -sin($yaw) + $center2->x;
					$z = cos($yaw) + $center2->z;
					$y = $center2->y;
					
					$level->addParticle(new RedstoneParticle(new Vector3($x, $y, $z)));
					
				}
				
			} else {
				
				for($yaw = 0;  $yaw <= 10; $yaw += (M_PI * 2) / 20){
					$x = -sin($yaw) + $center2->x;
					$z = cos($yaw) + $center2->z;
					$y = $center2->y;
					
					$level->addParticle(new RedstoneParticle(new Vector3($x, $y + 1, $z)));
					
				}
				
				$config->set("OpenChest2", false);
				$config->save();
				
			}
			
		}
		
		
	}
	
}

class ItemsLoad extends PluginTask {
	
	public function __construct($plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin);
    }

    public function onRun($tick) {
		
		foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {
			$name = $player->getName();
			$inv = $player->getInventory();
			
			$players = $player->getLevel()->getPlayers();
			$level = $player->getLevel();
			
			$x = $player->getX();
			$y = $player->getY() + 2;
			$z = $player->getZ();
			
			foreach($players as $play) {
				if(in_array($name, $this->plugin->showall)) {
					
					$player->showPlayer($play);
					
				} elseif(in_array($name, $this->plugin->showvips)) {
					
					if($play->hasPermission("lobby.see.vip")) {
						
						$player->showPlayer($play);
						
					} else {
						
						$player->hidePlayer($play);
						
					}
					
				} elseif(in_array($name, $this->plugin->shownone)) {
					
					$player->hidePlayer($play);
					
				}
				
			}
			
			// rot
			if(in_array($name, $this->plugin->particle1)) {
				
				$r = 255;
				$g = 0;
				$b = 0;
				
				$center = new Vector3($x, $y, $z);
				$particle = new DustParticle($center, $r, $g, $b, 1);
				
				for($yaw = 0;  $yaw <= 10; $yaw += (M_PI * 2) / 20){
					$x = -sin($yaw) + $center->x;
					$z = cos($yaw) + $center->z;
					$y = $center->y;
					
					$particle->setComponents($x, $y, $z);
					$level->addParticle($particle);
						
				}
				
			}
			//gelb
			if(in_array($name, $this->plugin->particle2)) {
				
				$r = 255;
				$g = 255;
				$b = 0;
				
				$center = new Vector3($x, $y, $z);
				$particle = new DustParticle($center, $r, $g, $b, 1);
				
				for($yaw = 0;  $yaw <= 10; $yaw += (M_PI * 2) / 20){
					$x = -sin($yaw) + $center->x;
					$z = cos($yaw) + $center->z;
					$y = $center->y;
					
					$particle->setComponents($x, $y, $z);
					$level->addParticle($particle);
						
				}
			}
			//gruen
			if(in_array($name, $this->plugin->particle3)) {
				
				$r = 0;
				$g = 255;
				$b = 0;
				
				$center = new Vector3($x, $y, $z);
				$particle = new DustParticle($center, $r, $g, $b, 1);
				
				for($yaw = 0;  $yaw <= 10; $yaw += (M_PI * 2) / 20){
					$x = -sin($yaw) + $center->x;
					$z = cos($yaw) + $center->z;
					$y = $center->y;
					
					$particle->setComponents($x, $y, $z);
					$level->addParticle($particle);
						
				}
			}
			//blau
			if(in_array($name, $this->plugin->particle4)) {
				
				$r = 0;
				$g = 0;
				$b = 255;
				
				$center = new Vector3($x, $y, $z);
				$particle = new DustParticle($center, $r, $g, $b, 1);
				
				for($yaw = 0;  $yaw <= 10; $yaw += (M_PI * 2) / 20){
					$x = -sin($yaw) + $center->x;
					$z = cos($yaw) + $center->z;
					$y = $center->y;
					
					$particle->setComponents($x, $y, $z);
					$level->addParticle($particle);
						
				}
				
			}
			//orange
			if(in_array($name, $this->plugin->particle5)) {
				
				$r = 255;
				$g = 165;
				$b = 0;
				
				$center = new Vector3($x, $y, $z);
				$particle = new DustParticle($center, $r, $g, $b, 1);
				
				for($yaw = 0;  $yaw <= 10; $yaw += (M_PI * 2) / 20){
					$x = -sin($yaw) + $center->x;
					$z = cos($yaw) + $center->z;
					$y = $center->y;
					
					$particle->setComponents($x, $y, $z);
					$level->addParticle($particle);
						
				}
				
			}
			
			if(in_array($name, $this->plugin->particle6)) {
				$x = $player->getX();
				$y = $player->getY();
				$z = $player->getZ();
				
				$center = new Vector3($x, $y, $z);
				
				for($yaw = 0;  $yaw <= 10; $yaw += (M_PI * 2) / 10){
					$x = -sin($yaw) + $center->x;
					$z = cos($yaw) + $center->z;
					$y = $center->y;
					
					$level->addParticle(new FlameParticle(new Vector3($x, $y + 1.5, $z)));
						
				}
				
				
			}
			
			//Boots
			if(in_array($name, $this->plugin->heart)) {
				
				$player->getLevel()->addParticle(new HeartParticle(new Vector3($player->getX(), $player->getY() + 0.4, $player->getZ())), $players);
				$effect = Effect::getEffect(10);
				$effect->setDuration(999);
				$effect->setAmplifier(1);
				$effect->setVisible(false);
				
				$inv->setBoots(Item::get(301, 0, 1));
				
				$player->addEffect($effect);
				
			}
			
			if(in_array($name, $this->plugin->jump)) {
				
				//$player->getLevel()->addParticle(new LavaParticle(new Vector3($player->getX(), $player->getY() + 0.5, $player->getZ())), $players);
				$effect = Effect::getEffect(8);
				$effect->setDuration(999);
				$effect->setAmplifier(1);
				$effect->setVisible(false);
				
				$inv->setBoots(Item::get(317, 0, 1));
				
				$player->addEffect($effect);
				
			}
			
			if(in_array($name, $this->plugin->speed)) {
				
				//$player->getLevel()->addParticle(new ExplodeParticle(new Vector3($player->getX(), $player->getY() + 0.5, $player->getZ())), $players);
				$effect = Effect::getEffect(1);
				$effect->setDuration(999);
				$effect->setAmplifier(3);
				$effect->setVisible(false);
				
				$inv->setBoots(Item::get(309, 0, 1));
				
				$player->addEffect($effect);
				
			}
			
			if(in_array($name, $this->plugin->water)) {
				
				$player->getLevel()->addParticle(new HugeExplodeParticle(new Vector3($player->getX(), $player->getY() + 1, $player->getZ())), $players);
				$effect = Effect::getEffect(13);
				$effect->setDuration(999);
				$effect->setAmplifier(1);
				$effect->setVisible(false);
				
				$inv->setBoots(Item::get(313, 0, 1));
				
				$player->addEffect($effect);
				
			}
			
		}
		
	}
	
}
