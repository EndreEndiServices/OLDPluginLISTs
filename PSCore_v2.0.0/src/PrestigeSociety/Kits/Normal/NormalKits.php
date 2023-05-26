<?php

namespace PrestigeSociety\Kits\Normal;

use pocketmine\block\Block;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\permission\Permission;
use pocketmine\Player;
use pocketmine\tile\Chest;
use pocketmine\tile\Tile;
use pocketmine\utils\Config;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\exc;
use PrestigeSociety\Core\Utils\RandomUtils;

class NormalKits {

	/** @var PrestigeSocietyCore */
	protected $core;

	/** @var Config */
	protected $kits;

	/** @var Block[][]|ChestInventory[][] */
	private $queue = [];

	/**
	 *
	 * NormalKits constructor.
	 *
	 * @param PrestigeSocietyCore $core
	 *
	 */
	public function __construct(PrestigeSocietyCore $core){
		$this->core = $core;
		$this->kits = new Config($core->getDataFolder() . "kits.yml", Config::YAML, [
			'starter' => [
				'display_item'         => '299:0:1:&aTest Kit:protection:3',
				'cooldown'             => '30s',
				'helmet'               => '298:0:1',
				'chest'                => '299:0:1:default:protection:1',
				'legs'                 => '300:0:1',
				'boots'                => '301:0:1',
				'items'                => [
					"268:0:1:default:sharpness:1",
				],
				'commands'             => [
					"tell @player &5You bought the @kit kit for @cost silver coins.\n&dplease wait &7@days days, @hours hours, @minutes minutes, and @seconds seconds &dto use again!",
				],
				'effects'              => [
					"strength:1:30",
				],
				'cost'                 => 500,
				'description'          => [
					"&2A very gucci kit",
				],
				'cooldown_description' => [
					'&eThis kit is on cooldown. Wait &7@days days',
					'&7@hours hours',
					'&7@minutes minutes',
					'&7and @seconds &e to use again.',
				],
			],
		]);

		foreach($this->kits->getAll() as $key => $value){
			if($core->getServer()->getPluginManager()->getPermission("kit." . $key) === null){
				$core->getServer()->getPluginManager()->addPermission(new Permission("kit." . $key, "", Permission::DEFAULT_OP));
			}
		}
	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function tryResetQueue(Player $player){
		$xuid = $player->getXuid();
		if(isset($this->queue[$xuid])){
			$block = $this->queue[$xuid]['old'];
			$player->level->sendBlocks([$player], [$block]);
			unset($this->queue[$xuid]);
		}
	}

	/**
	 *
	 * @param InventoryTransaction $transaction
	 *
	 * @param bool $cancel
	 *
	 */
	public function callTransaction(InventoryTransaction $transaction, bool &$cancel){
		$player = $transaction->getSource();

		$xuid = $player->getXuid();

		if(isset($this->queue[$xuid])){

			foreach($transaction->getActions() as $action){

				$cancel = true;

				$item = $action->getSourceItem();

				if($item->hasCustomBlockData() && $item->getCustomBlockData()->hasTag("kit_name")){

					$kit = $item->getCustomBlockData()->getString("kit_name");
					$kitData = $this->kits->get($kit);

					/** @var \DateTime $date */
					$date = exc::stringToTimestamp($kitData['cooldown'])[0];

					$time = $date->getTimestamp();

					if(!$this->core->PrestigeSocietyKits->checkCoolDown($player, $kit)){

						$coolDown = $this->core->PrestigeSocietyKits->getCoolDown($player, $kit);
						$coolDown = exc::secondsToDHMS($coolDown);
						$message = $this->core->getMessage('kits', 'on_cooldown');
						$message = str_replace(["@days", "@hours", "@minutes", "@seconds", "@kit"], [$coolDown[0], $coolDown[1], $coolDown[2], $coolDown[3], $kit], $message);
						$player->sendMessage(RandomUtils::colorMessage($message));

					}else{

						if(($money = $this->core->PrestigeSocietyEconomy->getMoney($player)) < intval($kitData['cost'])){

							$message = $this->core->getMessage('kits', 'non_sufficient_funds');
							$message = str_replace(["@kit", "@money"], [$kit, $money], $message);
							$player->sendMessage(RandomUtils::colorMessage($message));

							return;
						}

						if(count($player->getInventory()->getContents()) > 1 || count($player->getArmorInventory()->getContents()) > 1){

							$message = $this->core->getMessage('kits', 'empty_inventory_first');
							$player->sendMessage(RandomUtils::colorMessage($message));

							return;
						}

						$this->core->PrestigeSocietyKits->setCoolDown($player, $kit, $time);
						$this->core->PrestigeSocietyEconomy->subtractMoney($player, (int)$kitData['cost']);

						$armor = $this->parseItemsWithEnchants([$kitData['helmet'], $kitData['chest'], $kitData['legs'], $kitData['boots']]);
						$items = $this->parseItemsWithEnchants($kitData['items']);

						$this->setRawKit($player, $items, $armor);

						$coolDown = $this->core->PrestigeSocietyKits->getCoolDown($player, $kit);
						$coolDown = exc::secondsToDHMS($coolDown);

						foreach($this->parseEffects($kitData['effects']) as $effect){
							$player->addEffect($effect);
						}

						foreach($kitData['commands'] as &$command){
							$command = str_replace(["@days", "@hours", "@minutes", "@seconds", "@kit", "@player", "@cost"],
								[$coolDown[0], $coolDown[1], $coolDown[2], $coolDown[3], $kit, $player->getName(), $kitData['cost']], $command);
							$command = RandomUtils::colorMessage($command);
						}

						$this->sendCommands($kitData['commands']);
					}
				}
			}
		}
	}

	/**
	 *
	 * @param array $items
	 *
	 * @return Item[]
	 *
	 */
	public function parseItemsWithEnchants(array $items){
		$out = [];

		foreach($items as $key => $item){
			if($item instanceof Item){
				$out[] = $item;
			}else{

				$parts = explode(':', $item);

				$id = array_shift($parts);
				$meta = array_shift($parts);
				$amount = array_shift($parts);
				$name = array_shift($parts);

				$item = Item::fromString("$id:$meta");

				if(!($item->getId() === Item::AIR)){

					$item->setCount($amount);

					$parts = implode(":", $parts);

					foreach($this->parseEnchants([$parts]) as $enchant){
						$item->addEnchantment($enchant);
					}

					if(strtolower($name) !== "default"){
						$item->setCustomName(RandomUtils::colorMessage($name));
					}

					$out[] = $item;
				}

			}
		}

		return $out;
	}

	/**
	 *
	 * @param array $enchants
	 *
	 * @return array|EnchantmentInstance[]
	 *
	 */
	public function parseEnchants(array $enchants){
		/** @var EnchantmentInstance[] $out */
		$out = [];

		$i = 1;

		/** @var Enchantment $lastEnchantment */
		$lastEnchantment = null;

		foreach($enchants as $enchant){
			if($enchant instanceof EnchantmentInstance){
				$out[] = $enchant;
			}else{
				$parts = explode(':', $enchant);

				foreach($parts as $part){
					if(($i % 2) === 0){
						if($lastEnchantment !== null){
							$out[] = new EnchantmentInstance($lastEnchantment, $part);
						}
					}else{
						$lastEnchantment = Enchantment::getEnchantmentByName($part);
					}
					++$i;
				}
			}
		}

		return $out;
	}

	/**
	 *
	 * @param Player $player
	 * @param array $items
	 * @param array $armor
	 *
	 */
	public function setRawKit(Player $player, array $items, array $armor){
		$player->getInventory()->setContents($items);
		$player->getArmorInventory()->setContents($armor);
	}

	/**
	 *
	 * @param array $effects
	 *
	 * @return Effect[]
	 *
	 */
	public function parseEffects(array $effects){
		$out = [];

		foreach($effects as $effect){
			if($effect instanceof Effect){
				$out[] = $effect;
			}else{
				$parts = explode(":", $effect);

				$effect = new EffectInstance(Effect::getEffectByName($parts[0]));
				if($effect !== null){
					$out[] = $effect->setAmplifier(intval($parts[1]))->setDuration(intval($parts[2]) * 20);
				}
			}
		}

		return $out;
	}

	/**
	 *
	 * @param array $commands
	 *
	 */
	public function sendCommands(array $commands){
		foreach($commands as $command){
			if(is_string($command)){
				$this->core->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);
			}
		}
	}

	/**
	 *
	 * @param Player $player
	 *
	 * @return bool
	 *
	 * @throws \InvalidStateException
	 *
	 */
	public function getKitsWindow(Player $player){

		$displayItems = [];

		foreach($this->kits->getAll(true) as $kit){
			if($player->hasPermission("kit." . strtolower($kit))){
				$displayItems[] = $this->getKitDisplayItem($player, $kit);
			}
		}

		if(count($displayItems) > 0){

			$player->addWindow($this->getWindow($displayItems, $player));

			return true;
		}

		return false;
	}

	/**
	 *
	 * @param Player $player
	 * @param string $kitName
	 *
	 * @return null|Item
	 *
	 */
	public function getKitDisplayItem(Player $player, string $kitName){
		$kit = $this->kits->get($kitName);
		if($kit !== false){

			$coolDown = $this->core->PrestigeSocietyKits->getCoolDown($player, $kitName);

			if($coolDown > 0){

				$coolDown = exc::secondsToDHMS($coolDown);

				foreach($kit['cooldown_description'] as &$desc){
					$desc = RandomUtils::colorMessage($desc);
					$desc = str_replace(["@days", "@hours", "@minutes", "@seconds"], [$coolDown[0], $coolDown[1], $coolDown[2], $coolDown[3]], $desc);
				}

				$item = $this->parseItemsWithEnchants([$kit['display_item']])[0]->setLore($kit['cooldown_description']);
				$item->setCustomBlockData(new CompoundTag("", [new StringTag("kit_name", $kitName)]));

				return $item;

			}else{

				foreach($kit['description'] as &$desc){
					$desc = RandomUtils::colorMessage($desc);
				}

				$item = $this->parseItemsWithEnchants([$kit['display_item']])[0]->setLore($kit['description']);
				$item->setCustomBlockData(new CompoundTag("", [new StringTag("kit_name", $kitName)]));

				return $item;
			}
		}

		return null;
	}

	/**
	 *
	 * @param array $items
	 * @param Player $p
	 *
	 * @return null|ChestInventory
	 *
	 */
	public function getWindow(array $items, Player $p){

		$this->queue[$p->getXuid()]['old'] = $p->level->getBlock($p);

		$block = Block::get(Block::CHEST);
		$block->setComponents((int)$p->x, (int)$p->y, (int)$p->z);
		$p->level->sendBlocks([$p], [$block]);

		$nbt = new CompoundTag("", [
			new ListTag("Items", []),
			new StringTag("id", Tile::$tileCount++),
			new IntTag("x", $p->x),
			new IntTag("y", $p->y),
			new IntTag("z", $p->z),
		]);

		$tile = Tile::createTile(Tile::CHEST, $p->level, $nbt);

		$nbt->Items->setTagType(NBT::TAG_Compound);

		if($tile instanceof Chest){
			$eChest = new ChestInventory($tile);
			$eChest->setContents($items);
			$eChest->setSize(36);

			$this->queue[$p->getXuid()]['chest'] = $eChest;

			return $eChest;
		}

		return null;
	}

	/**
	 *
	 * @param Player $player
	 * @param string $kit
	 *
	 */
	public function setKit(Player $player, string $kit){
		//todo
	}

}