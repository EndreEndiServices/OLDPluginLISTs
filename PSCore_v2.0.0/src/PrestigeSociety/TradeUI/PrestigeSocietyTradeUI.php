<?php

namespace PrestigeSociety\TradeUI;

use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;
use PrestigeSociety\UIForms\CustomForm;
use PrestigeSociety\UIForms\SimpleForm;

class PrestigeSocietyTradeUI {

	/** @var PrestigeSocietyCore */
	public $core;
	/** @var array */
	protected $cache = [];
	/** @var array */
	protected $queue = [];
	/**
	 *
	 * @var int $SELL_UI_ID
	 * @var int $SHOP_UI_ID
	 *
	 */
	private $BUY_OR_SELL_UI_ID = 0, $CHOOSE_ITEM_UI_ID = 0, $SELL_UI_ID = 0, $SHOP_UI_ID = 0, $CONFIRM_PURCHASE_UI_ID = 0, $MY_OFFERS_UI_ID = 0, $CONFIRM_DELETE_OFFER_UI_ID = 0;
	/** @var \SQLite3 */
	private $db;

	/**
	 *
	 * EvListener constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		$this->core = $c;
	}

	public function init(){
		$this->BUY_OR_SELL_UI_ID = mt_rand(111111, 999999);
		$this->CHOOSE_ITEM_UI_ID = $this->BUY_OR_SELL_UI_ID + 10;
		$this->SELL_UI_ID = $this->CHOOSE_ITEM_UI_ID + 10;
		$this->SHOP_UI_ID = $this->SELL_UI_ID + 10;
		$this->CONFIRM_PURCHASE_UI_ID = $this->SHOP_UI_ID + 10;
		$this->MY_OFFERS_UI_ID = $this->CONFIRM_PURCHASE_UI_ID + 10;
		$this->CONFIRM_DELETE_OFFER_UI_ID = $this->MY_OFFERS_UI_ID + 10;
		$this->db = new \SQLite3($this->core->databasesFolder() . "auctionHouse.db");
		$this->db->exec("CREATE TABLE IF NOT EXISTS history (username VARCHAR, trade VARCHAR, itemId INT, itemMeta INT, price INT, amount INT, id INT, enchants VARCHAR)"); //username, traded item, price, amount, id
		$this->registerCommands();
		$this->core->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this->core);
		if(!file_exists($this->core->getDataFolder() . "cache.data")){
			$this->saveCache();
		}

		/*$v = [];

		$q = $this->db->query("SELECT id FROM history ORDER BY id ASC");
		while($qv = $q->fetchArray(SQLITE3_ASSOC)){
				$v[] = $qv['id'];
		}

		var_dump($v);*/

		$this->loadCache();
	}

	public function registerCommands(){
		/*$commands = [
			"ah" => new Ah($this),
			"cart" => new Cart($this),
			"myoffers" => new MyOffers($this)
		];
		foreach($commands as $prefix => $command){
				$this->core->getServer()->getCommandMap()->register($prefix, $command);
		}*/
	}

	public function saveCache(){
		file_put_contents($this->core->getDataFolder() . "cache.data", gzencode(json_encode($this->cache)));
	}

	public function loadCache(){
		$this->cache = json_decode(gzdecode(file_get_contents($this->core->getDataFolder() . "cache.data")), true);
	}

	/**
	 *
	 * @return string
	 *
	 */
	public function getRandomColor(){
		$colors = ["a", "b", "c", "d", "e", "6", "5"];

		return '§' . $colors[array_rand($colors)];
	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function getBuyOrSellForm(Player $player){
		$form = new SimpleForm();
		$form->setId($this->BUY_OR_SELL_UI_ID);
		$form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lDO YOU WANT TO BUY OR SELL?&r&k&e|"));
		$content = "";
		$content .= RandomUtils::colorMessage("&l&e===========================\n");
		$content .= RandomUtils::colorMessage("&r&dHey there, {$player->getName()}!\n\n");
		$content .= RandomUtils::colorMessage("&r&dIf you want to buy items from the\n");
		$content .= RandomUtils::colorMessage("&r&dother players, click 'BUY'!\n\n");
		$content .= RandomUtils::colorMessage("&r&dIf you want to sell your own items\n");
		$content .= RandomUtils::colorMessage("&r&dclick 'SELL'!\n");
		$content .= RandomUtils::colorMessage("&l&e===========================\n");
		$form->setContent($content);
		$form->setButton(RandomUtils::colorMessage("&e&k|&r&d&lBUY&r&k&e|"));
		$form->setButton(RandomUtils::colorMessage("&e&k|&r&d&lSELL&r&k&e|"));
		$form->send($player);
	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function getMyOffersUI(Player $player){
		$s = $this->fetchFromUsername($player->getLowerCaseName());
		if(count($s) != 0){
			$form = new SimpleForm();
			$form->setId($this->MY_OFFERS_UI_ID);
			$form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lYOUR OFFERS&r&k&e|"));
			foreach($s as $r){
				$this->cache[$player->getLowerCaseName()]["ids"][] = $r['id'];
				$form->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&d{$r['trade']} &d(&8x{$r['amount']}&d)&e&k|"), "http://permat.comli.com/items/{$r['itemId']}-{$r['itemMeta']}.png");
			}
			$form->send($player);
		}else{
			$player->sendMessage(RandomUtils::colorMessage($this->core->getMessage('auction_house', 'no_offers')));
		}
	}

	/**
	 *
	 * @param string $from
	 *
	 * @return array
	 *
	 */
	public function fetchFromUsername(string $from): array{
		$out = [];
		$q = $this->db->query("SELECT * FROM history WHERE username = '$from'");
		while($row = $q->fetchArray(SQLITE3_ASSOC)){
			$out[] = $row;
		}

		return $out;
	}

	/**
	 *
	 * @param Player $player
	 * @param mixed $formData
	 * @param int $formId
	 *
	 */
	public function handleFormResponse(Player $player, $formData, int $formId){
		switch($formId){
			case $this->BUY_OR_SELL_UI_ID:
				{
					if($formData === 0){
						$this->getShopForm($player);
					}else{
						if(count($player->getInventory()->getContents()) === 0){
							$player->sendMessage(RandomUtils::colorMessage($this->core->getMessage('auction_house', 'no_empty')));

							return;
						}
						$this->getChooseItemForm($player);
					}
				}
				break;
			case $this->CHOOSE_ITEM_UI_ID:
				{
					$index = $this->getCache($player)["items"][$formData];
					$item = $player->getInventory()->getItem($index);
					$this->getSellForm($player, $item);
					$this->resetCache($player);
					$this->cache[$player->getLowerCaseName()]["item"] = $index;
				}
				break;
			case $this->SHOP_UI_ID:
				{
					$ids = $this->cache[$player->getLowerCaseName()]["ids"];
					if(!isset($ids[$formData])){
						if($formData === 0){
							$this->queue[$player->getPlayer()]['type'] = 0;
							if(!$this->getShopForm($player)){
								$player->sendMessage(RandomUtils::colorMessage($this->core->getMessage('auction_house', 'no_more_items')));
							}
						}else{
							$this->queue[$player->getLowerCaseName()]['type'] = 1;
							if(!$this->getShopForm($player)){
								$player->sendMessage(RandomUtils::colorMessage($this->core->getMessage('auction_house', 'no_more_items')));
							}
						}

						return;
					}

					$id = $ids[$formData];
					$this->getConfirmPurchaseForm($player, $id);
					$this->resetCache($player);
					$this->cache[$player->getLowerCaseName()]["id"] = $id;
				}
				break;
			case $this->SELL_UI_ID:
				{
					if($formData[0] !== (string)(int)($formData[0]) or $formData[0] === null){
						$player->sendMessage(RandomUtils::colorMessage($this->core->getMessage('auction_house', 'enter_valid_price')));

						return;
					}
					$item = $player->getInventory()->getItem($this->getCache($player)["item"]);
					$c = $this->getCountFromItem($player, $item);
					if($c < $formData[1]){
						$player->sendMessage(RandomUtils::colorMessage(str_replace(['@chose', '@have'], [$formData[1], $c], $this->core->getMessage('auction_house', 'not_enough_to_sell'))));

						return;
					}
					$item->setCount($formData[1]);
					$player->getInventory()->removeItem($item);
					$enchants = [];
					foreach($item->getEnchantments() as $enchantment){
						$enchants[] = $enchantment->getType()->getName() . ':' . $enchantment->getLevel();
					}
					$this->insert($player->getLowerCaseName(), $item->getName(), $item->getId(), $item->getDamage(), (int)$formData[0], $formData[1], implode(';', $enchants));
					$this->core->getServer()->broadcastMessage(RandomUtils::colorMessage(str_replace(["@player", "@item", "@price", "@amount"], [$player->getName(), $item->getName(), $formData[0], $formData[1]], $this->core->getMessage('auction_house', 'selling_item'))));
					//$this->core->getServer()->broadcastMessage(TextFormat::GREEN . "To view, type: " . TextFormat::RED . "/ah " . $player->getName());
				}
				break;
			case $this->CONFIRM_PURCHASE_UI_ID:
				{
					if($formData === 0){
						$dat = $this->fetchFromId($this->cache[$player->getLowerCaseName()]["id"]);
						if(count($dat) > 0){
							$item = Item::get($dat['itemId'], $dat['itemMeta']);
							$item->setCount($dat['amount']);
							$item->setCustomName($dat['trade']);
							if($player->getInventory()->canAddItem($item)){
								if($this->core->getPrestigeSocietyEconomy()->getMoney($dat['username']) < $dat['price']){
									$player->sendMessage(RandomUtils::colorMessage($this->core->getMessage('auction_house', 'no_sufficient_funds')));

									return;
								}
								$this->core->getPrestigeSocietyEconomy()->addMoney($dat['username'], $dat['price']);
								$this->core->getPrestigeSocietyEconomy()->subtractMoney($player, $dat['price']);
								$player->getInventory()->addItem($item);
								$this->deleteFromId($dat['id']);
								$this->resetCache($player);
								$pl = $this->core->getServer()->getPlayer($dat['username']);
								if($pl !== null || $pl->isOnline()){
									$this->core->getServer()->broadcastMessage(RandomUtils::colorMessage(str_replace(["@player", "@item", "@price", "@amount"], [$player->getName(), $item->getName(), $dat['price'], $dat['amount']], $this->core->getMessage('auction_house', 'bought_item'))));
								}else{
									$this->cache[$dat['username']]['purchasesMessage'] .= TextFormat::YELLOW . $player->getName() . TextFormat::GREEN . " bought " . $item->getName() . " (x" . $dat['amount'] . ") from you for $" . $dat['price'] . "\n";
									$this->core->getServer()->broadcastMessage(TextFormat::GREEN . $player->getName() . " has bought " . $item->getName() . " (x" . $dat['amount'] . ") from " . $dat['username'] . " for $" . $dat['price']);
								}
							}else{
								$player->sendMessage(RandomUtils::colorMessage($this->core->getMessage('auction_house', 'inventory_full')));
							}
						}else{
							$player->sendMessage(RandomUtils::colorMessage($this->core->getMessage('auction_house', 'no_longer_available')));
						}
					}
				}
				break;
			case $this->MY_OFFERS_UI_ID:
				{
					$id = $this->cache[$player->getLowerCaseName()]["ids"][$formData];
					$this->getConfirmDeleteForm($player, $id);
					$this->resetCache($player);
					$this->cache[$player->getLowerCaseName()]["id"] = $id;
				}
				break;
			case $this->CONFIRM_DELETE_OFFER_UI_ID:
				{
					if($formData === 0){
						$id = $this->cache[$player->getLowerCaseName()]["id"];
						$dat = $this->fetchFromId($id);
						$item = Item::get($dat['itemId'], $dat['itemMeta']);
						$item->setCount($dat['amount']);
						$item->setCustomName($dat['trade']);
						$this->deleteFromId($id);
						if($player->getInventory()->canAddItem($item)){
							$player->getInventory()->addItem($item);
							$this->core->getServer()->broadcastMessage(RandomUtils::colorMessage(str_replace(["@player", "@item", "@price", "@amount"], [$player->getName(), $item->getName(), $dat['price'], $dat['amount']], $this->core->getMessage('auction_house', 'deleted_item'))));
						}else{
							$player->sendMessage(RandomUtils::colorMessage($this->core->getMessage('auction_house', 'inventory_full_2')));
						}
					}
				}
				break;
		}
	}

	/**
	 *
	 * @param Player $player
	 * @param string|null $from
	 *
	 * @return bool
	 *
	 */
	public function getShopForm(Player $player, string $from = null){

		if($from !== null){
			$s = $this->fetchFromUsername(strtolower($from));
			if(count($s) != 0){
				$form = new SimpleForm();
				$form->setId($this->SHOP_UI_ID);
				$form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&l{$from}'s SHOP&r&k&e|"));
				foreach($s as $r){
					$this->cache[$player->getLowerCaseName()]["ids"][] = $r['id'];
					$form->setButton(
						RandomUtils::colorMessage(
							"&l&k&e|&r&l&d{$r['trade']} &d(&8x{$r['amount']}&d)&e&k|&r\n" .
							"&8Price - &6{$r['price']} &8silver coins&r"
						), "http://permat.comli.com/items/{$r['itemId']}-{$r['itemMeta']}.png");
				}
				$form->send($player);
			}else{
				$player->sendMessage(RandomUtils::colorMessage(str_replace('@from', $from, $this->core->getMessage('auction_house', 'no_items_player'))));
			}
		}else{
			$s = $this->fetchItems($player);

			if($s !== null){
				if(count($s) != 0){
					$this->cache[$player->getLowerCaseName()]["buying_from"] = $from;
					$form = new SimpleForm();
					$form->setId($this->SHOP_UI_ID);
					$pg = $this->getPaginationArray($player);
					$items = $pg[0];
					$page = $pg[1];
					$max = $pg[2];
					$form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lPUBLIC SHOP&r&k&e|&r"));
					$form->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&d← Last&e&k|"));
					$form->setContent(RandomUtils::colorMessage(
						"            &l&dTotal of &7$items &ditems\n" .
						"          &l&e- &r&l&dPage &7$page &dof &7$max&e -"
					));
					$i = 1;
					$this->cache[$player->getLowerCaseName()]["ids"] = [];
					foreach($s as $r){
						if(!isset($this->cache["ids"])){
							$this->cache[$player->getLowerCaseName()]["ids"][$i++] = $r['id'];
						}
						$form->setButton(RandomUtils::colorMessage(
							"&l&k&e|&r&l&d{$r['trade']} &d(&8x{$r['amount']}&d)&e&k|&r\n" .
							"&8Seller - &6{$r['username']}&r\n" .
							"&8Price - &6{$r['price']} &8silver coins&r\n"
						), "http://permat.comli.com/items/{$r['itemId']}-{$r['itemMeta']}.png");
					}
					$form->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&dNext →&e&k|"));
					$form->send($player);
				}else{
					$player->sendMessage(RandomUtils::colorMessage(str_replace('@from', $from, $this->core->getMessage('auction_house', 'no_items_public'))));
				}
			}else{
				return false;
			}
		}

		return true;
	}

	/**
	 *
	 * Fetches what market items player will look at next
	 *
	 * @param Player $player
	 *
	 * @return null|array
	 *
	 */
	protected function fetchItems(Player $player){
		if(isset($this->queue[$player->getLowerCaseName()])){
			$s = $this->queue[$player->getLowerCaseName()];
			$type = $this->queue[$player->getLowerCaseName()]['type'];
			switch($type){
				case 0:
					$this->queue[$player->getLowerCaseName()]['skip'] -= 10;
					$f = $this->fetchLastFromMarketID($s['last'], $this->queue[$player->getLowerCaseName()]['skip']);
					if(!empty($f)){
						$this->queue[$player->getLowerCaseName()]['page']--;
						$this->queue[$player->getLowerCaseName()]['last'] = $f[0]['id'];
						$this->queue[$player->getLowerCaseName()]['next'] = end($f)['id'];

						return $f;
					}
					unset($this->queue[$player->getLowerCaseName()]);

					return null;
				case 1:
					$this->queue[$player->getLowerCaseName()]['skip'] += 10;
					$f = $this->fetchNextFromMarketID($s['next']);
					if(!empty($f)){
						$this->queue[$player->getLowerCaseName()]['page']++;
						$this->queue[$player->getLowerCaseName()]['last'] = $f[0]['id'];
						$this->queue[$player->getLowerCaseName()]['next'] = end($f)['id'];

						return $f;
					}
					unset($this->queue[$player->getName()]);

					return null;
			}
		}else{
			$f = $this->fetchNextFromMarketID(0);
			if(!empty($f)){
				$this->queue[$player->getLowerCaseName()]['skip'] = 0;
				$this->queue[$player->getLowerCaseName()]['page'] = 1;
				$this->queue[$player->getLowerCaseName()]['last'] = $f[0]['id'];
				$this->queue[$player->getLowerCaseName()]['next'] = count($f) > 1 ? end($f)['id'] : $f[0]['id'];

				return $f;
			}
		}
		unset($this->queue[$player->getLowerCaseName()]);

		return null;
	}

	/**
	 *
	 * @param int $marketId
	 * @param int $skip
	 *
	 * @return array
	 *
	 */
	public function fetchLastFromMarketID(int $marketId, int $skip): array{
		$out = [];
		$q = $this->db->query("SELECT * FROM history WHERE id < $marketId LIMIT 10 OFFSET $skip");
		while($row = $q->fetchArray(SQLITE3_ASSOC)){
			$out[] = $row;
		}

		return $out;
	}

	/**
	 *
	 * @param int $marketId
	 *
	 * @return array
	 *
	 */
	public function fetchNextFromMarketID(int $marketId): array{
		$out = [];
		$q = $this->db->query("SELECT * FROM history WHERE id > $marketId LIMIT 10");
		while($row = $q->fetchArray(SQLITE3_ASSOC)){
			$out[] = $row;
		}

		return $out;
	}

	/**
	 *
	 * @param Player $player
	 *
	 * @return array
	 *
	 */
	protected function getPaginationArray(Player $player): array{
		$tc = $this->getRowCount();
		$c = 0;

		for($i = 0; $i < $tc; $i += 10){
			$c++;
		}

		$p = $this->queue[$player->getLowerCaseName()]['page'];

		return [$tc, $p, $c];
	}

	/**
	 *
	 * @return int
	 *
	 */
	public function getRowCount(): int{
		$rows = $this->db->query("SELECT COUNT(*) as count FROM history");
		$row = $rows->fetchArray();
		$numRows = $row['count'];

		return $numRows;
	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function getChooseItemForm(Player $player){
		$form = new SimpleForm();
		$form->setId($this->CHOOSE_ITEM_UI_ID);
		$form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lCHOOSE AN ITEM TO SELL&r&k&e|"));
		$this->savePlayerItemNames($player);
		$items = $player->getInventory()->getContents();
		foreach($items as $key => $item){
			$form->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&d{$item->getName()} &d(&8x{$item->getCount()}&d)&e&k|"),
				"http://permat.comli.com/items/{$item->getId()}-{$item->getDamage()}.png");
		}
		$form->send($player);
	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function savePlayerItemNames(Player $player){
		$items = $player->getInventory()->getContents();
		$this->cache[$player->getLowerCaseName()]["items"] = [];

		foreach($items as $key => $item){
			$this->cache[$player->getLowerCaseName()]["items"][] = $key;
		}
	}

	/**
	 *
	 * @param Player $player
	 *
	 * @return array
	 *
	 */
	public function getCache(Player $player): array{
		if(isset($this->cache[$player->getLowerCaseName()])){
			return $this->cache[$player->getLowerCaseName()];
		}else{
			$this->resetCache($player);

			return [];
		}
	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function resetCache(Player $player){
		if(isset($this->cache[$player->getLowerCaseName()])){
			if(isset($this->queue[$player->getLowerCaseName()])){
				unset($this->queue[$player->getLowerCaseName()]);
			}
			$this->cache[$player->getLowerCaseName()] = [];
			$this->saveCache();
		}
	}

	/**
	 *
	 * @param Player $player
	 * @param Item $item
	 *
	 */
	public function getSellForm(Player $player, Item $item){
		$form = new CustomForm();
		$form->setId($this->SELL_UI_ID);
		$form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lSELL {$item->getName()}&r&k&e|"));
		$form->setInput(RandomUtils::colorMessage("&e&k|&r&d&lPRICE&r&k&e|"), "the price you're selling this item for.");
		$form->setSlider(RandomUtils::colorMessage("&d&lAMOUNT&r&e"), 1, $item->getCount(), 1, 1);
		$form->send($player);
	}

	/**
	 *
	 * @param Player $player
	 * @param int $id
	 *
	 */
	public function getConfirmPurchaseForm(Player $player, int $id){
		$dat = $this->fetchFromId($id);
		if(count($dat) > 0){
			$form = new SimpleForm();
			$form->setId($this->CONFIRM_PURCHASE_UI_ID);
			$form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lDO YOU WANT TO BUY THIS ITEM?&r&k&e|"));
			$content = "";
			$enchants = implode(", ", explode(';', $dat['enchants']));
			$content .= RandomUtils::colorMessage("&l&e===========================&r\n");
			$content .= RandomUtils::colorMessage("&dSeller: &7" . $dat['username'] . "\n");
			$content .= RandomUtils::colorMessage("&dItem Name: &7" . $dat['trade'] . " &r&5(" . Item::get($dat['itemId'])->getName() . ")\n");
			$content .= RandomUtils::colorMessage("&dPrice: &7" . $dat['price'] . "\n");
			$content .= RandomUtils::colorMessage("&dAmount: &7" . $dat['amount'] . "\n");
			$content .= RandomUtils::colorMessage("&dEnchantments: &7" . ($enchants === '' ? 'none' : $enchants) . "\n");
			$content .= RandomUtils::colorMessage("&dMarket ID: &7" . $dat['id'] . "\n");
			$content .= RandomUtils::colorMessage("&l&e===========================\n");
			$form->setContent($content);
			$form->setButton(RandomUtils::colorMessage("&a&lYES"));
			$form->setButton(RandomUtils::colorMessage("&c&lNO"));
			$form->send($player);
		}else{
			$player->sendMessage(RandomUtils::colorMessage($this->core->getMessage('auction_house', 'no_longer_available')));
		}
	}

	/**
	 *
	 * @param string $id
	 *
	 * @return array
	 *
	 */
	public function fetchFromId(string $id): array{
		$q = $this->db->query("SELECT * FROM history WHERE id = '$id'");
		while($row = $q->fetchArray(SQLITE3_ASSOC)){
			return $row;
		}

		return [];
	}

	/**
	 *
	 * @param Player $player
	 * @param Item $item
	 *
	 * @return int
	 *
	 */
	protected function getCountFromItem(Player $player, Item $item): int{
		$count = 0;
		foreach($player->getInventory()->all($item) as $slot => $i){
			$count += $i->getCount();
		}

		return $count;
	}

	/**
	 *
	 * @param string $username
	 * @param string $item
	 * @param int $itemId
	 * @param int $itemMeta
	 * @param int $price
	 * @param int $amount
	 * @param string $enchants
	 * @param int $id
	 *
	 */
	public function insert(string $username, string $item, int $itemId, int $itemMeta, int $price, int $amount, string $enchants, int $id = null){
		$q = $this->db->prepare("INSERT INTO history (username, trade, itemId, itemMeta, price, amount, id, enchants) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
		if($id === null){
			$id = time();
		}
		$q->bindParam(1, $username);
		$q->bindParam(2, $item);
		$q->bindParam(3, $itemId);
		$q->bindParam(4, $itemMeta);
		$q->bindParam(5, $price);
		$q->bindParam(6, $amount);
		$q->bindParam(7, $id);
		$q->bindParam(8, $enchants);
		$q->execute();
	}

	/**
	 *
	 * @param string $id
	 *
	 */
	public function deleteFromId(string $id){
		$this->db->query("DELETE FROM history WHERE id = '$id'");
	}

	/**
	 *
	 * @param Player $player
	 * @param int $id
	 *
	 */
	public function getConfirmDeleteForm(Player $player, int $id){
		$dat = $this->fetchFromId($id);
		if(count($dat) > 0){
			$form = new SimpleForm();
			$form->setId($this->CONFIRM_DELETE_OFFER_UI_ID);
			$form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lDO YOU WANT TO REMOVE THIS ITEM?&r&k&e|"));
			$content = "";
			$enchants = implode(", ", explode(';', $dat['enchants']));
			$content .= RandomUtils::colorMessage("&l&e===========================&r\n");
			$content .= RandomUtils::colorMessage("&4&lNOTE: &7all your items will be returned.&r\n");
			$content .= RandomUtils::colorMessage("&dItem Name: &7" . $dat['trade'] . " &r&5(" . Item::get($dat['itemId'])->getName() . ")\n");
			$content .= RandomUtils::colorMessage("&dPrice: &7" . $dat['price'] . "\n");
			$content .= RandomUtils::colorMessage("&dAmount: &7" . $dat['amount'] . "\n");
			$content .= RandomUtils::colorMessage("&dEnchantments: &7" . ($enchants === '' ? 'none' : $enchants) . "\n");
			$content .= RandomUtils::colorMessage("&dMarket ID: &7" . $dat['id'] . "\n");
			$content .= RandomUtils::colorMessage("&l&e===========================\n");
			$form->setContent($content);
			$form->setButton(RandomUtils::colorMessage("&a&lYES"));
			$form->setButton(RandomUtils::colorMessage("&c&lNO"));
			$form->send($player);
		}else{
			$player->sendMessage(RandomUtils::colorMessage($this->core->getMessage('auction_house', 'no_longer_available')));
		}
	}

	/**
	 *
	 * @return array
	 *
	 */
	public function fetchAll(): array{
		$out = [];
		$q = $this->db->query("SELECT * FROM history");
		while($row = $q->fetchArray(SQLITE3_ASSOC)){
			$out[] = $row;
		}

		return $out;
	}

}