<?php

namespace PrestigeSociety\Enchants;

use _64FF00\PurePerms\PurePerms;
use pocketmine\item\Armor;
use pocketmine\item\Bow;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\TieredTool;
use pocketmine\Player;
use pocketmine\utils\Config;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;
use PrestigeSociety\UIForms\CustomForm;
use PrestigeSociety\UIForms\SimpleForm;

class PrestigeSocietyEnchants {

	private const ENCHANTMENT_NAMES = [
		'Bane of Arthropods',
		'Blast Protection',
		'Depth Strider',
		'Efficiency',
		'Feather Falling',
		'Fire Aspect',
		'Fire Protection',
		'Infinity',
		'Knockback',
		'Looting',
		'Power',
		'Projectile Protection',
		'Protection',
		'Punch',
		'Sharpness',
	];
	/** @var Item[] */
	protected $queue = [];
	/** @var PrestigeSocietyCore */
	protected $core;
	/** @var int[] */
	protected $enchantsCost;
	private $CHOOSE_ENCHANT_ID = 0, $BUY_ENCHANT_CONFIRM_ID = 0;

	/**
	 *
	 * PrestigeSocietyEnchants constructor.
	 *
	 * @param PrestigeSocietyCore $core
	 *
	 */
	public function __construct(PrestigeSocietyCore $core){
		$this->core = $core;

		$defaults = [];

		foreach(self::ENCHANTMENT_NAMES as $enchantment){
			$enchantment = strtolower(str_replace(" ", "_", $enchantment));
			$defaults[$enchantment] = 5000;
		}

		$this->enchantsCost = (new Config($core->getDataFolder() . "enchants_cost.yml", Config::YAML, $defaults))->getAll();

		$this->CHOOSE_ENCHANT_ID = mt_rand(100000, 999999);
		$this->BUY_ENCHANT_CONFIRM_ID = $this->CHOOSE_ENCHANT_ID + 1;
	}

	/**
	 *
	 * @param Player $player
	 *
	 * @return bool
	 *
	 */
	public function getChooseEnchantUI(Player $player){
		$item = $player->getInventory()->getItemInHand();
		if($item instanceof Armor or
			$item instanceof TieredTool or
			$item instanceof Bow){
			$ui = new SimpleForm();
			$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lCHOOSE ENCHANTMENT&r&k&e|&r"));
			$ui->setId($this->CHOOSE_ENCHANT_ID);
			$ui->setContent("");
			$ui->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&dBane Of Arthropods&e&k|&r"));
			$ui->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&dBlast Protection&e&k|&r"));
			$ui->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&dDepth Strider&e&k|&r"));
			$ui->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&dEfficiency&e&k|&r"));
			$ui->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&dFeather Falling&e&k|&r"));
			$ui->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&dFire Aspect&e&k|&r"));
			$ui->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&dFire Protection&e&k|&r"));
			$ui->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&dInfinity&e&k|&r"));
			$ui->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&dKnockback&e&k|&r"));
			$ui->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&dLooting&e&k|&r"));
			$ui->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&dPower&e&k|&r"));
			$ui->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&dProjectile Protection&e&k|&r"));
			$ui->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&dProtection&e&k|&r"));
			$ui->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&dPunch&e&k|&r"));
			$ui->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&dSharpness&e&k|&r"));
			$ui->send($player);

			return true;
		}else{
			return false;
		}
	}

	/**
	 *
	 * @param Player $player
	 * @param        $formData
	 * @param int $formId
	 *
	 */
	public function handleFormResponse(Player $player, $formData, int $formId){
		$item = $player->getInventory()->getItemInHand();
		switch($formId){
			case  $this->CHOOSE_ENCHANT_ID:
				$this->queue[$player->getXuid()] = [];
				$this->queue[$player->getXuid()]['enchant'] = self::ENCHANTMENT_NAMES[$formData];
				$this->getBuyEnchantConfirm($player);
				break;
			case $this->BUY_ENCHANT_CONFIRM_ID:
				$enchant = $this->queue[$player->getXuid()]['enchant'];
				$enchant = str_replace(" ", "_", $enchant);

				if($item->hasEnchantments()){
					$count = count($item->getEnchantments());
					$group = PurePerms::getAPI()->getUserDataMgr()->getGroup($player);
					if($this->core->PrestigeSocietyEnchantments->playerExists($player)){
						$database = $this->core->PrestigeSocietyEnchantments->getEnchList($player);
						if($count >= 4){
							if($count >= $database){
								$message = "&6[!] &cYou reached the maximum number of enchants for this item @count.\n&cIf you want to unlock this, go to premium shop.";
								$message = str_replace("@count", $count, $message);
								$message = RandomUtils::colorMessage($message);
								$player->sendMessage($message);

								return;
							}
						}
					}else{
						$this->core->PrestigeSocietyEnchantments->addNewPlayer($player);
						switch($group){
							case "Member":
								$this->core->PrestigeSocietyEnchantments->setEnchList($player, 3);
								break;
							case "VIP":
								$this->core->PrestigeSocietyEnchantments->setEnchList($player, 5);
								break;
							case "GOLD":
								$this->core->PrestigeSocietyEnchantments->setEnchList($player, 4);
								break;
							default:
								$this->core->PrestigeSocietyEnchantments->setEnchList($player, 999);
						}
					}
				}

				switch($enchant){
					case 'Bane_of_Arthropods':
						switch($item->getId()){
							case Item::DIAMOND_SWORD:
							case Item::GOLD_SWORD:
							case Item::IRON_SWORD:
							case Item::STONE_SWORD:
							case Item::WOODEN_SWORD:
								break;
							default:
								$message = RandomUtils::colorMessage("&6[!] &cYou can't enchant this Item with this Enchantment!");
								$player->sendMessage($message);

								return;
						}
						break;
					case 'Blast_Protection':
						switch($item->getId()){
							case Item::DIAMOND_CHESTPLATE:
							case Item::GOLD_CHESTPLATE:
							case Item::CHAINMAIL_CHESTPLATE:
							case Item::IRON_CHESTPLATE:
							case Item::LEATHER_CHESTPLATE:
							case Item::CHAIN_LEGGINGS:
							case Item::DIAMOND_LEGGINGS:
							case Item::GOLD_LEGGINGS:
							case Item::IRON_LEGGINGS:
							case Item::LEATHER_LEGGINGS:
							case Item::CHAIN_BOOTS:
							case Item::DIAMOND_BOOTS:
							case Item::GOLD_BOOTS:
							case Item::IRON_BOOTS:
							case Item::LEATHER_BOOTS:
							case Item::DIAMOND_HELMET:
							case Item::CHAIN_HELMET:
							case Item::GOLD_HELMET:
							case Item::IRON_HELMET:
							case Item::LEATHER_HELMET:
								break;
							default:
								$message = RandomUtils::colorMessage("&6[!] &cYou can't enchant this Item with this Enchantment!");
								$player->sendMessage($message);

								return;
						}
						break;
					case 'Depth_Strider':
						switch($item->getId()){
							case Item::CHAIN_BOOTS:
							case Item::DIAMOND_BOOTS:
							case Item::GOLD_BOOTS:
							case Item::IRON_BOOTS:
							case Item::LEATHER_BOOTS:
								break;
							default:
								$message = RandomUtils::colorMessage("&6[!] &cYou can't enchant this Item with this Enchantment!");
								$player->sendMessage($message);

								return;
						}
						break;
					case 'Efficiency':
						switch($item->getId()){
							case Item::DIAMOND_AXE:
							case Item::GOLD_AXE:
							case Item::IRON_AXE:
							case Item::STONE_AXE:
							case Item::WOODEN_AXE:
							case Item::DIAMOND_SHOVEL:
							case Item::GOLD_SHOVEL:
							case Item::IRON_SHOVEL:
							case Item::STONE_SHOVEL:
							case Item::WOODEN_SHOVEL:
							case Item::DIAMOND_PICKAXE:
							case Item::GOLD_PICKAXE:
							case Item::IRON_PICKAXE:
							case Item::STONE_PICKAXE:
							case Item::WOODEN_PICKAXE:
								break;
							default:
								$message = RandomUtils::colorMessage("&6[!] &cYou can't enchant this Item with this Enchantment!");
								$player->sendMessage($message);

								return;
						}
						break;
					case 'Feather_Falling':
						switch($item->getId()){
							case Item::CHAIN_BOOTS:
							case Item::DIAMOND_BOOTS:
							case Item::GOLD_BOOTS:
							case Item::IRON_BOOTS:
							case Item::LEATHER_BOOTS:
								break;
							default:
								$message = RandomUtils::colorMessage("&6[!] &cYou can't enchant this Item with this Enchantment!");
								$player->sendMessage($message);

								return;
						}
						break;
					case 'Fire_Aspect':
						switch($item->getId()){
							case Item::DIAMOND_SWORD:
							case Item::GOLD_SWORD:
							case Item::IRON_SWORD:
							case Item::STONE_SWORD:
							case Item::WOODEN_SWORD:
								break;
							default:
								$message = RandomUtils::colorMessage("&6[!] &cYou can't enchant this Item with this Enchantment!");
								$player->sendMessage($message);

								return;
						}
						break;
					case 'Fire_Protection':
						switch($item->getId()){
							case Item::DIAMOND_CHESTPLATE:
							case Item::GOLD_CHESTPLATE:
							case Item::CHAINMAIL_CHESTPLATE:
							case Item::IRON_CHESTPLATE:
							case Item::LEATHER_CHESTPLATE:
							case Item::CHAIN_LEGGINGS:
							case Item::DIAMOND_LEGGINGS:
							case Item::GOLD_LEGGINGS:
							case Item::IRON_LEGGINGS:
							case Item::LEATHER_LEGGINGS:
							case Item::CHAIN_BOOTS:
							case Item::DIAMOND_BOOTS:
							case Item::GOLD_BOOTS:
							case Item::IRON_BOOTS:
							case Item::LEATHER_BOOTS:
							case Item::DIAMOND_HELMET:
							case Item::CHAIN_HELMET:
							case Item::GOLD_HELMET:
							case Item::IRON_HELMET:
							case Item::LEATHER_HELMET:
								break;
							default:
								$message = RandomUtils::colorMessage("&6[!] &cYou can't enchant this Item with this Enchantment!");
								$player->sendMessage($message);

								return;
						}
						break;
					case 'Infinity':
						switch($item->getId()){
							case Item::BOW:
								break;
							default:
								$message = RandomUtils::colorMessage("&6[!] &cYou can't enchant this Item with this Enchantment!");
								$player->sendMessage($message);

								return;
						}
						break;
					case 'Knockback':
						switch($item->getId()){
							case Item::DIAMOND_SWORD:
							case Item::GOLD_SWORD:
							case Item::IRON_SWORD:
							case Item::STONE_SWORD:
							case Item::WOODEN_SWORD:
								break;
							default:
								$message = RandomUtils::colorMessage("&6[!] &cYou can't enchant this Item with this Enchantment!");
								$player->sendMessage($message);

								return;
						}
						break;
					case 'Looting':
						switch($item->getId()){
							case Item::DIAMOND_SWORD:
							case Item::GOLD_SWORD:
							case Item::IRON_SWORD:
							case Item::STONE_SWORD:
							case Item::WOODEN_SWORD:
								break;
							default:
								$message = RandomUtils::colorMessage("&6[!] &cYou can't enchant this Item with this Enchantment!");
								$player->sendMessage($message);

								return;
						}
						break;
					case 'Power':
						switch($item->getId()){
							case Item::BOW:
								break;
							default:
								$message = RandomUtils::colorMessage("&6[!] &cYou can't enchant this Item with this Enchantment!");
								$player->sendMessage($message);

								return;
						}
						break;
					case 'Projectile_Protection':
						switch($item->getId()){
							case Item::DIAMOND_CHESTPLATE:
							case Item::GOLD_CHESTPLATE:
							case Item::CHAINMAIL_CHESTPLATE:
							case Item::IRON_CHESTPLATE:
							case Item::LEATHER_CHESTPLATE:
							case Item::CHAIN_LEGGINGS:
							case Item::DIAMOND_LEGGINGS:
							case Item::GOLD_LEGGINGS:
							case Item::IRON_LEGGINGS:
							case Item::LEATHER_LEGGINGS:
							case Item::CHAIN_BOOTS:
							case Item::DIAMOND_BOOTS:
							case Item::GOLD_BOOTS:
							case Item::IRON_BOOTS:
							case Item::LEATHER_BOOTS:
							case Item::DIAMOND_HELMET:
							case Item::CHAIN_HELMET:
							case Item::GOLD_HELMET:
							case Item::IRON_HELMET:
							case Item::LEATHER_HELMET:
								break;
							default:
								$message = RandomUtils::colorMessage("&6[!] &cYou can't enchant this Item with this Enchantment!");
								$player->sendMessage($message);

								return;
						}
						break;
					case 'Protection':
						switch($item->getId()){
							case Item::DIAMOND_CHESTPLATE:
							case Item::GOLD_CHESTPLATE:
							case Item::CHAINMAIL_CHESTPLATE:
							case Item::IRON_CHESTPLATE:
							case Item::LEATHER_CHESTPLATE:
							case Item::CHAIN_LEGGINGS:
							case Item::DIAMOND_LEGGINGS:
							case Item::GOLD_LEGGINGS:
							case Item::IRON_LEGGINGS:
							case Item::LEATHER_LEGGINGS:
							case Item::CHAIN_BOOTS:
							case Item::DIAMOND_BOOTS:
							case Item::GOLD_BOOTS:
							case Item::IRON_BOOTS:
							case Item::LEATHER_BOOTS:
							case Item::DIAMOND_HELMET:
							case Item::CHAIN_HELMET:
							case Item::GOLD_HELMET:
							case Item::IRON_HELMET:
							case Item::LEATHER_HELMET:
								break;
							default:
								$message = RandomUtils::colorMessage("&6[!] &cYou can't enchant this Item with this Enchantment!");
								$player->sendMessage($message);

								return;
						}
						break;
					case 'Punch':
						switch($item->getId()){
							case Item::BOW:
								break;
							default:
								$message = RandomUtils::colorMessage("&6[!] &cYou can't enchant this Item with this Enchantment!");
								$player->sendMessage($message);

								return;
						}
						break;
					case 'Sharpness':
						switch($item->getId()){
							case Item::DIAMOND_SWORD:
							case Item::GOLD_SWORD:
							case Item::IRON_SWORD:
							case Item::STONE_SWORD:
							case Item::WOODEN_SWORD:
							case Item::DIAMOND_AXE:
							case Item::GOLD_AXE:
							case Item::IRON_AXE:
							case Item::STONE_AXE:
							case Item::WOODEN_AXE:
								break;
							default:
								$message = RandomUtils::colorMessage("&6[!] &cYou can't enchant this Item with this Enchantment!");
								$player->sendMessage($message);

								return;
						}
						break;
				}
				$enchant = Enchantment::getEnchantmentByName($enchant);
				$enchant = new EnchantmentInstance($enchant, $formData[1]);

				if($item->hasEnchantment($enchant->getId()) && $item->getEnchantmentLevel($enchant->getId()) >= $enchant->getType()->getMaxLevel()){
					$message = $this->core->getMessage('enchants', 'already_enchanted');
					$message = str_replace(["@enchant", "@level"], [$enchant->getType()->getName(), $enchant->getLevel()], $message);
					$player->sendMessage(RandomUtils::colorMessage($message));

					return;
				}

				$index = strtolower(str_replace(" ", "_", $this->queue[$player->getXuid()]['enchant']));
				$cost = $this->enchantsCost[$index];
				$cost = $cost * $enchant->getLevel();

				if(($money = $this->core->PrestigeSocietyEconomy->getMoney($player)) < $cost){
					$message = $this->core->getMessage('enchants', 'not_enough_money');
					$message = str_replace(["@money", "@cost"], [$money, $cost], $message);
					$player->sendMessage(RandomUtils::colorMessage($message));

					return;
				}

				$this->core->PrestigeSocietyEconomy->subtractMoney($player, $cost);
				$item->addEnchantment($enchant);
				$index = $player->getInventory()->getHeldItemIndex();
				$player->getInventory()->setItem($index, $item);
				$message = $this->core->getMessage('enchants', 'bought_enchantment');
				$message = str_replace(["@enchant", "@level", "@cost"], [$this->queue[$player->getXuid()]['enchant'], $enchant->getLevel(), $cost], $message);
				$player->sendMessage(RandomUtils::colorMessage($message));

				unset($this->queue[$player->getXuid()]);
				break;
		}
	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function getBuyEnchantConfirm(Player $player){
		$ui = new CustomForm();
		$enchant = $this->queue[$player->getXuid()]['enchant'];
		$enchantName = str_replace(" ", "_", $enchant);
		$ui->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lBUY $enchant&r&k&e|&r"));
		$ui->setLabel(RandomUtils::colorMessage("&dEnchant cost per level: &7" . $this->enchantsCost[strtolower($enchantName)]));
		$max = Enchantment::getEnchantmentByName($enchantName)->getMaxLevel();
		$ui->setSlider(RandomUtils::colorMessage("&d&lLevel"), 1, $max, 1, 1);
		$ui->setId($this->BUY_ENCHANT_CONFIRM_ID);
		$ui->send($player);
	}

}