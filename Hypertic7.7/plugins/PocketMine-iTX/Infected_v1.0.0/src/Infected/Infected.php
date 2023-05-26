<?php

namespace Infected;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\scheduler\PluginTask;

use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\tile\Sign;
use pocketmine\level\Level;
use pocketmine\level\Position;

class Infected extends PluginBase implements Listener 
{
	public $prefix = TextFormat::GRAY . "[" . TextFormat::RED . TextFormat::BOLD . "Infected" . TextFormat::RESET . TextFormat::GRAY . "] " . TextFormat::WHITE;
	public $mode = 0;
	public $arenas = array();
	public $currentLevel = "";
	public $spawns = array();
	
	public function onEnable()
	{
        $this->getServer()->getPluginManager()->registerEvents($this ,$this);
		@mkdir($this->getDataFolder());
		$arena = new Config($this->getDataFolder() . "/arena.yml", Config::YAML);
		if($arena->get("arenas")!=null)
		{
			$this->arenas = $arena->get("arenas");
			foreach($this->arenas as $toLoad)
			{
				$this->getServer()->loadLevel($toLoad);
			}
		}
		$arena->save();
		$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
		if($config->get("time")==null)
		{
			$config->set("time",300);
		}
		if($config->get("starttime")==null)
		{
			$config->set("starttime",180);
		}
		if($config->get("players")==null)
		{
			$config->set("players",25);
		}
		if($config->get("infectedKit")==null)
		{
			$config->set("infectedKit", array("Helmet" => Item::GOLD_HELMET, "Chestplate" => Item::GOLD_CHESTPLATE, "Leggings" => Item::GOLD_LEGGINGS, "Boots" => Item::GOLD_BOOTS, "Items" => array(Item::BOW , Item::ARROW)));
		}
		if($config->get("survivorKit")==null)
		{
			$config->set("survivorKit", array("Helmet" => Item::LEATHER_CAP, "Chestplate" => Item::LEATHER_TUNIC, "Leggings" => Item::LEATHER_PANTS, "Boots" => Item::LEATHER_BOOTS, "Items" => array(Item::FISHING_ROD, Item::BOW, array(Item::ARROW, 5), array(Item::SNOWBALL, 60))));
		}
		$config->save();
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new GameRun($this), 20);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new RefreshSigns($this), 10);
	}
	
	public function onLogin(PlayerLoginEvent $event)
	{
		$player = $event->getPlayer();
		$player->getInventory()->clearAll();
	}
	
	public function onBlockBreak(BlockBreakEvent $event)
	{
		$player = $event->getPlayer();
		$level = $player->getLevel()->getFolderName();
		if(in_array($level,$this->arenas))
		{
			$event->setCancelled(true);
		}
	}
	
	public function onBlockPlace(BlockPlaceEvent $event)
	{
		$player = $event->getPlayer();
		$level = $player->getLevel()->getFolderName();
		if(in_array($level,$this->arenas))
		{
			$event->setCancelled(true);
		}
	}
	
	public function onCommand(CommandSender $player, Command $cmd, $label, array $args) {
        switch($cmd->getName()){
			case "inf":
				if($player->isOp())
				{
					if(!empty($args[0]))
					{
						if($args[0]=="addarena")
						{
							if($this->mode == 0)
							{
								if(!empty($args[1]))
								{
									if(file_exists($this->getServer()->getDataPath() . "/worlds/" . $args[1]))
									{
										unset($this->spawns);
										$this->spawns = array();
										$this->getServer()->loadLevel($args[1]);
										$this->getServer()->getLevelByName($args[1])->loadChunk($this->getServer()->getLevelByName($args[1])->getSafeSpawn()->getFloorX(), $this->getServer()->getLevelByName($args[1])->getSafeSpawn()->getFloorZ());
										array_push($this->arenas,$args[1]);
										$this->currentLevel = $args[1];
										$this->mode = 1;
										$player->sendMessage($this->prefix . "Go somewhere and use " . TextFormat::AQUA . "/inf setp " . TextFormat::WHITE . "to register a spawn. Use " . TextFormat::AQUA . "/inf done " . TextFormat::WHITE . " to continue.");
										$player->teleport($this->getServer()->getLevelByName($args[1])->getSafeSpawn(),0,0);
									}
									else
									{
										$player->sendMessage(TextFormat::RED . "There is no world with this name.");
									}
								}
								else
								{
									$player->sendMessage(TextFormat::RED . "Missing parameters.");
								}
							}
							else
							{
								$player->sendMessage(TextFormat::RED . "You can't use this right now.");
							}
						}
						else if($args[0]=="setp")
						{
							if($this->mode > 0 && $this->mode < 1000)
							{
								array_push($this->spawns, array($player->getX(),$player->getY(),$player->getZ()));
								$player->sendMessage($this->prefix . "[" . $this->mode . "] Spawn set!");
								$this->mode++;
							}
							else
							{
								$player->sendMessage(TextFormat::RED . "You can't use this right now.");
							}
						}
						else if($args[0]=="done")
						{
							$this->mode = 1000;
							$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
							$arena = new Config($this->getDataFolder() . "/arena.yml", Config::YAML);
							$arena->set("arenas", $this->arenas);
							$arena->set($this->currentLevel . "Spawns", $this->spawns);
							$arena->set($this->currentLevel . "StartTime", $config->get("starttime"));
							$arena->set($this->currentLevel . "Time", $config->get("time"));
							$arena->save();
							$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
							$this->getServer()->getDefaultLevel()->loadChunk($spawn->getFloorX(), $spawn->getFloorZ());
							$player->teleport($spawn,0,0);
							$player->sendMessage($this->prefix . "Tap a sign now to register it");
						}
						else
						{
							$player->sendMessage(TextFormat::RED . "Unknown command.");
						}
					}
					else
					{
						$player->sendMessage(TextFormat::RED . "Missing parameters.");
					}
				}
				else
				{
					$player->sendMessage(TextFormat::RED . "You don't have the needed permissions to execute this command.");
				}
			return true;
		}
	}
	
	public function onInteract(PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$tile = $player->getLevel()->getTile($block);
		
		if($tile instanceof Sign) 
		{
			if($this->mode==1000)
			{
				$tile->setText($this->prefix, TextFormat::GREEN . "[Join]" , $this->currentLevel, TextFormat::GOLD . "0 / 0");
				$this->currentLevel = "";
				$this->mode = 0;
				$player->sendMessage($this->prefix . "The arena has been registered successfully!");
			}
			else
			{
				$text = $tile->getText();
				if($text[0] == $this->prefix)
				{
					if($text[1] == TextFormat::GREEN . "[Join]")
					{
						$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
						$level = $this->getServer()->getLevelByName($text[2]);
						$spawn = $level->getSafeSpawn();
						$level->loadChunk($spawn->getFloorX(), $spawn->getFloorZ());
						$player->teleport($spawn,0,0);
						$player->setNameTag($player->getName());
						$player->getInventory()->clearAll();
					}
					else
					{
						$player->sendMessage(TextFormat::RED . "You can't join this match.");
					}
				}
			}
		}
	}
}

class RefreshSigns extends PluginTask {
	public $prefix = TextFormat::GRAY . "[" . TextFormat::RED . TextFormat::BOLD . "Infected" . TextFormat::RESET . TextFormat::GRAY . "] " . TextFormat::WHITE;
	public function __construct($plugin)
	{
		$this->plugin = $plugin;
		parent::__construct($plugin);
	}
  
	public function onRun($tick)
	{
		$config = new Config($this->plugin->getDataFolder() . "/config.yml", Config::YAML);
		$allplayers = $this->plugin->getServer()->getOnlinePlayers();
		$level = $this->plugin->getServer()->getDefaultLevel();
		$tiles = $level->getTiles();
		foreach($tiles as $t) {
			if($t instanceof Sign) {	
				$text = $t->getText();
				if($text[0]==$this->prefix)
				{
					$time = $config->get("time");
					$maxplayers = $config->get("players");
					$aop = 0;
					foreach($allplayers as $player){if($player->getLevel()->getFolderName()==$text[2]){$aop=$aop+1;}}
					$ingame = TextFormat::GREEN . "[Join]";
					$arena = new Config($this->plugin->getDataFolder() . "/arena.yml", Config::YAML);
					if($arena->get($text[2] . "Time") != $time)
					{
						$ingame = TextFormat::RED . "[Ingame]";
					}
					else if($aop >= $maxplayers)
					{
						$ingame = TextFormat::RED . "[Full]";
					}
					$t->setText($this->prefix,$ingame,$text[2], TextFormat::GOLD . $aop . " / " . $maxplayers);
				}
			}
		}
	}
}

class GameRun extends PluginTask implements Listener {
	public $prefix = TextFormat::GRAY . "[" . TextFormat::RED . TextFormat::BOLD . "Infected" . TextFormat::RESET . TextFormat::GRAY . "] " . TextFormat::WHITE;
	public $arenas = array();
	
	public function __construct($plugin)
	{
		$this->plugin = $plugin;
		$this->plugin->getServer()->getPluginManager()->registerEvents($this, $this->plugin); 
		parent::__construct($plugin);
	}
	
	public function onDamage(EntityDamageEvent $event){
		$entity = $event->getEntity();
		$level = $entity->getLevel()->getFolderName();
		if($entity instanceof Player)
		{
			if(in_array($level, $this->arenas))
			{
				if($entity->getNameTag() == $entity->getName())
				{
					$event->setCancelled(true);
				}
				else
				{
					if($event instanceof EntityDamageByEntityEvent)
					{
						if($entity->getNameTag() == TextFormat::GREEN . $entity->getName())
						{
							if($player->getNameTag() == TextFormat::RED . $player->getName())
							{
								$this->transformToInfected($entity);
							}
							else
							{
								$event->setCancelled(true);
							}
						}
						else
						{
							$event->setCancelled(true);
						}
					}
				}
			}
		}
	}
	
	public function onRespawn(PlayerRespawnEvent $event)
	{
		$player = $event->getPlayer();
		if($player instanceof Player)
		{
			$level = $player->getLevel()->getFolderName();
			if(in_array($level, $this->arenas))
			{
				$spawn = $this->getRandomSpawn($level);
				$event->setRespawnPosition(new Position($spawn[0],$spawn[1],$spawn[2],$player->getLevel()));
			}
		}
	}
  
	public function onRun($tick)
	{
		$config = new Config($this->plugin->getDataFolder() . "/config.yml", Config::YAML);
		$arenaconfig = new Config($this->plugin->getDataFolder() . "/arena.yml", Config::YAML);
		$this->arenas = $arenaconfig->get("arenas");
		if(!empty($arenas))
		{
			foreach($arenas as $arena)
			{
				$time = $arenaconfig->get($arena . "Time");
				$timeToStart = $arenaconfig->get($arena . "StartTime");
				$levelArena = $this->plugin->getServer()->getLevelByName($arena);
				if($levelArena instanceof Level)
				{
					$playersArena = array();
					$allplayers = $this->plugin->getServer()->getOnlinePlayers();
					foreach($allplayers as $maybeplayer)
					{
						if($maybeplayer->getLevel()->getFolderName() == $levelArena->getFolderName())
						{
							array_push($playersArena,$maybeplayer);
						}
					}
					shuffle($playersArena);
					if(count($playersArena)==0)
					{
						$arenaconfig->set($arena . "Time", $config->get("time"));
						$arenaconfig->set($arena . "StartTime", $config->get("starttime"));
					}
					else
					{
						if(count($playersArena)>=2)
						{
							if($timeToStart>0)
							{
								$timeToStart--;
								foreach($playersArena as $pl)
								{
									$pl->sendPopup(TextFormat::GOLD . $timeToStart . " seconds");
								}
								if($timeToStart<=0)
								{
									$infected = false;
									foreach($playersArena as $pl)
									{
										if($infected == false)
										{
											$infected = true;
											$this->transformToInfected($pl);
										}
										else
										{
											$this->transformToSurvivor($pl);
										}
										$randomspawn = $this->getRandomSpawn($arena);
										$levelArena->loadChunk($randomspawn[0],$randomspawn[2]);
										$pl->teleport(new Position($randomspawn[0],$randomspawn[1],$randomspawn[2],$levelArena));
									}
								}
								$arenaconfig->set($arena . "StartTime", $timeToStart);
							}
							else
							{
								$aop = count($playersArena);
								if($aop==1)
								{
									foreach($playersArena as $pl)
									{
										$pl->sendMessage($this->prefix . TextFormat::GREEN . "You was the last!");
										$pl->getInventory()->clearAll();
										$pl->setNameTag($pl->getName());
										$spawn = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
										$this->plugin->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
										$pl->teleport($spawn,0,0);
									}
									$arenaconfig->set($arena . "Time", $config->get("time"));
									$arenaconfig->set($arena . "StartTime", $config->get("starttime"));
								}
								$time--;
								foreach($playersArena as $pl)
								{
									$pl->sendTip(TextFormat::GOLD . $time . " seconds");
								}
								if($time <= 0)
								{
									$spawn = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
									$this->plugin->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
									foreach($playersArena as $pl)
									{
										$pl->teleport($spawn,0,0);
										if($pl->getNameTag()==TextFormat::GREEN . $player->getName())
										{
											$pl->sendMessage($this->prefix . "You won!");
										}
										$pl->setNameTag($player->getName());
										$pl->getInventory()->clearAll();
									}
									$time = $config->get("time");
								}
								$arenaconfig->set($arena . "Time", $time);
							}
						}
						else
						{
							if($timeToStart<=0)
							{
								foreach($playersArena as $pl)
								{
									$pl->sendMessage($this->prefix . TextFormat::GREEN . "You was the last!");
									$pl->getInventory()->clearAll();
										$pl->setNameTag($pl->getName());
									$spawn = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
									$this->plugin->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
									$pl->teleport($spawn);
								}
							}
							else
							{
								foreach($playersArena as $pl)
								{
									$pl->sendPopup(TextFormat::RED . "More players needed!");
								}
							}
							$arenaconfig->set($arena . "Time", $config->get("time"));
							$arenaconfig->set($arena . "StartTime", $config->get("starttime"));
						}
					}
				}
			}
		}
		$arenaconfig->save();
	}
	
	public function transformToInfected($player)
	{
		$player->setNameTag(TextFormat::RED . $player->getName());
		$config = new Config($this->plugin->getDataFolder() . "/config.yml", Config::YAML);
		$kit = $config->get("infectedKit");
		$items = $kit["Items"];
		$player->getInventory()->setHelmet(Item::get($kit["Helmet"]));
		$player->getInventory()->setChestplate(Item::get($kit["Chestplate"]));
		$player->getInventory()->setLeggings(Item::get($kit["Leggings"]));
		$player->getInventory()->setBoots(Item::get($kit["Boots"]));
		for($i = 0; $i < count($items); $i++)
		{
			$item = $items[$i];
			if(is_array($item))
			{
				$player->getInventory()->setItem($i,Item::get($item[0],0,$item[1]));
			}
			else
			{
				$player->getInventory()->setItem($i,Item::get($item[0]));
			}
		}
		$player->getInventory()->sendContents($player);
	}
	
	public function transformToSurvivor($player)
	{
		$player->setNameTag(TextFormat::GREEN . $player->getName());
		$config = new Config($this->plugin->getDataFolder() . "/config.yml", Config::YAML);
		$kit = $config->get("survivorKit");
		$items = $kit["Items"];
		$player->getInventory()->setHelmet(Item::get($kit["Helmet"]));
		$player->getInventory()->setChestplate(Item::get($kit["Chestplate"]));
		$player->getInventory()->setLeggings(Item::get($kit["Leggings"]));
		$player->getInventory()->setBoots(Item::get($kit["Boots"]));
		for($i = 0; $i < count($items); $i++)
		{
			$item = $items[$i];
			if(is_array($item))
			{
				$player->getInventory()->setItem($i,Item::get($item[0],0,$item[1]));
			}
			else
			{
				$player->getInventory()->setItem($i,Item::get($item[0]));
			}
		}
		$player->getInventory()->sendContents($player);
	}
	
	public function getRandomSpawn($arena)
	{
		$arenaconfig = new Config($this->plugin->getDataFolder() . "/arena.yml", Config::YAML);
		$spawns = $arenaconfig->get($arena . "Spawns");
		return $spawns[array_rand($spawns)];
	}
}