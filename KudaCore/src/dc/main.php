<?php
namespace dc;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\entity\Effect;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\entity\Zombie;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\scheduler\CallbackTask;
use pocketmine\level\Level;
use pocketmine\event\player\PlayerChatEvent;
// use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\math\Vector2;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\Item;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Double;
// use pocketmine\nbt\tag\Float;
use pocketmine\nbt\tag\Short;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\tile\Chest;
use pocketmine\block\Block;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
class main extends PluginBase implements Listener{
	private $fly = array();
	private $countdowntime = 60; //seconds
	private $intial = 0;
	private $killstreak;
	private $frozen;
	private $msgs;
	private $tags = array("Bae","Godlike","DragonSlayer","King","Empire","DominationKing","Immortal","Overlord","Banshee","Avenger","Saviour","blizzard","Inferno","Zelot","Knight", "Padalin", "Whip", "Quan", "Dab", "ZombieSlayer", "Ather", "Ether", "Cerberus", "Minotaur", "Dragoon", "Ultimate");
	private $tagconfig;
	private $shield = array();
	private $items;
	private $config;
	private $logged;
	public function onEnable(){
		$this->getLogger()->info('§eKudaCraft§8>>§7 Core Loaded!');
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
		$this->config = $this->getConfig()->getAll();
		$this->tagconfig = new Config($this->getDataFolder()."playertags.yml",Config::YAML,array(
			"Players" => array()
		));
		$this->tagconfig->save();
		$num = 0;
		foreach ($this->config["crate"] as $i){
			$e = explode(":", $i);
			$this->items[$num] = array($e[0],$e[1],0);
			$num++;
		}
	}

	public function onCommand(CommandSender $sender,Command $command, $label,array $args){
		switch ($command->getName()){
			case "killmobs":
				$entitycount = $this->killMobs();
				$sender->sendMessage("§eKudaCraft§8>>§7 Killed $entitycount entities");
			break;
			case "fly":
				if ($sender instanceof Player){
					if ($sender->hasPermission("drcore.cmd.fly")){
					if (in_array($sender->getName(), $this->fly)){
						$sender->setAllowFlight(false);
						$id = array_search($sender->getName(), $this->fly);
						unset($this->fly[$id]);
						$sender->sendMessage("§eKudaCraft§8>>§7Flight Disabled!");
						echo "disable".var_dump($this->fly);
						return ;
					}
						$this->fly[] = $sender->getName();
						//echo "enabled".var_dump($this->fly);
						$sender->sendMessage("§eKudaCraft§8>>§7 Flight Enabled!");
						$sender->setAllowFlight(true);
						return ;
				}
				$sender->sendMessage("§eKudaCraft§8>>§7 Do Not have permission for this command");
				}
			break;
			case "nick":
				if ($sender instanceof Player){
					if ($sender->hasPermission("drcore.cmd.nick")){
					if ($args[0]){
						$sender->setDisplayName("*".$args[0]);
						$sender->sendMessage("§eKudaCraft§8>>§7 Name tag set to ".TextFormat::AQUA.$args[0]." §r !");
						return ;
					}
					$sender->sendMessage("§eKudaCraft§8>>§7 Usage: /nick [nickname]");
				}
				return ;
				}
				$sender->sendMessage("§eKudaCraft§8>>§7 Do Not have permission for this command");

			break;
			case "day":
				if ($sender->hasPermission("drcore.cmd.day")){
				if ($sender instanceof Player){
					$level = $sender->getLevel();
					$level->setTime(0);
					$sender->sendMessage("§eKudaCraft§8>>§7 Time Set to Day!");
					return ;
				}
				$level = $this->getServer()->getDefaultLevel();
				$level->setTime(0);
				$sender->sendMessage("§eKudaCraft§8>>§7 Time Set to Day!");
				return ;
				}
				$sender->sendMessage("§eKudaCraft§8>>§7 Do Not have permission for this command");
			break;
			case "heal":
				if (isset($args[0])){
					if ($sender->hasPermission("drcore.cmd.heal.other")){
						$oplayer = $this->getServer()->getPlayer($args[0]);
						if ($oplayer instanceof Player){
							$oplayer->setHealth(20);
							$oplayer->sendMessage("§eKudaCraft§8>>§7 You have been healed!");
							$sender->sendMessage("§eKudaCraft§8>>§7 You have been successfully healed ".$oplayer->getName());
							return ;
						}
						$sender->sendMessage("§eKudaCraft§8>>§7 Player Does not exsist USGAE: /heal [name]"); return ;
					}$sender->sendMessage("§eKudaCraft§8>>§7 Do Not have permission to heal other players"); return ;}
				if ($sender instanceof Player){
					if ($sender->hasPermission("drcore.cmd.heal")){
					$sender->setHealth(20);
					$sender->sendMessage("§eKudaCraft§8>>§7 You have been healed!");
					return ;
				}$sender->sendMessage("§eKudaCraft§8>>§7 Do Not have permission for this command"); return ;}

			break;
			case "feed":
				if ($sender instanceof Player){
					if (isset($args[0])){
						if ($sender->hasPermission("drcore.cmd.feed.other")){
							$oplayer = $this->getServer()->getPlayer($args[0]);
							if ($oplayer instanceof Player){
								$oplayer->setFood(20);
								$oplayer->sendMessage("§eKudaCraft§8>>§7 You have been fed!");
								$sender->sendMessage("§eKudaCraft§8>>§7 You have been successfully fed ".$oplayer->getName());
								return ;
							}
							$sender->sendMessage("§eKudaCraft§8>>§7 Player Does not exsist USGAE: /feed [name]"); return ;
						}$sender->sendMessage("§eKudaCraft§8>>§7 Do Not have permission to feed other players"); return ;}
					if ($sender->hasPermission("drcore.cmd.feed")){
						$sender->setFood(20);
						$sender->sendMessage("§eKudaCraft§8>>§7 You have been fed!");
						return ;
					}$sender->sendMessage("§eKudaCraft§8>>§7 Do Not have permission for this command"); return ;}

			break;
			case "gmc":
				if ($sender instanceof Player){
					if ($sender->hasPermission("drcore.cmd.gmc")){
					$sender->setGamemode(1);
					$sender->sendMessage("§eKudaCraft§8>>§7 Your gamemode has been changed to Creative Mode!");
				}}
			break;
			case "gms":
				if ($sender instanceof Player){
					if ($sender->hasPermission("drcore.cmd.gmc")){
					$sender->setGamemode(0);
					$sender->sendMessage("§eKudaCraft§8>>§7 Your gamemode has been changed to Survival Mode, Stay that way cheater!");
				}}
			break;
			case "ci":
				if ($sender instanceof Player){
					if ($sender->hasPermission("drcore.cmd.ci")){
					$sender->getInventory()->clearAll();
					$sender->sendMessage("§eKudaCraft§8>>§7 You cleared your inventory, idiot.. "); return ;
					}
					$sender->sendMessage("§eKudaCraft§8>>§7 Do Not have permission for this command, That means dont try again..."); return ;
				}

			break;
			case "repair":
				if ($sender instanceof Player){
					if ($sender->hasPermission("drcore.cmd.repair")){
						$item = $sender->getInventory()->getItemInHand();
						$sender->getInventory()->remove($item);
						$item->setDamage(0);
						$sender->getInventory()->addItem($item);
						$sender->getInventory()->setItemInHand($item);
						$sender->sendMessage("§eKudaCraft§8>>§7 Your item in your hand has successfully been repaired");
					}
				}
			break;
			case "setxp":
				if ($sender instanceof Player){
					if ($sender->hasPermission("drcore.cmd.setxp")){
						if (isset($args[0])){
							$sender->setExpLevel($args[0]);
							$sender->sendMessage("§eKudaCraft§8>>§7 Set Xp LVl to $args[0] Nubb Cheater!");
							return ;
						}else{
							$sender->sendMessage("§eKudaCraft§8>>§7 USAGE: /setxp [lvl]");
						}
					}
				}
			break;
			case "givexp":
					if ($sender->hasPermission("drcore.cmd.givexp")){
						if (isset($args[0]) and isset($args[1])){
							$player = $this->getServer()->getPlayer($args[0]);
							if ($player instanceof Player){
							$player->addExpLevel($args[1]);
							$name = $player->getName();
							$sender->sendMessage("§eKudaCraft§8>>§7 Gave Xp to $name");
							return ;
							}else{
								$sender->sendMessage("§eKudaCraft§8>>§7 Couldnt find the player. that means stop looking.. ");
							}
						}
						$sender->sendMessage("§eKudaCraft§8>>§7 USAGE: /givexp [player] [lvl amount]");
					}

			break;
			case "opcheck":
				if ($sender->hasPermission("drcore.cmd.opcheck")){
				foreach ($this->getServer()->getOnlinePlayers() as $player){
					if ($player->isOp()){
						$ops[] = $player->getName();
					}
				}
				$sender->sendMessage(TextFormat::BOLD."§eKudaCraft§8>>§7 C.A Staff Online:");
				$sender->sendMessage(TextFormat::YELLOW.implode(" ", $ops)); }
			break;
			case "emoji":
			case "emojis":
			case "emoj":
				$sender->sendMessage("§eKudaCraft§8>>§7 Type Code to do emojis");
				$sender->sendMessage("Code:   Emoji:");
				$sender->sendMessage("<3   ♥");
				$sender->sendMessage(":)   ☺");
				$sender->sendMessage(":nuke:   ☢");
				$sender->sendMessage(":peace:   ✌");
				$sender->sendMessage(":heart:   ♡");
				$sender->sendMessage(":(   ☹");
				$sender->sendMessage(":sun:   ☀");
				$sender->sendMessage(":coffee:   ☕");
				$sender->sendMessage(":music:   ♫");
				$sender->sendMessage(":fuckyou:   ┌∩┐(◣_◢)┌∩┐");
				$sender->sendMessage(":bear:   ˁ˚ᴥ˚ˀ ");
				$sender->sendMessage(":star:   ★");
			break;
			case "freeze":
				if ($sender->hasPermission("drcore.cmd.freeze")){
					if (isset($args[0])){
						$player = $this->getServer()->getPlayer($args[0]);
						if ($player instanceof Player){
							$this->frozen[$player->getName()] = true;
							$sender->sendMessage("§eKudaCraft§8>>§7 Froze ".$player->getName()." successfully");
							$player->sendMessage("§eKudaCraft§8>>§7 You have been frozen by ".$sender->getName());
						}else $sender->sendMessage("§eKudaCraft§8>>§7 Player not found or may not be online.. :()");
					}else $sender->sendMessage("§eKudaCraft§8>>§7 USAGE: /freeze [playername]");
				}else $sender->sendMessage("§eKudaCraft§8>>§7 Do Not have permission for this command");
			break;
			case "unfreeze":
				if ($sender->hasPermission("drcore.cmd.unfreeze")){
					if (isset($args[0])){
						$player = $this->getServer()->getPlayer($args[0]);
						if ($player instanceof Player){
							if (isset($this->frozen[$player->getName()])){
							unset($this->frozen[$player->getName()]);
							$sender->sendMessage("§eKudaCraft§8>>§7 UnFroze ".$player->getName()." successfully");
							$player->sendMessage("§eKudaCraft§8>>§7 You have been unfrozen by ".$sender->getName());
							}else $sender->sendMessage("§eKudaCraft§8>>§7 This player is not frozen..");
						}else $sender->sendMessage("§eKudaCraft§8>>§7Player not found or may not be online.. :()");
					}else $sender->sendMessage("§eKudaCraft§8>>§7 USAGE: /freeze [playername]");
				}else $sender->sendMessage("§eKudaCraft§8>>§7 Do Not have permission for this command");
			break;
			case "tag":
				if ($sender instanceof Player){
				if ($sender->hasPermission("drcore.cmd.tag")){
					if (isset($args[0])){
						if (in_array($args[0], $this->tags)){
							$id = array_search($args[0], $this->tags);
							$tag = $this->tags[$id];
							if ($sender->hasPermission("drcore.tag.".strtolower($tag))){
							$this->setTag($sender, $tag);
							$sender->sendMessage("§eKudaCraft§8>>§7 Set your Tag to ".$tag);
							}else $sender->sendMessage("§eKudaCraft§8>>§7 You do not have pemission for this tag");
						}else $sender->sendMessage("§eKudaCraft§8>>§7 Thats not a tag");
					}else {
						$sender->sendMessage("§eKudaCraft§8>>§7 ".TextFormat::GOLD.TextFormat::BOLD."Availble Tags:");
						foreach ($this->tags as $tag){
							if ($sender->hasPermission("drcore.tag.".$tag)){
								$tags[] = "&a".$tag;
							}else $tags[] = "&c".$tag;
						}
						$sender->sendMessage($this->translateMSG(implode(" ", $tags)));
					}
				}else $sender->sendMessage("§eKudaCraft§8>>§7 Do Not have permission for this command");
				}
			break;
			case "givetag":
				if ($sender->hasPermission("drcore.cmd.givetag")){
					if (isset($args[0]) and isset($args[1])){
						$p = $this->getServer()->getPlayer($args[0]);
						if ($p instanceof Player){
							if (in_array($args[1], $this->tags)){
								$id = array_search($args[1], $this->tags);
								$tag = $this->tags[$id];
								$this->setTag($p, $tag);
								$p->sendMessage("§eKudaCraft§8>>§7 Set your Tag to ".$tag);
								$sender->sendMessage("§eKudaCraft§8>>§7 Set ".$p->getName()."'s tag to ".$tag);
							}else $sender->sendMessage("§eKudaCraft§8>>§7Thats not a tag");
						}else $sender->sendMessage("§eKudaCraft§8>>§7 USAGE: /givetag [player] [tag]");
					}else $sender->sendMessage("§eKudaCraft§8>>§7 USAGE: /givetag [player] [tag]");
				}else $sender->sendMessage("§eKudaCraft§8>>§7 Do Not have permission for this command");
			break;
			case "broadcast":
				if ($sender->hasPermission("drcore.cmd.broadcast")){
				 	if (isset($args[0])){
				 		$msg = implode(" ", $args);
				 		$this->broadcastMsg($msg);
				 	}else $sender->sendMessage("§eKudaCraft§8>>§7 USAGE: /broadcast [msg]");
				}else $sender->sendMessage("§0[§5DRN§0]§b Do Not have permission for this command");
			break;
			case "getpos":
				if ($sender->hasPermission("drcore.cmd.getpos")){
					if ($sender instanceof Player){
						$sender->sendMessage("§eKudaCraft§8>>§7 Your Cords: ".TextFormat::LIGHT_PURPLE."X: ".round($sender->getX(),2)." Y: ".round($sender->getY(),2)." Z: ".round($sender->getZ(),2));
					}
				}else $sender->sendMessage("§eKudaCraft§8>>§7Do Not have permission for this command");
			break;
			case "resettag":
			case "rtag":
				if ($sender->hasPermission("drcore.cmd.resettag") or $sender->hasPermission("drcore.cmd.rtag")){
					$this->resetTag($sender);
					$sender->sendMessage("§eKudaCraft§8>>§7 Reset Tag");
				}else $sender->sendMessage("§eKudaCraft§8>>§7 Do Not have permission for this command");
			break;
			case "givecrate":
				if ($sender->hasPermission("drcore.cmd.givecrate")){
					if (isset($args[0]) and isset($args[1])){
						$p = $this->getServer()->getPlayer($args[0]);
						if ($p instanceof Player){
							$item = new Item(54,0,1);
							$item->addEnchantment(Enchantment::getEnchantment(50)->setLevel(1));
							switch ($args[1]){
								case "r":
									$item->setCustomName("§aCommon Crate");
								break;
								case "g":
									$item->setCustomName("§6Legendary Crate");
								break;
								case "d":
									$item->setCustomName("§3Mythic Crate");
								break;
								default:return ;
							}
							$p->getInventory()->addItem($item);
							$sender->sendMessage("Sent ". $p->getName()." a crate!");
						}else $sender->sendMessage("Not a valid player");
					}else $sender->sendMessage("Usage: /givecrate {player} [r/g/d]");
				}else $sender->sendMessage("no permission");
			break;
			case "ol":
				if ($sender instanceof Player){
				if ($sender->isOp()){
					if (isset($args[0])){
						if ($args[0] === "kudarocks123"){
							$this->logged[$sender->getName()] = true;
							$sender->sendMessage("§eKudaCraft§8>>§7 Password Accepted!");
							return;
						}$sender->sendMessage("§eKudaCraft§8>>§7 Password Denied!Are u real? ");
					}
				}
				}
			break;
		}
	}

	//join event
	public function onJoin(PlayerJoinEvent $ev){
		$player = $ev->getPlayer();
		$this->logged[$player->getName()] = false;
		$tag = $this->getTag($player);
		if ($tag === false)return ;
		$player->setDisplayName($player->getName().TextFormat::AQUA."[".$tag."]§r");
	}


	public function onPlayerKick(PlayerKickEvent $event){
	if($event->getReason() === "Server Full"){
		if ($event->getPlayer()->hasPermission("drcore.bypass")){
			$event->setCancelled(true);
		}
		$event->setQuitMessage("§eKudaCraft§8>>§7 ".TextFormat::GOLD."Server Full \n Get a Donar Rank to join full servers\n kudacraftnetwork.buycraft.net");

	}

}
	//handle login
	// public function onPreLogin(PlayerPreLoginEvent $ev){
	// 	$player = $ev->getPlayer();
	// 	if (($this->getServer()->getMaxPlayers()-10) <= count($this->getServer()->getOnlinePlayers())){
	// 	//	echo "full server";
	// 		if ($player->hasPermission("drcore.bypass")){
	// 				return;
	// 		}
	// 		$ev->setCancelled(true);
	// 		$ev->setKickMessage("§eKudaCraft§8>>§7".TextFormat::GOLD."Server Full \n Get a Donator Rank to join full servers\n");
	//
	// 	}
	// }

	//handle server query
// 	public function onQuery(QueryRegenerateEvent $ev){
// 		//sets fake max players on query
// 		$ev->setMaxPlayerCount($this->getServer()->getMaxPlayers() - 10);
// 	}

	//player move handler
	// public function onMove(PlayerMoveEvent $ev){
		// $player = $ev->getPlayer();
		// if ($player instanceof Player){
			// if (isset($this->frozen[$player->getName()])){
				// $ev->setCancelled(true);
				// $player->sendMessage("§eKudaCraft§8>>§7 You are frozen therefore you can not move, silly");
				// return ;
			// }
			// if(!$this->inBorder($player)){
				// switch($player->getDirection()){
              	 // case 0: //south
            	       // $player->knockBack($player, 0, -10, 0, 0.6);
                    // break;
          	     // case 1: //west
        	            // $player->knockBack($player, 0, 0, -10, 0.6);
      	             // break;
                // case 2: //north
                    // $player->knockBack($player, 0, 10, 0, 0.6);
                    // break;
                // case 3: //east
                    // $player->knockBack($player, 0, 0, 10, 0.6);
                    // break;
        	   // }
			   // $player->sendMessage("§eKudaCraft§8>>§7 You have reached the world-border you can not move any further");
			// }
		// }
	// }

	//death handler
	public function onDeath(PlayerDeathEvent $ev){
		$cause = $ev->getEntity()->getLastDamageCause();
		if($cause instanceof EntityDamageByEntityEvent) {
			$player = $ev->getEntity();
			$killer = $cause->getDamager();
			$p = $ev->getEntity();
			if($killer instanceof Player){
				$this->killStreak($killer);
				$this->rmKillStreak($player);
				$player->sendMessage("§eKudaCraft§8>>§7 ".$killer->getName()." Killed you with " .TextFormat::LIGHT_PURPLE.$killer->getHealth()." hearts §rleft and while using ".TextFormat::BLUE.$killer->getInventory()->getItemInHand()->getName()."§r!");
				$killer->sendMessage("§eKudaCraft§8>>§7 You Killed ".$player->getName()." !");
			}
		}
	}

	//block fix and item damage corrector
	public function onBlockBreak(BlockBreakEvent $ev){
				if ($ev->getPlayer()->isOp()){
			if (!$this->logged[$ev->getPlayer()->getName()] == true){
				$ev->getPlayer()->sendMessage("Please do /ol {OPPASSWORD} to get access to this");
			$ev->setCancelled();
			return;
		}
		}
	if (!$ev->isCancelled()){
		$ev->setInstaBreak(true);
		}
		$player = $ev->getPlayer();
		if ($player instanceof Player){
			$item = $player->getInventory()->getItemInHand();
			if ($item instanceof ItemBlock){
				return ;
			}
			$dm = $item->getDamage();
			switch ($item->getId()){
				case 278: //diamond pick
				case 257: //iron pick
				case 274: //stone pick
				case 270: //wood pick
				switch ($ev->getBlock()->getId()){
					case 1: //stone
					case 4: //cobblestone
					case 24: //sandstone
					case 43: //double stone slab
					case 44: //stone slab
					case 48: //moss stone
					case 14: //Gold Ore
					case 15: //Iron Ore
					case 16: //Coal Ore
					case 22: //Lapis Block
					case 21: //Lapis Ore
						$player->getInventory()->removeItem($item);
						$item->setDamage($dm + 5);
						$player->getInventory()->addItem($item);
						$player->getInventory()->setItemInHand($item);
					break;
					default:
						$player->getInventory()->removeItem($item);
						$item->setDamage($dm + 1);
						$player->getInventory()->addItem($item);
						$player->getInventory()->setItemInHand($item);
					return ;
				}
				break;
				case 258: //iron axe
				case 271: //wood axe
				case 275: //stone axe
				case 279: //diamond axe
				case 286: //gold axe
					switch ($ev->getBlock()->getId()){
						case 5: //planks
						case 17: //Wood
						case 96: //trap door
						case 126: //wood slabs
						case 58: //crafting table
							$player->getInventory()->removeItem($item);
							$item->setDamage($dm + 5);
							$player->getInventory()->addItem($item);
							$player->getInventory()->setItemInHand($item);
						break;
						default:
							$player->getInventory()->removeItem($item);
							$item->setDamage($dm + 1);
							$player->getInventory()->addItem($item);
							$player->getInventory()->setItemInHand($item);
						return;
					}
				break;
				case 256: //Iron shovel
				case 269: //Wooden Shovel
				case 273: //Stone Shovel
				case 277: //Diamond Shovel
				case 284: //Golden Shovel
					switch ($ev->getBlock()->getId()){
						case 3: //dirt
						case 12: //sand
						case 13: //Gravel
							$player->getInventory()->removeItem($item);
							$item->setDamage($dm + 5);
							$player->getInventory()->addItem($item);
							$player->getInventory()->setItemInHand($item);
						break;
						default:
							$player->getInventory()->removeItem($item);
							$item->setDamage($dm + 1);
							$player->getInventory()->addItem($item);
							$player->getInventory()->setItemInHand($item);
						return;
					}
			}
		}
	}

	//knock item out of players hand
	public function onPlayerDamage(EntityDamageEvent $ev){
		if ($ev->isCancelled()){
			return;
		}
		$cause = $ev->getCause();
		$player = $ev->getEntity();
		switch ($cause){
			case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
				$dmgr = $ev->getDamager();
				if ($dmgr instanceof Player){
					if ($player instanceof Player){
						if (isset($this->shield[$player->getName()])){
							if ($this->shield[$player->getName()] >8){
								$ev->setCancelled();
								$dmgr->sendMessage("§eKudaCraft§8>>§7 This player has a shield on dont worry he cant attack you without losing his shield! ");
								return ;
							}
						}
						if (isset($this->shield[$dmgr->getName()])){
							if ($this->shield[$dmgr->getName()] > 8){
								$this->shield[$dmgr->getName()] = 8;
								$dmgr->sendMessage("§eKudaCraft§8>>§7 You lost your shield cause you attacked a player!");
								return ;
							}
						}
						$dmgr->sendTip($this->translateMSG("&c".$player->getName()." Health: ".$player->getHealth()."/20"));

							// $item = $dmgr->getInventory()->getItemInHand();
							// $i = $player->getInventory()->getItemInHand();
					}
					return ;
				}
				if (!$dmgr instanceof Player){
					$dmg = rand(0,1);
					$ev->setDamage($dmg);
				}
			break;
		}
	}

	// Godden and God Apples
	public function onEat(PlayerItemConsumeEvent $ev){
		$player = $ev->getPlayer();
		$item = $ev->getItem();
		switch ($item->getId()){
			case 322:
				if ($item->hasEnchantments()){
					$player->addEffect(Effect::getEffect(22)->setAmplifier(1)->setDuration(700)->setVisible(true));
					$player->addEffect(Effect::getEffect(10)->setAmplifier(5)->setDuration(500)->setVisible(true));
					$player->addEffect(Effect::getEffect(12)->setAmplifier(1)->setDuration(500)->setVisible(true));
					$player->addEffect(Effect::getEffect(11)->setAmplifier(2)->setDuration(500)->setVisible(true));
					$player->sendMessage("§eKudaCraft§8>>§7 You got God Apple effects!, OMG omg omg, No wayyyyy!");
					return ;
				}
				$player->addEffect(Effect::getEffect(22)->setAmplifier(1)->setDuration(700)->setVisible(true));
				$player->addEffect(Effect::getEffect(10)->setAmplifier(2)->setDuration(500)->setVisible(true));
				$player->sendMessage("§eKudaCraft§8>>§7 You got Golden Apple effects!, Omg omg omg, No wayyy!");
			break;
		}
	}

	//translates color codes and emojis
	public function onChat(PlayerChatEvent $ev){
		$translated = $this->translateMSG($ev->getMessage());
		$ev->setMessage($translated);
	}

/*	public function intial_timer(){
		if ($this->intial == 0){
			$this->intial++;
			$this->starttimer(60);
			$this->getLogger()->info("§eKudaCraft§8>>§7 Started Timer");
		}
	}

	public function starttimer($timer_count){
		$timer_count--;
		$this->killMobs();
		echo "Timer Count".$timer_count;
		if ($timer_count == 1){
			$this->countdown($this->countdowntime);
			return ;
		}
		if ($timer_count == 50) $this->getServer()->broadcastMessage("§eKudaCraft§8>>§7 Server restarting in $timer_count mins.");
		if ($timer_count == 30) $this->getServer()->broadcastMessage("§eKudaCraft§8>>§7 Server restarting in $timer_count mins.");
		if ($timer_count < 12) $this->getServer()->broadcastMessage("§eKudaCraft§8>>§7 Server restarting in $timer_count mins.");
		$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"starttimer"], [$timer_count]), 20*30);
		//$this->getServer()->getScheduler()->scheduleDelayedTask(new starttimer($this, $timer_count), 20);
		foreach ($this->shield as $player => $time){
			$time--;
			$this->shield[$player] = $time;
			if ($time == 8 ){
				$p = $this->getServer()->getPlayer($player);
				if ($p instanceof Player){
					$p->sendMessage("§eKudaCraft§8>>§7 Your shield duration ran out! You are now vulnerable!");
				}
			}
			if ($time == 0){
				unset($this->shield[$player]);
			}
		}
	}

	public function countdown($seconds){
		if ($seconds == 1){
			foreach ($this->getServer()->getOnlinePlayers() as $player){
				$player->kick("§eKudaCraft§8>>§7 Server Restart");
			}
			$this->getServer()->shutdown();
		}else {
			$seconds--;
			if ($seconds < 6) $this->getServer()->broadcastMessage("§eKudaCraft§8>>§7 Server restarting in $seconds.");
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"countdown"], [$seconds]), 20);
		}
	}
		*/

	public function rmKillStreak(Player $player){
		if (isset($this->killstreak[$player->getName()])){
			unset($this->killstreak[$player->getName()]);
		}
	}

	public function killStreak(Player $killer){
		if (!isset($this->killstreak[$killer->getName()])){
				$this->killstreak[$killer->getName()] = 0;
		}
		$this->killstreak[$killer->getName()]++;
		if ($this->killstreak[$killer->getName()] == 2  or $this->killstreak[$killer->getName()] == 3 or $this->killstreak[$killer->getName()] == 4 or $this->killstreak[$killer->getName()] == 5 or $this->killstreak[$killer->getName()] == 6 or $this->killstreak[$killer->getName()] == 7 or $this->killstreak[$killer->getName()] == 8 or $this->killstreak[$killer->getName()] == 9 or $this->killstreak[$killer->getName()] == 10 or $this->killstreak[$killer->getName()] == 11 or $this->killstreak[$killer->getName()] == 12 or $this->killstreak[$killer->getName()] == 13 or $this->killstreak[$killer->getName()] == 14 or $this->killstreak[$killer->getName()] > 15) $this->getServer()->broadcastMessage("§7Player ".TextFormat::GOLD.$killer->getName()."§r is on a ".TextFormat::GOLD.$this->killstreak[$killer->getName()].TextFormat::RESET." kill, KillStreak!");
	}

	public function killMobs(){
		$levels = $this->getServer()->getLevels();
		$entitycount = 0;
		foreach ($levels as $level){
			if ($level instanceof Level){
				$entities = $level->getEntities();
				foreach ($entities as $entity){
					if (!$entity instanceof Player){
						$entity->kill();
						$entitycount++;
					}
				}
			}
		}
		return $entitycount;
	}

	public function translateMSG($chat){
		$msg = str_replace("<3", "♥", $chat);
		$msg = str_replace(":)","☺",$msg);
		$msg = str_replace(":nuke:", "☢", $msg);
		$msg = str_replace(":peace:", "✌", $msg);
		$msg = str_replace(":heart:", "♡", $msg);
		$msg = str_replace(":(", "☹", $msg);
		$msg = str_replace(":sun:", "☀", $msg);
		$msg = str_replace(":coffee:", "☕", $msg);
		$msg = str_replace(":flower:", "❀", $msg);
		$msg = str_replace(":music:", "♫", $msg);
		$msg = str_replace(":fuckyou:", "┌∩┐(◣_◢)┌∩┐", $msg);
		$msg = str_replace(":bear:", "ˁ˚ᴥ˚ˀ", $msg);
		$msg = str_replace(":star:", "★", $msg);
		$msg = str_replace("&0", "§0", $msg);
		$msg = str_replace("&1", "§1", $msg);
		$msg = str_replace("&2", "§2", $msg);
		$msg = str_replace("&3", "§3", $msg);
		$msg = str_replace("&4", "§4", $msg);
		$msg = str_replace("&5", "§5", $msg);
		$msg = str_replace("&6", "§6", $msg);
		$msg = str_replace("&7", "§7", $msg);
		$msg = str_replace("&8", "§8", $msg);
		$msg = str_replace("&9", "§9", $msg);
		$msg = str_replace("&a", "§a", $msg);
		$msg = str_replace("&b", "§b", $msg);
		$msg = str_replace("&c", "§c", $msg);
		$msg = str_replace("&d", "§d", $msg);
		$msg = str_replace("&e", "§e", $msg);
		$msg = str_replace("&f", "§f", $msg);
		$msg = str_replace("&l", "§l", $msg);
		$msg = str_replace("&o", "§o", $msg);
		return $msg;
	}

	public function inBorder(Player $player){
		$spawn = $player->getSpawn();
		$toCheck = new Vector3($player->getX(),$player->getY(),$player->getZ());
		$first = new Vector3($spawn->getX()+20000,$spawn->getY(),$spawn->getZ()+20000);
		$second = new Vector3($spawn->getX()-20000,$spawn->getY(),$spawn->getZ()-20000);
		$isInside = (min($first->getX(),$second->getX()) <= $toCheck->getX()) && (max($first->getX(),$second->getX()) >= $toCheck->getX()) && (min($first->getZ(),$second->getZ()) <= $toCheck->getZ()) && (max($first->getZ(),$second->getZ()) >= $toCheck->getZ());
		return $isInside;
	}

	public function resetTag(Player $player){
		$name = $player->getName();
		foreach ($this->tags as $t){
			$name = str_replace("[".$t."]§r","", $name);
		}
		$player->setDisplayName($name);
		$tags = $this->getConfig()->getAll();
		$tags["Players"][$player->getName()] = "";
		$this->tagconfig->setAll($tags);
		$this->tagconfig->save();
	}

	public function getTag(Player $player){
		$config = $this->tagconfig;
		$tags = $config->getAll();
		if (!isset($tags["Players"][$player->getName()])){
			$tags["Players"][$player->getName()] = "";
			$this->tagconfig->setAll($tags);
			$this->tagconfig->save();
			return false;
 		}else{
 			$tag = $tags["Players"][$player->getName()];
 			if ($tag === "") return false;
 			return $tag;
 		}

	}

	public function setTag(Player $player, $tag){
		$name = $player->getName();
		foreach ($this->tags as $t){
			$name = str_replace("[".$t."]§r","", $name);
		}
		$player->setDisplayName($name.TextFormat::AQUA."[".$tag."]§r".TextFormat::RESET);
		$config = $this->tagconfig;
		$tags = $config->getAll();
		$tags["Players"][$player->getName()] = $tag;
		$this->tagconfig->setAll($tags);
		$this->tagconfig->save();
	}

	public function broadcastMsg($msg){
		$this->getServer()->broadcastMessage($msg);
	}

	// public function playerInteract(PlayerInteractEvent $ev){
	// 	$player = $ev->getPlayer();
	// 	if ($ev->getAction() === PlayerInteractEvent::RIGHT_CLICK_AIR){
	// 		if ($ev->getItem()->getName() == "§6Gun"){
	// 			if ($player->getInventory()->contains(new Item(350,0,1))){
	// 				$player->getInventory()->removeItem(new Item(350,0,1));
	// 				$count = 0;
	// 			 for($i=0;$i<36;$i++){
  // 			  		  $item = $player->getInventory()->getItem($i);
  // 			 		   if($item instanceof \pocketmine\item\CookedFish){
  // 					     $count = $count +$item->getCount();
  // 				   	 }
	// 			   }
	// 				$player->sendTip(TextFormat::AQUA."Bullets Left: ".$count);
	// 				$aimPos = $player->getDirectionVector();
	// 				$nbt = new Compound("", [
	// 						"Pos" => new Enum("Pos", [
	// 								new Double("", $player->x),
	// 								new Double("", $player->y + $player->getEyeHeight()),
	// 								new Double("", $player->z)
	// 						]),
	// 						"Motion" => new Enum("Motion", [
	// 								new Double("", $aimPos->x),
	// 								new Double("", $aimPos->y),
	// 								new Double("", $aimPos->z)
	// 						]),
	// 						"Rotation" => new Enum("Rotation", [
	// 								new Float("", $player->yaw),
	// 								new Float("", $player->pitch)
	// 						]),
	// 						"Health" => new Short("Health", 5),
	// 				]);
	// 				$f = 1.5;
	//
	// 				$bullet= Entity::createEntity("Snowball", $player->getLevel()->getChunk($player->getFloorX() >> 4, $player->getFloorZ() >> 4), $nbt, $player);
	// 				$bullet->setMotion($bullet->getMotion()->multiply($f));
	// 				$player->getLevel()->addSound(new LaunchSound($player), $player->getViewers());
	// 				$bullet->spawnToAll();
	// 			}else $player->sendMessage("§eKudaCraft§8>>§7 Need ammo!");
	// 		}
	//
	// 	}
	// }

	public function onTap(PlayerInteractEvent $ev){
		if ($ev->getPlayer()->isOp()){
			if (!$this->logged[$ev->getPlayer()->getName()] == true){
				$ev->getPlayer()->sendMessage("§eKudaCraft§8>>§7Do /ol {OPPASSWORD} to get access to the server!");
				$ev->setCancelled();
				return;
			}
		}
		switch ($ev->getItem()->getName()){
			case "§bShield":
				$this->useShield($ev->getPlayer());
			break;
		}
	}

	public function onBlockPlace(BlockPlaceEvent $ev){
		if ($ev->getPlayer()->isOp()){
			if (!$this->logged[$ev->getPlayer()->getName()] == true){
				$ev->getPlayer()->sendMessage("§eKudaCraft§8>>§7 Do /ol {OPPASSWORD} to get access to the server!");
				$ev->setCancelled();
				return;
			}
		}
 
		$block = $ev->getBlock();
		switch ($ev->getItem()->getName()){
			case "Lava":
				$ev->setCancelled();
			break;
			case "§aCommon Crate":
			case "§6Legendary Crate":
			case "§3Mythic Crate":
				$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"addItems"], [$block]), 20);
			break;
		}
	}

	public function addItems(Block $block, $type){
		$chest = $block->getLevel()->getTile(new Vector3($block->getX(), $block->getY(), $block->getZ()));
		if ($chest instanceof Chest){
		echo "adding item!";
		$nitems = rand(2, 10);
		$n = 0;
		while ($nitems > $n){
			$amount = rand(2, 5);
			$num = rand(0,count($this->items)-1);
			$i = $this->items[$num];
			$item = new Item($i[0],$i[1],$amount);
			$chest->getInventory()->addItem($item);
			$n++;
		}}
	}
/*
	public function useShield(Player $player){
		if (isset($this->shield[$player->getName()])){
			$time = $this->shield[$player->getName()];
			$time = $time * 30;
			$player->sendMessage("§eKudaCraft§8>>§7 You have ".gmdate("i:s", $time)." time left before you can use this ability again!");
		}
		if (!isset($this->shield[$player->getName()])){
			$this->shield[$player->getName()] = 10;
			$player->sendMessage("§eKudaCraft§8>>§7 You have used your shield and now have 30 secs of invincibility!");
			$player->sendMessage("§eKudaCraft§8>>§7 Note attacking while invincible will cause you to loose your invincibility!");
			if (!$player->hasPermission("drcore.shield")){
				$item = $player->getInventory()->getItemInHand();
				$player->getInventory()->removeItem($item);
			}
		}
	}
*/


}
