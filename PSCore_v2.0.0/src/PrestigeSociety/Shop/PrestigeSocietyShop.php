<?php

namespace PrestigeSociety\Shop;

use _64FF00\PurePerms\PurePerms;
use factions\entity\Member;
use factions\FactionsPE;
use factions\manager\Members;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\exc;
use PrestigeSociety\Core\Utils\RandomUtils;
use PrestigeSociety\Teleport\PrestigeSocietyTeleport;
use PrestigeSociety\UIForms\CustomForm;
use PrestigeSociety\UIForms\SimpleForm;

class PrestigeSocietyShop {

	/** @var \SQLite3 */
	public $db;
	/** @var string[][] */
	public $queue = [];
	/** @var array */
	protected $shop = [
		0, //category => id
		0,
		0,
		0,
	];
	/** @var int */
	protected $ADD_SHOP_ID = 0, $REMOVE_SHOP_ID = 0,
		$ADD_SHOP_INFO = 3, $SELECT_CATEGORY_ID = 0,
		$SELECT_CATEGORY_NAME = 4, $SELECT_CATEGORY_NAME_COLORED = 5,
		$MONEY_SURE_FIRST = 7, $MONEY_SURE_SECOND = 8,
		$MONEY_SURE_THREE = 9, $CATEGORY_PREMIUM = 2,
		$SHOP_ID, $SELECT_ITEM_ID, $SELECT_TYPE_ID = 1, $ADDITIONAL_HOMES = 6,
		$ADDITIONAL_ENCH = 10, $GOLDEN_MEMBER = 11, $VIP_MEMBER = 12,
		$ADDITIONAL_SLOTS = 13;
	/** @var PrestigeSocietyCore */
	protected $core;

	/** @var PrestigeSocietyTeleport */
	protected $base;

	/** @var array */
	protected $selecting = [];


	/**
	 *
	 * PrestigeSocietyLevels constructor.
	 *
	 * @param \SQLite3 $db
	 * @param PrestigeSocietyCore $core
	 *
	 */
	public function __construct(\SQLite3 $db, PrestigeSocietyCore $core, PrestigeSocietyTeleport $base){


		$this->ADD_SHOP_ID = mt_rand(100, 1000);
		$this->REMOVE_SHOP_ID = $this->ADD_SHOP_ID + 1;
		$this->SELECT_CATEGORY_ID = $this->REMOVE_SHOP_ID + 1;
		$this->SHOP_ID = $this->SELECT_CATEGORY_ID + 1;
		$this->SELECT_ITEM_ID = $this->SHOP_ID + 1;

		$this->db = $db;
		$this->db->exec("CREATE TABLE IF NOT EXISTS shop (item VARCHAR, price INT, amount INT, itemId int, itemMeta INT, id INT, category INT);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS shopItemCount (category INT UNIQUE, id INT);");
		foreach($this->shop as $id => $count){
			$query = $this->db->prepare("INSERT OR IGNORE INTO shopItemCount (category, id) VALUES (?, ?);");
			$query->bindParam(1, $id);
			$query->bindParam(2, $count);
		}
		$query = $this->db->query("SELECT * FROM shopItemCount");
		while($q = $query->fetchArray(SQLITE3_ASSOC)){
			$this->shop[$q['category']] = $q['id'];
		}

		$this->core = $core;
		$this->base = $base;

	}

	/**
	 *
	 * @return array
	 *
	 */
	public function getAllShops(){
		$out = [];
		$query = $this->db->query("SELECT * FROM shop;");
		while($q = $query->fetchArray(SQLITE3_ASSOC)){
			$out[] = $q;
		}

		return $out;
	}

	public function getSelectCategoryUIFirst(Player $player){
		$ui = new SimpleForm();
		$ui->setId($this->SELECT_TYPE_ID);
		$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lSelect Type&r&k&e|"));
		$ui->setContent("");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dInfo&e&k|\n" .
			"&r&8Click to Enter!"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/blocks.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dNormal Shop&e&k|\n" .
			"&r&8Click to Enter!"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/blocks.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dPremium Shop&e&k|\n" .
			"&r&8Click to Enter!"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/armor.png");
		$ui->send($player);
	}

	/**
	 *
	 * @param Player $player
	 * @param        $formData
	 * @param int $formId
	 *
	 */
	public function handleFormResponse(Player $player, $formData, int $formId){
		$xuid = $player->getXuid();

		$credits = $this->core->PrestigeSocietyCredits->getCredits($player);

		switch($formId){
			case $this->SELECT_CATEGORY_ID:

				$this->queue[$xuid]['category'] = $formData;

				//VAR_DUMP($this->queue[$xuid]['action'] === 2);

				switch($this->queue[$xuid]['action']){

					case 0:
						if($this->getAddShopUI($player)){
							return;
						}
						$player->sendMessage(RandomUtils::colorMessage($this->core->getMessage('shop', 'cannot_have_air')));
						break;
					case 1:
						if($this->getSelectItemIdUI($player, $this->queue[$xuid]['category'])){
							$this->queue[$xuid]['idAction'] = 0;

							return;
						}

						$player->sendMessage(RandomUtils::colorMessage($this->core->getMessage('shop', 'no_items')));
						break;
					case 2:
						if($this->getSelectItemIdUI($player, $this->queue[$xuid]['category'])){
							$this->queue[$xuid]['idAction'] = 1;

							return;
						}

						$player->sendMessage(RandomUtils::colorMessage($this->core->getMessage('shop', 'no_items')));
						break;
				}

				break;
			case $this->ADD_SHOP_ID:
				if($formData[0] !== (string)(int)($formData[0]) or $formData[0] === null){
					$player->sendMessage(RandomUtils::colorMessage($this->core->getMessage('shop', 'enter_valid_price')));

					return;
				}

				$item = $player->getInventory()->getItemInHand();
				$item->setCount($formData[1]);
				$this->addNewShop($item, exc::stringToInteger($formData[0]), $this->queue[$xuid]['category']);
				$player->sendMessage(RandomUtils::colorMessage(str_replace(["@player", "@item", "@price", "@amount"], [$player->getName(), $item->getName(), $formData[0], $formData[1]], $this->core->getMessage('shop', 'added_item'))));

				break;
			case $this->REMOVE_SHOP_ID:

				if($formData === 0){
					$id = $this->queue[$xuid]['id'];
					$category = $this->queue[$xuid]['category'];

					$shop = $this->getShop($category, $id);
					$this->removeShop($category, $id);

					$player->sendMessage(RandomUtils::colorMessage(str_replace(["@player", "@item", "@price", "@amount"], [$player->getName(), $shop['item'], $shop['price'], $shop['amount']], $this->core->getMessage('shop', 'removed_item'))));
				}
				break;

			case $this->SELECT_ITEM_ID:

				if($formData === 0){
					$this->getSelectCategoryUI($player);

					return;
				}

				$id = $this->selecting[$xuid][$formData];
				$this->queue[$xuid]['id'] = $id;
				$category = $this->queue[$xuid]['category'];

				switch($this->queue[$xuid]['idAction']){

					case 0:
						$this->getRemoveShopUI($player, $category, $id);
						break;
					case 1:
						$this->getShopUI($player, $category, $id);
						break;
				}

				break;

			case $this->SHOP_ID:

				if($formData === 0){

					$id = $this->queue[$xuid]['id'];
					$category = $this->queue[$xuid]['category'];

					$shop = $this->getShop($category, $id);

					if(count($shop) > 0){
						$item = Item::get($shop['itemId'], $shop['itemMeta']);
						$item->setCount($shop['amount']);
						$item->setCustomName($shop['item']);

						if($this->core->getPrestigeSocietyEconomy()->getMoney($player) < $shop['price']){
							$player->sendMessage(RandomUtils::colorMessage($this->core->getMessage('shop', 'non_sufficient_funds')));
						}else{
							if(!$player->getInventory()->canAddItem($item)){
								$player->sendMessage(RandomUtils::colorMessage($this->core->getMessage('shop', 'inventory_full')));

								return;
							}
							$player->getInventory()->addItem($item);
							$this->core->getPrestigeSocietyEconomy()->subtractMoney($player, $shop['price']);
							$player->sendMessage(RandomUtils::colorMessage(str_replace(["@player", "@item", "@price", "@amount"], [$player->getName(), $item->getName(), $shop['price'], $shop['amount']], $this->core->getMessage('shop', 'bought_item'))));
						}
					}else{
						$player->sendMessage(RandomUtils::colorMessage($this->core->getMessage('shop', 'invalid_item')));
					}
				}

				break;

			case $this->SELECT_TYPE_ID:
				if($formData === 0){
					$this->createShopInfo($player);

					return;
				}elseif($formData === 1){
					$this->core->getServer()->dispatchCommand($player, "warp shop");

					//$this->getSelectCategoryUI($player);
					return;
				}elseif($formData === 2){
					$this->getSelectCategoryUIPremium($player);

					return;
				}

				break;

			case $this->ADDITIONAL_HOMES:
				$count = (int)$formData[1];
				$countHomes = count($this->base->getHomeAPI()->getPlayerHomes($player));
				$needed = 75 * $count;
				if($needed <= $credits){
					$this->core->getPrestigeSocietyCredits()->subtractCredits($player, $needed);
					$this->core->PrestigeSocietyCountHomes->addNewPlayer($player);
					$this->core->PrestigeSocietyCountHomes->addCount($player, $count + $countHomes);
					$message = $this->core->getMessage('homes', 'buy');
					$message = str_replace(['@homes', '@get', '@coins'], [$count, $needed, $credits], $message);
					$message = RandomUtils::colorMessage($message);
					$player->sendMessage($message);
				}else{
					$message = $this->core->getMessage('additionalname', 'no_credits');
					$message = str_replace('@coins', $credits, $message);
					$message = RandomUtils::colorMessage($message);
					$player->sendMessage($message);

					return;
				}
				break;

			case $this->ADDITIONAL_ENCH:
				$count = (int)$formData[1];
				$countEnch = $this->core->PrestigeSocietyEnchantments->getEnchList($player);
				$needed = 175 * $count;
				if(($count + $countEnch) > 5){
					$message = $this->core->getMessage('enchantings', 'much_ench');
					$message = RandomUtils::colorMessage($message);
					$player->sendMessage($message);

					return;
				}
				if($needed <= $credits){
					$this->core->getPrestigeSocietyCredits()->subtractCredits($player, $needed);
					$this->core->PrestigeSocietyEnchantments->addNewPlayer($player);
					$this->core->PrestigeSocietyEnchantments->addEnchList($player, $count + $countEnch);
					$message = $this->core->getMessage('enchantings', 'buy');
					$credits = $credits - (175 * $count);
					$message = str_replace(['@ench', '@get', '@coins'], [$count, $needed, $credits], $message);
					$message = RandomUtils::colorMessage($message);
					$player->sendMessage($message);
				}else{
					$message = $this->core->getMessage('additionalname', 'no_credits');
					$message = str_replace('@coins', $credits, $message);
					$message = RandomUtils::colorMessage($message);
					$player->sendMessage($message);

					return;
				}
				break;

			case $this->CATEGORY_PREMIUM:
				switch($formData){
					case 0:
						if($credits >= 200){
							$this->addButtonGoldenMember($player, 200);
						}else{
							$message = $this->core->getMessage('additionalname', 'no_credits');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}

						return;
					case 1:
						$this->createUIName($player);

						return;
					case 2:
						$this->addButtonSureMoneyFirst($player, 50, 20000);

						return;
					case 3:
						$this->addButtonSureMoneySecond($player, 100, 40000);

						return;
					case 4:
						$this->addButtonSureMoneyThree($player, 150, 60000);

						return;
					case 5:
						if($credits >= 50){
							$this->core->PrestigeSocietyCredits->subtractCredits($player, 50);
							$command = "key Epic @player 1";
							$command = str_replace("@player", $player->getName(), $command);
							$this->core->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);
							$message = "&6[!] &cYou succesfuly bought &eEpic Crate &cfor @coins credits.";
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}else{
							$message = $this->core->getMessage('additionalname', 'no_credits');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}
						break;
					case 6:
						if($credits >= 100){
							$this->core->PrestigeSocietyCredits->subtractCredits($player, 100);
							$command = "key Custom @player 1";
							$command = str_replace("@player", $player->getName(), $command);
							$this->core->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);
							$message = "&6[!] &cYou succesfuly bought &eCustom Enchants Crate &cfor @coins credits.";
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}else{
							$message = $this->core->getMessage('additionalname', 'no_credits');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}
						break;
					case 7:
						$this->addSlotsFaction($player);

						return;
					case 8:
						if($credits >= 1000){
							$this->addVip($player, 1000);

							return;
						}else{
							$message = $this->core->getMessage('additionalname', 'no_credits');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}
						break;
					case 9:
						if($credits >= 75){
							$this->addHomes($player);

							return;
						}else{
							$message = $this->core->getMessage('additionalname', 'no_credits');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}
						break;
					case 10:
						if($credits >= 175){
							$this->addEnch($player);

							return;
						}else{
							$message = $this->core->getMessage('additionalname', 'no_credits');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}
						break;
					case 11:
						if($credits >= 150){
							PurePerms::getAPI()->getUserDataMgr()->setPermission($player, "repair.free");
							$message = $this->core->getMessage('repair', 'buy');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}else{
							$message = $this->core->getMessage('additionalname', 'no_credits');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}
					case 12:
						if($credits >= 750){
							PurePerms::getAPI()->getUserDataMgr()->setPermission($player, "command.lsd");
							$message = $this->core->getMessage('LSD', 'buy');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}else{
							$message = $this->core->getMessage('additionalname', 'no_credits');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}
				}
				break;

			case $this->SELECT_CATEGORY_NAME:
				switch($formData){
					case 0:
						$this->createUIColoredName($player);

						return;
					case 1:
						if($credits >= 250){
							$this->handleRainbowName($player);
							$message = $this->core->getMessage('additionalname', 'buy');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);
						}else{
							$message = $this->core->getMessage('additionalname', 'no_credits');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}
						break;
				}
				break;

			case $this->GOLDEN_MEMBER:
				switch($formData){
					case 0:
						if($credits >= 200){
							$this->core->PrestigeSocietyCredits->subtractCredits($player, 200);
							$group = PurePerms::getAPI()->getGroup("Gold");
							PurePerms::getAPI()->getUserDataMgr()->setGroup($player, $group, null, "30d");
							$this->core->PrestigeSocietyCountHomes->addCount($player, 4);

							return;
						}else{
							$message = $this->core->getMessage('additionalname', 'no_credits');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}
					case 1:
						return;
				}
				break;

			case $this->VIP_MEMBER:
				switch($formData){
					case 0:
						if($credits >= 1000){
							$this->core->PrestigeSocietyCredits->subtractCredits($player, 1000);
							$group = PurePerms::getAPI()->getGroup("VIP");
							PurePerms::getAPI()->getUserDataMgr()->setGroup($player, $group, null, "90d");
							$this->core->PrestigeSocietyCountHomes->addCount($player, 5);

							return;
						}else{
							$message = $this->core->getMessage('additionalname', 'no_credits');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}
					case 1:
						return;
				}
				break;

			case $this->SELECT_CATEGORY_NAME_COLORED:
				switch($formData){
					case 0:
						if($credits >= 100){
							$this->core->getPrestigeSocietyNicks()->resetNick($player);
							$message = $this->core->getMessage('additionalname', 'clear');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$this->core->getPrestigeSocietyCredits()->subtractCredits($player, 100);
							$player->sendMessage($message);

							return;
						}else{
							$message = $this->core->getMessage('additionalname', 'no_credits');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}
						break;
					case 1:
						if($credits >= 100){
							$name = $player->getName();
							$name = "&9" . $name . "&r";
							$this->core->getPrestigeSocietyNicks()->setNick($player, $name);
							$message = $this->core->getMessage('additionalname', 'buy');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$this->core->getPrestigeSocietyCredits()->subtractCredits($player, 100);
							$player->sendMessage($message);

							return;
						}else{
							$message = $this->core->getMessage('additionalname', 'no_credits');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}
						break;
					case 2:
						if($credits >= 100){
							$name = $player->getName();
							$name = "&d" . $name . "&r";
							$this->core->getPrestigeSocietyNicks()->setNick($player, $name);
							$message = $this->core->getMessage('additionalname', 'buy');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$this->core->getPrestigeSocietyCredits()->subtractCredits($player, 100);
							$player->sendMessage($message);

							return;
						}else{
							$message = $this->core->getMessage('additionalname', 'no_credits');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}
						break;
					case 3:
						if($credits >= 100){
							$name = $player->getName();
							$name = "&6" . $name . "&r";
							$this->core->getPrestigeSocietyNicks()->setNick($player, $name);
							$message = $this->core->getMessage('additionalname', 'buy');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$this->core->getPrestigeSocietyCredits()->subtractCredits($player, 100);
							$player->sendMessage($message);

							return;
						}else{
							$message = $this->core->getMessage('additionalname', 'no_credits');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}
						break;
					case 4:
						if($credits >= 100){
							$name = $player->getName();
							$name = "&9" . $name . "&r";
							$this->core->getPrestigeSocietyNicks()->setNick($player, $name);
							$message = $this->core->getMessage('additionalname', 'buy');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$this->core->getPrestigeSocietyCredits()->subtractCredits($player, 100);
							$player->sendMessage($message);

							return;
						}else{
							$message = $this->core->getMessage('additionalname', 'no_credits');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}
						break;
					case 5:
						if($credits >= 100){
							$name = $player->getName();
							$name = "&e" . $name . "&r";
							$this->core->getPrestigeSocietyNicks()->setNick($player, $name);
							$message = $this->core->getMessage('additionalname', 'buy');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$this->core->getPrestigeSocietyCredits()->subtractCredits($player, 100);
							$player->sendMessage($message);

							return;
						}else{
							$message = $this->core->getMessage('additionalname', 'no_credits');
							$message = str_replace('@coins', $credits, $message);
							$message = RandomUtils::colorMessage($message);
							$player->sendMessage($message);

							return;
						}
						break;
				}
				break;

			case $this->MONEY_SURE_FIRST:
				switch($formData){
					case 0:
						$this->addMoney($player, 50, $credits, 20000);

						return;
					default:
						return;
				}
				break;

			case $this->MONEY_SURE_SECOND:
				switch($formData){
					case 0:
						$this->addMoney($player, 100, $credits, 40000);

						return;
					default:
						return;
				}
				break;

			case $this->MONEY_SURE_THREE:
				switch($formData){
					case 0:
						$this->addMoney($player, 150, $credits, 60000);

						return;
					default:
						return;
				}
				break;

			case $this->ADDITIONAL_SLOTS:
				$count = (int)$formData[1];
				$member = Members::get($player, false);
				if(!$member->hasFaction()){
					$message = "&6[!] &cYou need have a Faction!";
					$message = RandomUtils::colorMessage($message);
					$member->getPlayer()->sendMessage($message);

					return;
				}
				$fac = Members::get($player, false)->getFaction();
				if(!$member->isLeader()){
					$message = "&6[!] &cYou are not Leader!";
					$message = RandomUtils::colorMessage($message);
					$member->getPlayer()->sendMessage($message);

					return;
				}
				$member = $fac->getName();
				$countEnch = $this->core->PrestigeSocietyFaction->getSlots($member);
				$needed = 150 * $count;
				if(($count + $countEnch) > 15){
					$message = $this->core->getMessage('faction', 'much_ench');
					$message = RandomUtils::colorMessage($message);
					$player->sendMessage($message);

					return;
				}
				if($needed <= $credits){
					$this->core->getPrestigeSocietyCredits()->subtractCredits($player, $needed);
					$this->core->PrestigeSocietyFaction->addSlots($member, $count + $countEnch);
					$message = $this->core->getMessage('faction', 'buy');
					$credits = $credits - (150 * $count);
					$message = str_replace(['@ench', '@get', '@coins'], [$count, $needed, $credits], $message);
					$message = RandomUtils::colorMessage($message);
					$player->sendMessage($message);
				}else{
					$message = $this->core->getMessage('additionalname', 'no_credits');
					$message = str_replace('@coins', $credits, $message);
					$message = RandomUtils::colorMessage($message);
					$player->sendMessage($message);

					return;
				}
				break;
		}
	}

	/**
	 *
	 * @param Player $player
	 *
	 * @return bool
	 *
	 */
	public function getAddShopUI(Player $player){
		if($player->getInventory()->getItemInHand()->getId() !== Item::AIR){
			$ui = new CustomForm();
			$ui->setTitle("Add Shop");
			$ui->setInput("Price", "The price of the item you're selling.");
			$ui->setSlider("Amount", 1, 64, 1, 1);
			$ui->setId($this->ADD_SHOP_ID);
			$ui->send($player);

			return true;
		}

		return false;
	}

	/**
	 *
	 * @param Player $player
	 * @param int $category
	 *
	 * @return bool
	 *
	 */
	public function getSelectItemIdUI(Player $player, int $category){
		$shops = $this->getShopItems($category);
		if(count($shops) > 0){
			$ui = new SimpleForm();
			$ui->setId($this->SELECT_ITEM_ID);
			$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&l" . $this->categoryToString($category) . "&r&k&e|"));
			$ui->setContent("");

			$xuid = $player->getXuid();

			$this->selecting[$xuid] = [];
			$ui->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&d← Back&e&k|"));

			$i = 1;
			foreach($shops as $shop){
				$this->selecting[$xuid][$i++] = $shop['id'];
				$ui->setButton(RandomUtils::colorMessage(
					"&l&k&e|&r&l&d{$shop['item']} &d(&8x{$shop['amount']}&d)&e&k|\n" .
					"&r&8Price - {$shop['price']} silver coins"
				), "http://permat.comli.com/items/{$shop['itemId']}-{$shop['itemMeta']}.png");
			}
			$ui->send($player);

			return true;
		}

		return false;
	}

	/**
	 *
	 * @param int $category
	 * @return array
	 *
	 */
	public function getShopItems(int $category){
		$out = [];
		$query = $this->db->query("SELECT * FROM shop WHERE category = '{$category}';");
		while($q = $query->fetchArray(SQLITE3_ASSOC)){
			$out[] = $q;
		}

		return $out;
	}

	/**
	 *
	 * @param int $category
	 *
	 * @return mixed|string
	 *
	 */
	public function categoryToString(int $category){
		$ids = [
			'Blocks',
			'Food',
			'Potions',
			'Miscellaneous',
		];

		return $ids[$category] ?? 'Unknown';
	}

	/**
	 *
	 * @API
	 *
	 * @param Item $item
	 * @param int $price
	 * @param int $category
	 *
	 * @return bool
	 *
	 */
	public function addNewShop(Item $item, int $price, int $category){
		if($category < 0 || $category > 5){
			return false;
		}
		$itemName = $item->getName();
		$itemId = $item->getId();
		$itemMeta = $item->getDamage();
		$amount = $item->getCount();
		$id = $this->shop[$category]++;
		$query = $this->db->prepare("INSERT INTO shop (item, price, amount, itemId, itemMeta, id, category) VALUES (?, ?, ?, ?, ?, ?, ?);");
		$this->db->querySingle("UPDATE shopItemCount SET id = '{$id}' WHERE category = '{$category}';");
		$query->bindParam(1, $itemName);
		$query->bindParam(2, $price);
		$query->bindParam(3, $amount);
		$query->bindParam(4, $itemId);
		$query->bindParam(5, $itemMeta);
		$query->bindParam(6, $id);
		$query->bindParam(7, $category);
		$query->execute();

		return true;
	}

	/**
	 *
	 * @param int $category
	 * @param int $id
	 *
	 * @return array
	 *
	 */
	public function getShop(int $category, int $id){
		if($this->shopExists($category, $id)){
			$query = $this->db->query("SELECT * FROM shop WHERE id = '{$id}' AND category = '{$category}';");
			while($q = $query->fetchArray(SQLITE3_ASSOC)){
				return $q;
			}
		}

		return [];
	}

	/**
	 *
	 * @API
	 *
	 * @param int $category
	 * @param int $id
	 *
	 * @return bool
	 *
	 */
	public function shopExists(int $category, int $id){
		$query = $this->db->query("SELECT item FROM shop WHERE id ='{$id}' AND category = '$category';");
		$fetch = $query->fetchArray(SQLITE3_ASSOC);

		if(is_array($fetch) && count($fetch) != 0){
			return true;
		}

		return false;
	}

	/**
	 *
	 * @API
	 *
	 * @param int $category
	 * @param int $id
	 *
	 */
	public function removeShop(int $category, int $id){
		if($this->shopExists($category, $id)){
			$this->db->querySingle("DELETE FROM shop WHERE id = '{$id}';", true);
		}
	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function getSelectCategoryUI(Player $player){
		$ui = new SimpleForm();
		$ui->setId($this->SELECT_CATEGORY_ID);
		$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lSelect Category&r&k&e|"));
		$ui->setContent("");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dBlocks&e&k|\n" .
			"&r&8Click to buy Blocks!"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/blocks.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dFood&e&k|\n" .
			"&r&8Click to buy Food!"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/food.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dPotions&e&k|\n" .
			"&r&8Click to buy Potions!"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/potions.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dRare Items&e&k|\n" .
			"&r&8Click to buy Misc items!"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/misc.png");
		$ui->send($player);
	}

	/**
	 *
	 * @param Player $player
	 * @param int $category
	 * @param int $id
	 *
	 * @return bool
	 *
	 */
	public function getRemoveShopUI(Player $player, int $category, int $id){
		$shops = $this->getShop($category, $id);
		if(count($shops) > 0){
			$ui = new SimpleForm();
			$ui->setId($this->REMOVE_SHOP_ID);
			$ui->setTitle("Remove this item from the shop?");
			$ui->setButton("", "http://permat.comli.com/items/{$shops['itemId']}-{$shops['itemMeta']}.png");
			$content = "";
			$content .= RandomUtils::colorMessage("Item: " . $shops['item'] . "\n");
			$content .= RandomUtils::colorMessage("&rItem ID: " . $shops['itemId'] . "\n");
			$content .= RandomUtils::colorMessage("&rAmount: " . $shops['amount'] . "\n");
			$content .= RandomUtils::colorMessage("&rPrice: " . $shops['price'] . " silver coins\n");
			$ui->setContent($content);
			$ui->setButton("Yes");
			$ui->setButton("No");
			$ui->send($player);

			return true;
		}

		return false;
	}

	/**
	 *
	 * @param Player $player
	 * @param int $category
	 * @param int $id
	 *
	 * @return bool
	 *
	 */
	public function getShopUI(Player $player, int $category, int $id){
		$shops = $this->getShop($category, $id);

		if(count($shops) > 0){
			$ui = new SimpleForm();
			$ui->setId($this->SHOP_ID);
			$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lDO YOU WANT TO BUY THIS ITEM?&r&k&e|"));
			$content = "";
			$content .= RandomUtils::colorMessage("&l&e===========================\n");
			$content .= RandomUtils::colorMessage("&r&dItem: &7" . $shops['item'] . "\n\n");
			$content .= RandomUtils::colorMessage("&r&dItem ID: &7" . $shops['itemId'] . "\n\n");
			$content .= RandomUtils::colorMessage("&r&dAmount: &7" . $shops['amount'] . "\n\n");
			$content .= RandomUtils::colorMessage("&r&dPrice: &7" . $shops['price'] . " silver coins\n");
			$content .= RandomUtils::colorMessage("&l&e===========================\n");
			$ui->setContent($content);
			$ui->setButton(RandomUtils::colorMessage("&a&lYES"));
			$ui->setButton(RandomUtils::colorMessage("&c&lNO"));
			$ui->send($player);

			return true;
		}

		return false;
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	public function createShopInfo(Player $player){
		$lang = $this->core->PrestigeSocietyLang->getLang($player);
		$message = 'unknown';
		switch($lang){
			case 0:
				$message = "&7Probably if you read this message you are wondering what's up with this shop and these 2 categories.\n\n\n\n               &e&lNORMAL SHOP\n\n&r&7If you want to earn money on the server, type &8[&d/jobs&8] &7and start working. As a result of your work, you will receive a sum of money with which you can buy items from the normal shop.\n\n\n               &e&lPREMIUM SHOP\n\n&r&7If you want to get credits, acces &dstore.chpe.us &7if you want to buy using Paypal or contact &dMRN#1358 &7on discord if you want to use other methods of payment. With these credits you can buy different perks from the premium shop.";
				break;
			case 1:
				$message = "&7Probabil daca citesti acest mesaj te intrebi care este treaba cu acest shop si cu aceste 2 categorii.\n\n\n\n               &e&lNORMAL SHOP\n\n&r&7Pentru a face bani pe server, selecteaza unul dintre joburi &8[&d/jobs&8] &7si incepe munca. In urma muncii tale, o sa primesti o suma cu bani cu ajutorul careia poti cumpara iteme din normal shop.\n\n\n               &e&lPREMIUM SHOP\n\n&r&7Pentru a face rost de credite, acceseaza &dstore.chpe.us &7daca doresti sa cumperi prin PayPal sau contacteaza-l pe &dMRN#1358 &7pe discord daca doresti sa cumperi prin alta metoda de plata. Cu creditele repsective poti cumpara diferite perkuri din premium shop.";
				break;
		}
		$message = RandomUtils::colorMessage($message);
		$ui = new CustomForm();
		$ui->setId($this->ADD_SHOP_INFO);
		$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lInfo&r&k&e|"));
		$ui->setLabel($message);
		$ui->send($player);

		return true;
	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function getSelectCategoryUIPremium(Player $player){
		$ui = new SimpleForm();
		$ui->setId($this->CATEGORY_PREMIUM);
		$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lPremium Shop&r&k&e|"));
		$ui->setContent("");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dGold Member&e&k|\n" .
			"&r&81 month\n" .
			"&r&8200 credits"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/blocks.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dColored Name&e&k|\n" .
			"&r&8100-250 credits"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/armor.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dMoney&e&k|\n" .
			"&r&820.000 silver coins\n" .
			"&r&850 credits"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/tools.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dMoney&e&k|\n" .
			"&r&840.000 silver coins\n" .
			"&r&8100 credits"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/weapons.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dMoney&e&k|\n" .
			"&r&8x60.000 silver coins\n" .
			"&r&8150 credits"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/food.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dEpic Crate Key&e&k|\n" .
			"&r&8x1\n" .
			"&r&850 credits"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/potions.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dCustom Enchant Crate Key&e&k|\n" .
			"&r&8x1\n" .
			"&r&8100 credits"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/misc.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dAdditional Factions Slots&e&k|\n" .
			"&r&8150 credits"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/misc.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dVIP Member&e&k|\n" .
			"&r&83 month\n" .
			"&r&81000 credits"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/misc.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dAdditional Set Homes&e&k|\n" .
			"&r&8x1\n" .
			"&r&875 credits"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/misc.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dAdditional Enchantments&e&k|\n" .
			"&r&8x1\n" .
			"&r&8175 credits"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/misc.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dRepair Acces&e&k|\n" .
			"&r&8150 credits"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/misc.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dLSD Acces&e&k|\n" .
			"&r&8750 credits"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/misc.png");
		$ui->send($player);
	}

	public function addButtonGoldenMember(Player $player, int $needed){
		$ui = new SimpleForm();
		$ui->setId($this->GOLDEN_MEMBER);
		$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lAre you sure?&r&k&e|"));
		$content = "";
		$content .= RandomUtils::colorMessage("&6[!] &eAre you sure?\n&eYou will buy &6Golden Member &esilver coins for $needed credits.");
		$ui->setContent($content);
		$ui->setButton(RandomUtils::colorMessage("&a&lYES"));
		$ui->setButton(RandomUtils::colorMessage("&c&lNO"));
		$ui->send($player);

		return true;
	}

	public function createUIName(Player $player){
		$ui = new SimpleForm();
		$ui->setId($this->SELECT_CATEGORY_NAME);
		$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lCustom Name&r&k&e|"));
		$content = "";
		$message = $this->core->getMessage('additionalname', 'description');
		$message = str_replace('@coins', $this->core->PrestigeSocietyCredits->getCredits($player), $message);
		$content .= RandomUtils::colorMessage($message);
		$ui->setContent($content);
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dColored Name&e&k|\n" .
			"&r&8100 credits"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/blocks.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dRainbow Name&e&k|\n" .
			"&r&8250 credits"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/blocks.png");
		$ui->send($player);

		return true;
	}

	public function addButtonSureMoneyFirst(Player $player, int $needed, int $money){
		$money = $money - 50;
		$ui = new SimpleForm();
		$ui->setId($this->MONEY_SURE_FIRST);
		$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lAre you sure?&r&k&e|"));
		$content = "";
		$content .= RandomUtils::colorMessage("&6[!] &eAre you sure?\n&eYou will buy " . $money . " silver coins for $needed credits.");
		$ui->setContent($content);
		$ui->setButton(RandomUtils::colorMessage("&a&lYES"));
		$ui->setButton(RandomUtils::colorMessage("&c&lNO"));
		$ui->send($player);

		return true;
	}

	public function addButtonSureMoneySecond(Player $player, int $needed, int $money){
		$money = $money - 100;
		$ui = new SimpleForm();
		$ui->setId($this->MONEY_SURE_SECOND);
		$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lAre you sure?&r&k&e|"));
		$content = "";
		$content .= RandomUtils::colorMessage("&6[!] &eAre you sure?\n&eYou will buy " . $money . " silver coins for $needed credits.");
		$ui->setContent($content);
		$ui->setButton(RandomUtils::colorMessage("&a&lYES"));
		$ui->setButton(RandomUtils::colorMessage("&c&lNO"));
		$ui->send($player);

		return true;
	}

	public function addButtonSureMoneyThree(Player $player, int $needed, int $money){
		$money = $money - 150;
		$ui = new SimpleForm();
		$ui->setId($this->MONEY_SURE_THREE);
		$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lAre you sure?&r&k&e|"));
		$content = "";
		$content .= RandomUtils::colorMessage("&6[!] &eAre you sure?\n&eYou will buy " . $money . " silver coins for $needed credits.");
		$ui->setContent($content);
		$ui->setButton(RandomUtils::colorMessage("&a&lYES"));
		$ui->setButton(RandomUtils::colorMessage("&c&lNO"));
		$ui->send($player);

		return true;
	}

	public function addSlotsFaction(Player $player){
		$ui = new CustomForm();
		$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lAdditional Factions Slots&r&k&e|"));
		$ui->setId($this->ADDITIONAL_SLOTS);
		$ui->setLabel(RandomUtils::colorMessage("&eFrom here you can get permission to set more slots for your faction.\n&eSelect count below."));
		$ui->setSlider(RandomUtils::colorMessage("&7Amount"), 1, 5, 1, 1);
		$ui->send($player);

		return true;
	}

	public function addVip(Player $player, int $needed){
		$ui = new SimpleForm();
		$ui->setId($this->VIP_MEMBER);
		$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lAre you sure?&r&k&e|"));
		$content = "";
		$content .= RandomUtils::colorMessage("&6[!] &eAre you sure?\n&eYou will buy &6Golden Member &esilver coins for $needed credits.");
		$ui->setContent($content);
		$ui->setButton(RandomUtils::colorMessage("&a&lYES"));
		$ui->setButton(RandomUtils::colorMessage("&c&lNO"));
		$ui->send($player);

		return true;
	}

	public function addHomes(Player $player){
		$ui = new CustomForm();
		$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lAdditional Homes&r&k&e|"));
		$ui->setId($this->ADDITIONAL_HOMES);
		$ui->setLabel(RandomUtils::colorMessage("&eFrom here you can get permission to set more homes.\n&eSelect count below."));
		$ui->setSlider(RandomUtils::colorMessage("&7Amount"), 1, 20, 1, 1);
		$ui->send($player);

		return true;
	}

	public function addEnch(Player $player){
		$ui = new CustomForm();
		$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lAdditional Enchantmentss&r&k&e|"));
		$ui->setId($this->ADDITIONAL_ENCH);
		$ui->setLabel(RandomUtils::colorMessage("&eFrom here you can get permission to set more enchantments.\n&eSelect count below."));
		$ui->setSlider(RandomUtils::colorMessage("&7Amount"), 1, 2, 1, 1);
		$ui->send($player);

		return true;
	}

	public function createUIColoredName(Player $player){
		$ui = new SimpleForm();
		$ui->setId($this->SELECT_CATEGORY_NAME_COLORED);
		$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lColored Name&r&k&e|"));
		$content = "";
		$message = $this->core->getMessage('additionalname', 'description_colored');
		$message = str_replace('@coins', $this->core->PrestigeSocietyCredits->getCredits($player), $message);
		$content .= RandomUtils::colorMessage($message);
		$ui->setContent($content);
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dClear Name&e&k|\n" .
			"&r&8Free"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/blocks.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dDark Blue&e&k|\n" .
			"&r&8100 credits"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/blocks.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dRose&e&k|\n" .
			"&r&8100 credits"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/blocks.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dOrange&e&k|\n" .
			"&r&8100 credits"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/blocks.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dBlue&e&k|\n" .
			"&r&8100 credits"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/blocks.png");
		$ui->setButton(RandomUtils::colorMessage(
			"&l&k&e|&r&l&dYellow&e&k|\n" .
			"&r&8100 credits"
		), "https://raw.githubusercontent.com/xBeastMode/psicons2/master/blocks.png");
		$ui->send($player);

		return true;
	}

	public function handleRainbowName(Player $player){
		$name = $player->getName();
		$splitname = str_split($name);
		$count = count($splitname);
		$arr = [''];
		for($i = 0; $i < $count; $i++){
			$f = $i;
			if($f > 5){
				$f = 6;
				$f = $i - $f;
			}
			switch($f){
				case 0:
					$f = "5";
					break;
				case 2:
					$f = "b";
					break;
				case 3:
					$f = "e";
					break;
				case 4:
					$f = "c";
					break;
				case 5:
					$f = "d";
					break;
			}
			$name = "&" . $f . $splitname[$i];
			array_push($arr, $name);
		}
		$name = implode($arr, '');
		$name = $name . "&r";
		$name = TextFormat::colorize($name);
		$this->core->getPrestigeSocietyNicks()->setNick($player, $name);
		$this->core->getPrestigeSocietyCredits()->subtractCredits($player, 250);

		return;
	}

	public function addMoney(Player $player, int $needed, int $credits, int $money){
		if($credits >= $needed){
			$this->core->getPrestigeSocietyCredits()->subtractCredits($player, $needed);
			$this->core->getPrestigeSocietyEconomy()->addMoney($player, $money);
		}else{
			$message = $this->core->getMessage('additionalname', 'no_credits');
			$message = str_replace('@coins', $credits, $message);
			$message = RandomUtils::colorMessage($message);
			$player->sendMessage($message);

			return;
		}
	}

}