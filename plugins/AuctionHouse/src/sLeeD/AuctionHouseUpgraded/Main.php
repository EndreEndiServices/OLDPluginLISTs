<?php

namespace sLeeD\AuctionHouseUpgraded;

use sLeeD\AuctionHouseUpgraded\Task;
use sLeeD\AuctionHouseUpgraded\CooldownTask;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\nbt\tag\{CompoundTag, IntTag, StringTag, IntArrayTag};
use pocketmine\tile\Tile;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use onebone\economyapi\EconomyAPI;

class Main extends PluginBase implements Listener{
	
	public $auction;
	public $auctionItems = [];
	public $clickItem = [];
	public $block1 = [];
	public $block2 = [];
	public $inventory = [];
	public $ahCounter = [];
    public static $instance = null;
	
    public function onEnable() {
        self::$instance = $this;
        $this->getScheduler()->scheduleRepeatingTask(new CooldownTask($this, 25), 25);
        $this->config = new Config($this->getDataFolder(). "config.yml", Config::YAML, array(
            "cooldown-seconds" => 15,
            "has-cooldown-message" => "§c[§7CoolDown§c] {TIME} secondes !"
        ));
        $this->cooldown = new Config($this->getDataFolder(). "cooldowns.yml", Config::YAML);
        @mkdir($this->getDataFolder());
		if (!file_exists($this->getDataFolder() . "AuctionLog.txt")) {
			fopen($this->getDataFolder() . "AuctionLog.txt", "w");
		}
		$oldFile = file_get_contents($this->getDataFolder() . "AuctionLog.txt", FILE_USE_INCLUDE_PATH);
		$newFile = $oldFile . "\n\nAUCTION HOUSE LOG";
		file_put_contents($this->getDataFolder() . "AuctionLog.txt", $newFile);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->auction = new Config($this->getDataFolder() . "Auction.yml", Config::YAML);
		$this->refreshAuction();
		$this->settings = (new Config($this->getDataFolder() . "Settings.yml", Config::YAML, array(
			"Limit" => 5,
			"MinimumPrice" => 5,
			"MaximumPrice" => 10000000,
			"ExpirationInMinutes" => 5,
			"ExpirationInHours" => 0,
			"ExpirationInDays" => 1
		)))->getAll();
		$this->auctionSettings = (new Config($this->getDataFolder() . "AuctionSettings.yml", Config::YAML, array(
			"AuctionName" => "          §l§bAuction §eHouse",
			"ItemDisplay" => "§aItem: §b{itemName} \n\n§r§aSeller: §b{seller}\n§r§aPrice: §b{price}§r\n\n§r§aExpiration: §b{expiration}",
			"MyAuction" => "§l§aYour Auction\n§rYou currently have §b{myauction}§c/{auctionlimit} §aauctions",
			"Bin" => "§l§aCollection Bin\n§rClick here to view and collect all of your\nitems that expired from auction",
			"LeftArrow" => "§l§e<< Previous Page",
			"Refresh" => "§l§dREFRESH\n§rClick here to refresh the list",
			"RightArrow" => "§l§eNext Page >>",
			"HowToSell" => "§l§eHow to sell\n§rHold an item and type §a/ah sell <price>",
			"Guide" => "§l§aGuide\n§rAuctionHouse is a place where\nyou can trade your items to earn money",
			"AuctionBinName" => "          §l§bExpired §eItems",
			"BackToAuction" => "§l§aBack to Auction",
			"ClaimAll" => "§l§bClaim All"
		)))->getAll();
		$this->message = (new Config($this->getDataFolder() . "Message.yml", Config::YAML, array(
			"Prefix" => "§l§a[§bAH§a] §r",
			"HoldItem" => "§cPlease hold item in your hand§r",
			"SurvivalOnly" => "§cPlease switch to survival",
			"DontMove" => "§cPlease don't move while opening Auction",
			"AddAuctionSuccess" => "§fYou successfully add §b{itemName} §fx§b{itemCount} §ffor §b{price} §fon §aAuction§r",
			"ReachAuctionLimit" => "§cYou've reached auction limit§r",
			"InvalidPriceRange" => "§cOnly §b{minimumPrice} §cto §b{maximumPrice} §cprice are allowed§r",
			"InvalidPriceValue" => "§cPlease put a valid number§r",
			"InvalidItem" => "§cItem is not allowed",
			"ReceivedMoney" => "§fYou received §b{money} §ffrom §aauction§r",
			"PurchaseSuccess" => "§fYou purchased §b{itemName} §fx§b{itemCount} §ffor §b{price}§r",
			"NotEnoughMoney" => "§cYou do not have enough money§r",
			"FailedToOpen" => "§cFailed to open. Please try again later",
			"MoveUp" => "§cFailed to open. Please move 5 blocks up",
			"NoDupe" => "§cDupe glitch is not allowed!",
			"NotAvailable" => "§cItem is not available. Try to refresh§r"
		)))->getAll();
		$this->blockItems = (new Config($this->getDataFolder() . "BlockItems.yml", Config::YAML, array(
			"ItemID" => [
				1,
				0,
				0
			],
		)))->getAll();
		$this->db = new \SQLite3($this->getDataFolder() . "Auction.db");
		$this->db->exec("CREATE TABLE IF NOT EXISTS limits(player TEXT PRIMARY KEY, total INT);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS pending(player TEXT PRIMARY KEY, money INT);");
	}
    public static function getInstance() : self {
        return self::$instance;
    }

   public function onDisable(){
        $this->config->save();
        $this->cooldown->save();
    }
    public function convertSeconds(int $seconds) : string {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;   
        return "$hours:$minutes:$seconds";
    }
   public function formatMessage(string $message, $player) : string {
        $message = str_replace("{TIME}", $this->getCooldownTime($player), $message);
        $message = str_replace("{NAME}", $player->getName(), $message);
        return $message;
    }
	
    public function onCommand(CommandSender $sender, Command $cmd, string $label,array $args) : bool {
		switch($cmd->getName()){
			case "ah":

if($this->hasCooldown($sender)){
$sender->sendPopup($this->formatMessage($this->config->get("has-cooldown-message"), $sender));

}else if (!$this->hasCooldown($sender)){
				if($sender instanceof Player){
					if(count($args) === 0) {
						if($sender->getGamemode() == 0){
							if($sender->y <= 5){
								$sender->sendMessage($this->message["Prefix"] . $this->message["MoveUp"]);
								return true;
							}
							$this->writeLog($sender->getName() . " open Auction House");
			             $this->addCooldown($sender);
		$this->openAuctionHouse($sender);
						} else {
							$sender->sendMessage($this->message["Prefix"] . $this->message["SurvivalOnly"]);
						}
					}
					if(count($args) === 2) {
						if($args[0] == "sell"){
							if($sender->getGamemode() == 0){
								$item = $sender->getInventory()->getItemInHand();
								if($item->getId() === 0){
									$sender->sendMessage($this->message["Prefix"] . $this->message["HoldItem"]);
								} else {
									foreach($this->blockItems["ItemID"] as $itemID){
										if($item->getId() == $itemID){
											$sender->sendMessage($this->message["Prefix"] . $this->message["InvalidItem"]);
											return true;
										}
									}
									if(is_numeric($args[1])) {
										if($args[1] >= $this->settings["MinimumPrice"] && $args[1] <= $this->settings["MaximumPrice"]) {
											$name = $sender->getName();
											$result = $this->db->query("SELECT * FROM limits WHERE player = '$name';");
											$array = $result->fetchArray(SQLITE3_ASSOC);
											if($array['total'] < $this->settings["Limit"]) {
												$this->db->query("UPDATE limits SET total = total + 1 WHERE player = '$name'");
												$sender->getInventory()->setItemInHand(Item::get(0, 0, 0));
												$key = rand();
												if(isset($this->auctionItems[$key])){
													while(isset($this->auctionItems[$key])){
														$key = rand();
													}
												}
												if($item->hasCustomName() == 1){
													$customName = $item->getCustomName();
												} else {
													$customName = $item->getName();
												}
												$itemLore = [];
												foreach($item->getLore() as $lore){
													$itemLore[] = $lore;
												}
												if($itemLore == null){
													$itemLore[] = 99999;
												}
												$encId = [];
												if($item->hasEnchantments() == 1){
													foreach($item->getEnchantments() as $info){
														$enchants[] = $info->getId() . ":" . $info->getLevel();
													}
												} else {
													$enchants[] = "99999:99999";
												}
												$time = time();
												$day = ($this->settings["ExpirationInDays"] * 86400);
												$hour = ($this->settings["ExpirationInHours"] * 3600);
												$mins = ($this->settings["ExpirationInMinutes"] * 60);
												$expiration = $time + $day + $hour + $mins;
												$this->auction->set(
													$key, array(
														$sender->getName(),
														$item->getId(),
														$item->getDamage(),
														$item->getCount(),
														$customName,
														$itemLore,
														$enchants,
														$args[1],
														$expiration,
														$key
													)
												);
												$this->auction->save();
												$this->writeLog($sender->getName() . " sell an item in Auction House");
												$sender->sendMessage($this->message["Prefix"] . str_replace(["{itemName}", "{itemCount}", "{price}"], [$customName, $item->getCount(), $args[1]], $this->message["AddAuctionSuccess"]));
											} else {
												$sender->sendMessage($this->message["Prefix"] . $this->message["ReachAuctionLimit"]);
											}
										} else {
											$sender->sendMessage($this->message["Prefix"] . str_replace(["{minimumPrice}", "{maximumPrice}"], [$this->settings["MinimumPrice"], $this->settings["MaximumPrice"]], $this->message["InvalidPriceRange"]));
										}
									} else {
										$sender->sendMessage($this->message["Prefix"] . $this->message["InvalidPriceValue"]);
									}
								}
							} else {
								$sender->sendMessage($this->message["Prefix"] . $this->message["SurvivalOnly"]);
							}
						}
					}
				}
			break;
}
		}
		return true;
    }
	
	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$result = $this->db->query("SELECT * FROM pending WHERE player = '$name';");
		$array = $result->fetchArray(SQLITE3_ASSOC);	
		if (empty($array)) {
			$pending = $this->db->prepare("INSERT INTO pending(player, money) VALUES (:player, :money);");
			$pending->bindValue(":player", $name);
			$pending->bindValue(":money", 0);
			$pending->execute();
		} else {
			$money = $array['money'];
			if($money >= 1){
				EconomyAPI::getInstance()->addMoney($player, $money);
				$this->db->query("UPDATE pending SET money = 0 WHERE player = '$name'");
				$player->sendMessage($this->message["Prefix"] . str_replace(["{money}"], [$money], $this->message["ReceivedMoney"]));
			}
		}
		$result = $this->db->query("SELECT * FROM limits WHERE player = '$name';");
		$array = $result->fetchArray(SQLITE3_ASSOC);	
		if (empty($array)) {
			$limit = $this->db->prepare("INSERT INTO limits(player, total) VALUES (:player, :total);");
			$limit->bindValue(":player", $name);
			$limit->bindValue(":total", 0);
			$limit->execute();
		}
	}

	public function onCloseWindow(InventoryCloseEvent $event){
		$player = $event->getPlayer();
		$this->sendRealBlock($player);
	}
	
	public function openAuctionHouse($player){
		$this->writeLog($player->getName() . " opening Auction House");
		$b1 = $player->getLevel()->getBlockAt((int)$player->x, (int)$player->y - 4, (int)$player->z);
		$b2 = $player->getLevel()->getBlockAt((int)$player->x + 1, (int)$player->y - 4, (int)$player->z);
		if($b1->getId() == 54){
			$player->sendMessage($this->message["Prefix"] . $this->message["MoveUp"]);
			return true;
		}
		if($b2->getId() == 54){
			$player->sendMessage($this->message["Prefix"] . $this->message["MoveUp"]);
			return true;
		}
		$this->block1[$player->getName()] = $player->getLevel()->getBlockAt((int)$player->x, (int)$player->y - 4, (int)$player->z);
		$this->block2[$player->getName()] = $player->getLevel()->getBlockAt((int)$player->x + 1, (int)$player->y - 4, (int)$player->z);
		$nbt = new CompoundTag(" ", [
			new StringTag("id", Tile::CHEST),
			new StringTag("CustomName", $this->auctionSettings["AuctionName"]),
			new IntTag("x", (int)$player->x),
			new IntTag("y", (int)$player->y - 4),
			new IntTag("z", (int)$player->z)
		]);
		$leftChest = Tile::createTile("Chest", $player->getLevel(), $nbt);
		$nbt = new CompoundTag(" ", [
			new StringTag("id", Tile::CHEST),
			new StringTag("CustomName", $this->auctionSettings["AuctionName"]),
			new IntTag("x", (int)$player->x + 1),
			new IntTag("y", (int)$player->y - 4),
			new IntTag("z", (int)$player->z)
		]);
		$rightChest = Tile::createTile("Chest", $player->getLevel(), $nbt);
		$leftChest->pairWith($rightChest);
		$rightChest->pairWith($leftChest);
		$block = Block::get(Block::CHEST)->setComponents($leftChest->x, $leftChest->y, $leftChest->z);
		$block2 = Block::get(Block::CHEST)->setComponents($rightChest->x, $rightChest->y, $rightChest->z);
		$player->getLevel()->sendBlocks([$player], [$block, $block2]);
		$this->inventory[$player->getName()] = $leftChest->getInventory();
		$this->addAuctionItems($player, $this->inventory[$player->getName()]);
		$this->getScheduler()->scheduleDelayedTask(new Task\AHWindow($this, $player, $this->inventory[$player->getName()]), 15);
	}

	public function openBin($player){
		$this->writeLog($player->getName() . " opening Bin");
		$b1 = $player->getLevel()->getBlockAt((int)$player->x, (int)$player->y - 4, (int)$player->z);
		$b2 = $player->getLevel()->getBlockAt((int)$player->x + 1, (int)$player->y - 4, (int)$player->z);
		if($b1->getId() == 54){
			$player->sendMessage($this->message["Prefix"] . $this->message["MoveUp"]);
			return true;
		}
		if($b2->getId() == 54){
			$player->sendMessage($this->message["Prefix"] . $this->message["MoveUp"]);
			return true;
		}
		$this->block1[$player->getName()] = $player->getLevel()->getBlockAt((int)$player->x, (int)$player->y - 4, (int)$player->z);
		$this->block2[$player->getName()] = $player->getLevel()->getBlockAt((int)$player->x + 1, (int)$player->y - 4, (int)$player->z);
		$nbt = new CompoundTag(" ", [
			new StringTag("id", Tile::CHEST),
			new StringTag("CustomName", $this->auctionSettings["AuctionBinName"]),
			new IntTag("x", (int)$player->x),
			new IntTag("y", (int)$player->y - 4),
			new IntTag("z", (int)$player->z)
		]);
		$leftChest = Tile::createTile("Chest", $player->getLevel(), $nbt);
		$nbt = new CompoundTag(" ", [
			new StringTag("id", Tile::CHEST),
			new StringTag("CustomName", $this->auctionSettings["AuctionBinName"]),
			new IntTag("x", (int)$player->x + 1),
			new IntTag("y",  (int)$player->y - 4),
			new IntTag("z", (int)$player->z)
		]);
		$rightChest = Tile::createTile("Chest", $player->getLevel(), $nbt);
		$leftChest->pairWith($rightChest);
		$rightChest->pairWith($leftChest);
		$block = Block::get(Block::CHEST)->setComponents($leftChest->x, $leftChest->y, $leftChest->z);
		$block2 = Block::get(Block::CHEST)->setComponents($rightChest->x, $rightChest->y, $rightChest->z);
		$player->getLevel()->sendBlocks([$player], [$block, $block2]);
		$this->inventory[$player->getName()] = $leftChest->getInventory();
		$this->addBinItems($player, $this->inventory[$player->getName()]);
		$this->getScheduler()->scheduleDelayedTask(new Task\AHWindow($this, $player, $this->inventory[$player->getName()]), 15);
	}

	public function addBinItems($player, $inventory, int $page = 0){
		$this->writeLog($player->getName() . " adding Bin Items");
		$this->refreshAuction();
		if($inventory->getDefaultSize() == 54){
			$inventory->clearAll();
			if(!empty($this->auctionItems)) {
				$auction = yaml_parse_file($this->getDataFolder() . "Auction.yml");
				$ahBinItems = [];
				foreach($auction as $key => $val) $ahBinItems[$key] = $val;
				foreach($ahBinItems as $data){
					$timeNow = time();
					if($data[8] < $timeNow){
						if($data[0] != $player->getName()){
							unset($ahBinItems[$data[9]]);
						}
					}
				}
				if(!empty($ahBinItems)) {
					$chunked = array_chunk($ahBinItems, 44, true);
					if($page < 0){
						$page = count($chunked) - 1;
					}
					$page = isset($chunked[$page]) ? $page : 0;
					foreach($chunked[$page] as $data){
						$timeNow = time();
						if($data[8] < $timeNow){
							if($data[0] == $player->getName()){
								$item = Item::get($data[1], $data[2], $data[3]);
								$item->setCustomName($data[4]);
								$item->setNamedTagEntry(new StringTag("AHUBinMenus", "contents")); // MENUS
								foreach($data[5] as $lore){
									if($lore != 99999){
										$item->setLore($data[5]);
									}
								}
								foreach($data[6] as $enchant) {
									$enchant = explode(':', $enchant);
									$encId = $enchant[0];
									$encLvl = $enchant[1];
									if($encId != 99999 && $encLvl != 99999){
										$enchantment = Enchantment::getEnchantment($encId);
										if($enchantment != null){
											$enchInstance = new EnchantmentInstance($enchantment, $encLvl);
											$item->addEnchantment($enchInstance);
										}
									}
								}
								$inventory->addItem($item);
							}
						}
					}
				}
			}
			$item = Item::get(264, 0, 1);
			$item->setCustomName($this->auctionSettings["BackToAuction"]);
			$item->setNamedTagEntry(new StringTag("AHUBinMenus", "backtoauction"));
			$inventory->setItem(45, $item);
			$item = Item::get(339, 0, 1);
			$item->setCustomName($this->auctionSettings["LeftArrow"]);
			$item->setNamedTagEntry(new IntArrayTag('binturner', [0, $page]));
			$inventory->setItem(48, $item);
			$item = Item::get(54, 0, 1);
			$item->setCustomName($this->auctionSettings["ClaimAll"]);
			$item->setNamedTagEntry(new StringTag("AHUBinMenus", "claimall"));
			$inventory->setItem(49, $item);
			$item = Item::get(339, 0, 1);
			$item->setCustomName($this->auctionSettings["RightArrow"]);
			$item->setNamedTagEntry(new IntArrayTag('binturner', [1, $page]));
			$inventory->setItem(50, $item);
			$item = Item::get(340, 0, 1);
			$item->setCustomName($this->auctionSettings["Guide"]);
			$item->setNamedTagEntry(new StringTag("AHUBinMenus", "guide"));
			$inventory->setItem(53, $item);
		} else {
			$player->sendMessage($this->message["Prefix"] . $this->message["FailedToOpen"]);
		}
	}
	
	public function getBinItems($player, $inventory, int $page = 0){
		$this->refreshAuction();
		if(!empty($this->auctionItems)) {
			$auction = yaml_parse_file($this->getDataFolder() . "Auction.yml");
			$ahBinItems = [];
			foreach($auction as $key => $val) $ahBinItems[$key] = $val;
			foreach($ahBinItems as $data){
				$timeNow = time();
				if($data[8] < $timeNow){
					if($data[0] != $player->getName()){
						unset($ahBinItems[$data[9]]);
					}
				}
			}
			if(!empty($ahBinItems)) {
				$chunked = array_chunk($ahBinItems, 44, true);
				if($page < 0){
					$page = count($chunked) - 1;
				}
				$page = isset($chunked[$page]) ? $page : 0;
				foreach($chunked[$page] as $data){
					$timeNow = time();
					if($data[8] < $timeNow){
						if($data[0] == $player->getName()){
							$item = Item::get($data[1], $data[2], $data[3]);
							$item->setCustomName($data[4]);
							$item->setNamedTagEntry(new StringTag("AHUBinMenus", "contents"));
							foreach($data[5] as $lore){
								if($lore != 99999){
									$item->setLore($data[5]);
								}
							}
							foreach($data[6] as $enchant) {
								$enchant = explode(':', $enchant);
								$encId = $enchant[0];
								$encLvl = $enchant[1];
								if($encId != 99999 && $encLvl != 99999){
									$enchantment = Enchantment::getEnchantment($encId);
									if($enchantment != null){
										$enchInstance = new EnchantmentInstance($enchantment, $encLvl);
										$item->addEnchantment($enchInstance);
									}
								}
							}
							$player->getInventory()->addItem($item);
							$name = $player->getName();
							$this->db->query("UPDATE limits SET total = total - 1 WHERE player = '$name'");
							$this->auction->remove($data[9]);
							$this->auction->save();
							$this->addBinItems($player, $inventory);
						}
					}
				}
			}
		}
	}

	public function sendRealBlock($player){
		if(isset($this->block1[$player->getName()]) && isset($this->block2[$player->getName()]) && isset($this->inventory[$player->getName()])){
			$this->inventory[$player->getName()]->clearAll();
			$player->getLevel()->sendBlocks([$player], [$this->block1[$player->getName()], $this->block2[$player->getName()]]);
			if($this->block1[$player->getName()]->getId() == 54){
				$player->getLevel()->setBlock(new Vector3($this->block1[$player->getName()]->x, $this->block1[$player->getName()]->y, $this->block1[$player->getName()]->z), new Block(0, 0), true);
			}
			if($this->block2[$player->getName()]->getId() == 54){
				$player->getLevel()->setBlock(new Vector3($this->block2[$player->getName()]->x, $this->block2[$player->getName()]->y, $this->block2[$player->getName()]->z), new Block(0, 0), true);
			}
			unset($this->block1[$player->getName()]);
			unset($this->block2[$player->getName()]);
			unset($this->inventory[$player->getName()]);
		}
	}
	
	public function refreshAuction(){
		$auction = yaml_parse_file($this->getDataFolder() . "Auction.yml");
		$this->auctionItems = [];
		if(!empty($auction)) foreach($auction as $key => $val) $this->auctionItems[$key] = $val;
	}

	public function addAuctionItems($player, $inventory, int $page = 0){
		$this->writeLog($player->getName() . " adding Auction Items");
		$this->refreshAuction();
		if($inventory->getDefaultSize() == 54){
			$inventory->clearAll();
			if(!empty($this->auctionItems)) {
				$auction = yaml_parse_file($this->getDataFolder() . "Auction.yml");
				$ahItems = [];
				foreach($auction as $key => $val) $ahItems[$key] = $val;
				foreach($ahItems as $data){
					$timeNow = time();
					if($data[8] < $timeNow){
						unset($ahItems[$data[9]]);
					}
				}
				if(!empty($ahItems)) {
					$chunked = array_chunk($ahItems, 44, true);
					if($page < 0){
						$page = count($chunked) - 1;
					}
					$page = isset($chunked[$page]) ? $page : 0;
					foreach($chunked[$page] as $data){
						$timeNow = time();
						if($data[8] > $timeNow){
							$item = Item::get($data[1], $data[2], $data[3]);
							$remainingTime = $data[8] - $timeNow;
							$day = floor($remainingTime / 86400);
							$hourSeconds = $remainingTime % 86400;
							$hour = floor($hourSeconds / 3600);
							$minuteSec = $hourSeconds % 3600;
							$minute = floor($minuteSec / 60);
							$remainingSec = $minuteSec % 60;
							$second = ceil($remainingSec);
							if($day >= 1){
								$expiration = $day . " day(s)";
							} else {
								if($hour >= 1){
									$expiration = $hour . " hour(s) & " . $minute . " minute(s)";
								} else {
									if($minute >= 1){
										$expiration = $minute . " minute(s) & " . $second . " second(s)";
									} else {
										$expiration = $second . " second(s)";
									}
								}
							}
							$item->setNamedTagEntry(new IntArrayTag("AHUcontents", [$data[7], $data[9]]));
							$item->setCustomName(str_replace(["{itemName}", "{seller}", "{price}", "{expiration}"], [$data[4], $data[0], $data[7], $expiration], $this->auctionSettings["ItemDisplay"]));
							foreach($data[5] as $lore){
								if($lore != 99999){
									$item->setLore($data[5]);
								}
							}
							foreach($data[6] as $enchant) {
								$enchant = explode(':', $enchant);
								$encId = $enchant[0];
								$encLvl = $enchant[1];
								if($encId != 99999 && $encLvl != 99999){
									$enchantment = Enchantment::getEnchantment($encId);
									if($enchantment != null){
										$enchInstance = new EnchantmentInstance($enchantment, $encLvl);
										$item->addEnchantment($enchInstance);
									}
								}
							}
							$inventory->addItem($item);
						}
					}
				}
			}
			$name = $player->getName();
			$result = $this->db->query("SELECT * FROM limits WHERE player = '$name';");
			$array = $result->fetchArray(SQLITE3_ASSOC);	
			if (empty($array)) {
				$limit = $this->db->prepare("INSERT INTO limits(player, total) VALUES (:player, :total);");
				$limit->bindValue(":player", $name);
				$limit->bindValue(":total", 0);
				$limit->execute();
			}
			$item = Item::get(264, 0, 1);
			$item->setCustomName(str_replace(["{myauction}", "{auctionlimit}"], [$array['total'], $this->settings["Limit"]], $this->auctionSettings["MyAuction"]));
			$item->setNamedTagEntry(new StringTag("AHUmenus", "myauction"));
			$inventory->setItem(45, $item);
			$item = Item::get(130, 0, 1);
			$item->setCustomName($this->auctionSettings["Bin"]);
			$item->setNamedTagEntry(new StringTag("AHUmenus", "bin"));
			$inventory->setItem(46, $item);
			$item = Item::get(339, 0, 1);
			$item->setCustomName($this->auctionSettings["LeftArrow"]);
			$item->setNamedTagEntry(new IntArrayTag('turner', [0, $page]));
			$inventory->setItem(48, $item);
			$item = Item::get(54, 0, 1);
			$item->setCustomName($this->auctionSettings["Refresh"]);
			$item->setNamedTagEntry(new StringTag("AHUmenus", "refresh"));
			$inventory->setItem(49, $item);
			$item = Item::get(339, 0, 1);
			$item->setCustomName($this->auctionSettings["RightArrow"]);
			$item->setNamedTagEntry(new IntArrayTag('turner', [1, $page]));
			$inventory->setItem(50, $item);
			$item = Item::get(266, 0, 1);
			$item->setCustomName($this->auctionSettings["HowToSell"]);
			$item->setNamedTagEntry(new StringTag("AHUmenus", "howtosell"));
			$inventory->setItem(52, $item);
			$item = Item::get(340, 0, 1);
			$item->setCustomName($this->auctionSettings["Guide"]);
			$item->setNamedTagEntry(new StringTag("AHUmenus", "guide"));
			$inventory->setItem(53, $item);
		} else {
			$player->sendMessage($this->message["Prefix"] . $this->message["FailedToOpen"]);
		}
	}

	public function onTransaction(InventoryTransactionEvent $event){
		$transactions = $event->getTransaction()->getActions();
		$player = null;
		$chestinv = null;
		$action = null;
		foreach($transactions as $transaction){
			if($transaction instanceof SlotChangeAction) {
				/*
				if(($inv = $transaction->getInventory()) instanceof PlayerInventory){
					$player = $transaction->getInventory()->getHolder();
					$action = $transaction;
					$item = $action->getSourceItem();
					if($item->getNamedTag()->hasTag("AHUmenus")){
						$event->setCancelled(true);
						$player->getInventory()->clearAll();
						$player->sendMessage($this->message["Prefix"] . $this->message["NoDupe"]);
					}
					if($item->getNamedTag()->hasTag("turner")){
						$event->setCancelled(true);
						$player->getInventory()->clearAll();
						$player->sendMessage($this->message["Prefix"] . $this->message["NoDupe"]);
					}
					if($item->getNamedTag()->hasTag("AHUcontents")){
						$event->setCancelled(true);
						$player->getInventory()->clearAll();
						$player->sendMessage($this->message["Prefix"] . $this->message["NoDupe"]);
					}
					if($item->getNamedTag()->hasTag("AHUBinMenus")){
						$event->setCancelled(true);
						$player->getInventory()->clearAll();
						$player->sendMessage($this->message["Prefix"] . $this->message["NoDupe"]);
					}
					if($item->getNamedTag()->hasTag("binturner")){
						$event->setCancelled(true);
						$player->getInventory()->clearAll();
						$player->sendMessage($this->message["Prefix"] . $this->message["NoDupe"]);
					}
				}
				*/
				if(($inv = $transaction->getInventory()) instanceof ChestInventory){
					foreach($inv->getViewers() as $assumed){
						if($assumed instanceof Player){
							$player = $assumed;
							$chestinv = $inv;
							$action = $transaction;
							if(($player ?? $chestinv ?? $action) === null){
								return;
							}
							if($player->getGamemode() == 0){
								$item = $action->getSourceItem();
								if($item->getId() === Item::AIR){
									$this->writeLog($player->getName() . " Try to click Air Item in Auction House");
									return;
								}
								if($item->getNamedTag()->hasTag("AHUmenus")){
									$event->setCancelled(true);
									$menu = $item->getNamedTag()->getString("AHUmenus");
									if($menu == "bin"){
										$this->writeLog($player->getName() . " click Bin");
										$this->sendRealBlock($player);
										$this->getScheduler()->scheduleDelayedTask(new Task\AHBin($this, $player), 15);
									}
									if($menu == "refresh"){
										$this->writeLog($player->getName() . " click Refresh");
										$this->addAuctionItems($player, $chestinv);
									}
								}
								if($item->getNamedTag()->hasTag("turner")){
									$event->setCancelled(true);
									$this->writeLog($player->getName() . " click Page");
									$pagedata = $item->getNamedTag()->getIntArray("turner");
									$page = $pagedata[0] === 0 ? --$pagedata[1] : ++$pagedata[1];
									$this->addAuctionItems($player, $chestinv, $page);
								}
								if($item->getNamedTag()->hasTag("AHUcontents")){
									$event->setCancelled(true);
									$data = $item->getNamedTag()->getIntArray("AHUcontents");
									if(!isset($this->clickItem[$player->getName()])){
										$this->clickItem[$player->getName()] = $data[1];
									} else {
										if($this->clickItem[$player->getName()] != $data[1]){
											unset($this->clickItem[$player->getName()]);
										} else {
											unset($this->clickItem[$player->getName()]);
											$this->refreshAuction();
											if(isset($this->auctionItems[$data[1]])){
												if(EconomyAPI::getInstance()->myMoney($player) >= $data[0]){
													$itemData = $this->auctionItems[$data[1]] ?? null;
													if($itemData !== null){
														$this->writeLog($player->getName() . " bought an item with ID: " . $data[1]);
														$item = Item::get($itemData[1], $itemData[2], $itemData[3]);
														$item->setCustomName($itemData[4]);
														foreach($itemData[5] as $lore){
															if($lore != 99999){
																$item->setLore($itemData[5]);
															}
														}
														foreach($itemData[6] as $enchant) {
															$enchant = explode(':', $enchant);
															$encId = $enchant[0];
															$encLvl = $enchant[1];
															if($encId != 99999 && $encLvl != 99999){
																$enchantment = Enchantment::getEnchantment($encId);
																$enchInstance = new EnchantmentInstance($enchantment, $encLvl);
																$item->addEnchantment($enchInstance);
															}
														}
														$player->getInventory()->addItem($item);
														$seller = $this->getServer()->getPlayer($itemData[0]);
														if($seller instanceof Player){
															EconomyAPI::getInstance()->addMoney($seller, $itemData[7]);
															$seller->sendMessage($this->message["Prefix"] . str_replace(["{money}"], [$itemData[7]], $this->message["ReceivedMoney"]));
														} else {
															$sellerName = $itemData[0];
															$money = $itemData[7];
															$this->db->query("UPDATE pending SET money = money + '$money' WHERE player = '$sellerName'");
														}
														$sellerName = $itemData[0];
														$this->db->query("UPDATE limits SET total = total - 1 WHERE player = '$sellerName'");
														EconomyAPI::getInstance()->reduceMoney($player, $itemData[7]);
														$player->sendMessage($this->message["Prefix"] . str_replace(["{itemName}", "{itemCount}", "{price}"], [$itemData[4], $itemData[3], $itemData[7]], $this->message["PurchaseSuccess"]));
														$this->auction->remove($data[1]);
														$this->auction->save();
														$this->addAuctionItems($player, $chestinv);
														$this->writeLog($player->getName() . " received the item");
													}
												} else {
													$player->sendMessage($this->message["Prefix"] . $this->message["NotEnoughMoney"]);
												}
											} else {
												$player->sendMessage($this->message["Prefix"] . $this->message["NotAvailable"]);
											}
										}
									}
								}
								if($item->getNamedTag()->hasTag("AHUBinMenus")){
									$event->setCancelled(true);
									$menu = $item->getNamedTag()->getString("AHUBinMenus");
									if($menu == "backtoauction"){
										$this->writeLog($player->getName() . " click Back To Auction");
										$this->sendRealBlock($player);
										$this->getScheduler()->scheduleDelayedTask(new Task\AHAuction($this, $player), 15);
									}
									if($menu == "claimall"){
										$this->writeLog($player->getName() . " click Claim All");
										$this->getBinItems($player, $chestinv);
									}
								}
								if($item->getNamedTag()->hasTag("binturner")){
									$event->setCancelled(true);
									$this->writeLog($player->getName() . " click Bin Page");
									$pagedata = $item->getNamedTag()->getIntArray("binturner");
									$page = $pagedata[0] === 0 ? --$pagedata[1] : ++$pagedata[1];
									$this->addBinItems($player, $chestinv, $page);
								}
							}
						}
					}
				}
			}
		}
	}
	
	public function onInteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();
		foreach($this->block1 as $block1){
			if($block->x == $block1->x && $block->y == $block1->y && $block->z == $block1->z){
				$event->setCancelled(true);
			}
		}
		foreach($this->block2 as $block2){
			if($block->x == $block2->x && $block->y == $block2->y && $block->z == $block2->z){
				$event->setCancelled(true);
			}
		}
	}
	
	public function onBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();
		foreach($this->block1 as $block1){
			if($block->x == $block1->x && $block->y == $block1->y && $block->z == $block1->z){
				$event->setCancelled(true);
			}
		}
		foreach($this->block2 as $block2){
			if($block->x == $block2->x && $block->y == $block2->y && $block->z == $block2->z){
				$event->setCancelled(true);
			}
		}
	}

	public function onPlace(BlockPlaceEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();
		foreach($this->block1 as $block1){
			if($block->x == $block1->x && $block->y == $block1->y && $block->z == $block1->z){
				$event->setCancelled(true);
			}
		}
		foreach($this->block2 as $block2){
			if($block->x == $block2->x && $block->y == $block2->y && $block->z == $block2->z){
				$event->setCancelled(true);
			}
		}
	}
	
	public function writeLog($log){
		$oldFile = file_get_contents($this->getDataFolder() . "AuctionLog.txt", FILE_USE_INCLUDE_PATH);
		$date = date("M-d-Y H:i:s");
		$newFile = $oldFile . "\n" . $date . " - " . $log;
		file_put_contents($this->getDataFolder() . "AuctionLog.txt", $newFile);
	}

    public function timer(){
        foreach($this->cooldown->getAll() as $player => $time){
		    $time--;
		    $this->cooldown->set($player, $time);
		    $this->cooldown->save();
		    if($time == 0){
		        $this->cooldown->remove($player);
			    $this->cooldown->save();
            }
        }
    }

    public function hasCooldown($player){
        return $this->cooldown->exists($player->getLowerCaseName());
    }

    public function getCooldownSeconds($player){
        return $this->cooldown->get($player->getLowerCaseName());
    }

    public function getCooldownTime($player){
        return $this->convertSeconds($this->getCooldownSeconds($player));
    }

    public function addCooldown($player){
        $this->cooldown->set($player->getLowerCaseName(), $this->config->get("cooldown-seconds"));
        $this->cooldown->save();
    }

}
