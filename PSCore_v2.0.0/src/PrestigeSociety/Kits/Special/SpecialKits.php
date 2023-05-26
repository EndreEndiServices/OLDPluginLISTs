<?php

namespace PrestigeSociety\Kits\Special;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\utils\Config;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\exc;
use PrestigeSociety\Core\Utils\RandomUtils;
use PrestigeSociety\Kits\Special\Kit\Acrobat;
use PrestigeSociety\Kits\Special\Kit\Araneidae;
use PrestigeSociety\Kits\Special\Kit\Bartender;
use PrestigeSociety\Kits\Special\Kit\Berserker;
use PrestigeSociety\Kits\Special\Kit\Iceman;
use PrestigeSociety\Kits\Special\Kit\Magneto;
use PrestigeSociety\Kits\Special\Kit\Scorpio;
use PrestigeSociety\Kits\Special\Kit\Swapper;
use PrestigeSociety\Kits\Special\Kit\Thor;

class SpecialKits {


	/**
	 * @var KitManager
	 */
	public $kitManager;
	/**
	 * @var Vault
	 */
	public $vault;
	/** @var PrestigeSocietyCore */
	protected $core;
	/** @var Config */
	protected $kits;

	public function __construct(PrestigeSocietyCore $core){
		$this->core = $core;
		$this->kits = new Config($core->getDataFolder() . "special_kits.yml", Config::YAML, [
			'Acrobat'   => [
				'ability_item'    => '288:0:1:&bAcrobat &a| Click with this to use ability',
				'cooldown'        => '10s',
				'deactivate_time' => '-1s',
				'helmet'          => '298:0:1',
				'chest'           => '299:0:1:default:protection:1',
				'legs'            => '300:0:1',
				'boots'           => '301:0:1',
				'items'           => [
					"268:0:1:default:sharpness:1",
				],
				'commands'        => [
					"tell @player &5You equipped the @kit (level @level) kit.",
				],
				'effects'         => [
					"strength:1:30",
				],
			],
			'Araneidae' => [
				'ability_item'    => '375:0:1:&bAraneidae &a| Right click with this to use ability',
				'cooldown'        => '15s',
				'deactivate_time' => '10s',
				'helmet'          => '298:0:1',
				'chest'           => '299:0:1:default:protection:1',
				'legs'            => '300:0:1',
				'boots'           => '301:0:1',
				'items'           => [
					"268:0:1:default:sharpness:1",
				],
				'commands'        => [
					"tell @player &5You equipped the @kit (level @level) kit.",
				],
				'effects'         => [
					"strength:1:30",
				],
			],
			'Bartender' => [
				'ability_item'    => '437:0:1:&bBartender &a| Hit a player with this to use ability',
				'cooldown'        => '10s',
				'deactivate_time' => '-1s',
				'helmet'          => '298:0:1',
				'chest'           => '299:0:1:default:protection:1',
				'legs'            => '300:0:1',
				'boots'           => '301:0:1',
				'items'           => [
					"268:0:1:default:sharpness:1",
				],
				'commands'        => [
					"tell @player &5You equipped the @kit (level @level) kit.",
				],
				'effects'         => [
					"strength:1:30",
				],
			],
			'Berserker' => [
				'ability_item'    => '286:0:1:&bBerserker &a| Right click this to use ability',
				'cooldown'        => '15s',
				'deactivate_time' => '10s',
				'helmet'          => '298:0:1',
				'chest'           => '299:0:1:default:protection:1',
				'legs'            => '300:0:1',
				'boots'           => '301:0:1',
				'items'           => [
					"268:0:1:default:sharpness:1",
				],
				'commands'        => [
					"tell @player &5You equipped the @kit (level @level) kit.",
				],
				'effects'         => [
					"strength:1:30",
				],
			],
			'Iceman'    => [
				'ability_item'    => '79:0:1:&bIceman &a| Walk on water to activate ability',
				'cooldown'        => '15s',
				'deactivate_time' => '10s',
				'helmet'          => '298:0:1',
				'chest'           => '299:0:1:default:protection:1',
				'legs'            => '300:0:1',
				'boots'           => '301:0:1',
				'items'           => [
					"268:0:1:default:sharpness:1",
				],
				'commands'        => [
					"tell @player &5You equipped the @kit (level @level) kit.",
				],
				'effects'         => [
					"strength:1:30",
				],
			],
			'Magneto'   => [
				'ability_item'    => '318:0:1:&eMagneto &a| Right click this to use ability',
				'cooldown'        => '15s',
				'deactivate_time' => '5s',
				'helmet'          => '298:0:1',
				'chest'           => '299:0:1:default:protection:1',
				'legs'            => '300:0:1',
				'boots'           => '301:0:1',
				'items'           => [
					"268:0:1:default:sharpness:1",
				],
				'commands'        => [
					"tell @player &5You equipped the @kit (level @level) kit.",
				],
				'effects'         => [
					"strength:1:30",
				],
			],
			'Scorpio'   => [
				'ability_item'    => '399:0:1:&eScorpio &a| Right click this to use ability',
				'cooldown'        => '15s',
				'deactivate_time' => '5s',
				'helmet'          => '298:0:1',
				'chest'           => '299:0:1:default:protection:1',
				'legs'            => '300:0:1',
				'boots'           => '301:0:1',
				'items'           => [
					"268:0:1:default:sharpness:1",
				],
				'commands'        => [
					"tell @player &5You equipped the @kit (level @level) kit.",
				],
				'effects'         => [
					"strength:1:30",
				],
			],
			'Swapper'   => [
				'ability_item'    => '501:0:1:&bSwapper &a| Right click on a player to use ability',
				'cooldown'        => '15s',
				'deactivate_time' => '-1s',
				'helmet'          => '298:0:1',
				'chest'           => '299:0:1:default:protection:1',
				'legs'            => '300:0:1',
				'boots'           => '301:0:1',
				'items'           => [
					"268:0:1:default:sharpness:1",
				],
				'commands'        => [
					"tell @player &5You equipped the @kit (level @level) kit.",
				],
				'effects'         => [
					"strength:1:30",
				],
			],
			'Thor'      => [
				'ability_item'    => '369:0:1:&eThor &a| Right click this to use ability',
				'cooldown'        => '15s',
				'deactivate_time' => '10s',
				'helmet'          => '298:0:1',
				'chest'           => '299:0:1:default:protection:1',
				'legs'            => '300:0:1',
				'boots'           => '301:0:1',
				'items'           => [
					"268:0:1:default:sharpness:1",
				],
				'commands'        => [
					"tell @player &5You equipped the @kit (level @level) kit.",
				],
				'effects'         => [
					"strength:1:30",
				],
			],
		]);
		$this->kitManager = new KitManager($this);
		$this->vault = new Vault();
		$this->registerKits();
	}

	public function registerKits(){
		$kits = [];

		$kit = $this->parseKitData('Acrobat');

		if($kit !== null){
			$kits[] = new Acrobat($kit[0], $kit[1], $kit[2], $kit[3]);
		}

		$kit = $this->parseKitData('Araneidae');

		if($kit !== null){
			$kits[] = new Araneidae($kit[0], $kit[1], $kit[2], $kit[3]);
		}

		$kit = $this->parseKitData('Bartender');

		if($kit !== null){
			$kits[] = new Bartender($kit[0], $kit[1], $kit[2], $kit[3]);
		}

		$kit = $this->parseKitData('Berserker');

		if($kit !== null){
			$kits[] = new Berserker($kit[0], $kit[1], $kit[2], $kit[3]);
		}

		$kit = $this->parseKitData('Iceman');

		if($kit !== null){
			$kits[] = new Iceman($kit[0], $kit[1], $kit[2], $kit[3]);
		}

		$kit = $this->parseKitData('Magneto');

		if($kit !== null){
			$kits[] = new Magneto($kit[0], $kit[1], $kit[2], $kit[3]);
		}


		$kit = $this->parseKitData('Scorpio');

		if($kit !== null){
			$kits[] = new Scorpio($kit[0], $kit[1], $kit[2], $kit[3]);
		}

		$kit = $this->parseKitData('Swapper');

		if($kit !== null){
			$kits[] = new Swapper($kit[0], $kit[1], $kit[2], $kit[3]);
		}

		$kit = $this->parseKitData('Thor');

		if($kit !== null){
			$kits[] = new Thor($kit[0], $kit[1], $kit[2], $kit[3]);
		}

		$this->getKitManager()->registerKits($kits);
	}

	/**
	 *
	 * @param string $kitName
	 *
	 * @return array
	 *
	 */
	public function parseKitData(string $kitName){
		$kit = $this->kits->get($kitName);

		if($kit !== null){

			$items = array_merge($this->parseItemsWithEnchants([$kit['helmet'], $kit['chest'], $kit['legs'], $kit['boots']]), $this->parseItemsWithEnchants($kit['items']));
			/** @var \DateTime $coolDown */
			$coolDown = exc::stringToTimestamp($kit['cooldown'])[0];
			$coolDown = ($coolDown->getTimestamp() - time());

			/** @var \DateTime $deactivate */
			$deactivate = exc::stringToTimestamp($kit['deactivate_time'])[0];
			$deactivate = ($deactivate->getTimestamp() - time());

			$special = $this->parseItemsWithEnchants([$kit['ability_item']])[0];
			$special->setCustomBlockData(new CompoundTag("", [new StringTag("kit_name", $kitName)]));

			return [$special, $items, $coolDown, $deactivate];

		}

		return null;

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
	 * @return KitManager
	 *
	 */
	public function getKitManager(): KitManager{
		return $this->kitManager;
	}

	/**
	 *
	 * @return PrestigeSocietyCore
	 *
	 */
	public function getCore(): PrestigeSocietyCore{
		return $this->core;
	}

	/**
	 *
	 * @return Vault
	 *
	 */
	public function getVault(): Vault{
		return $this->vault;
	}

	/**
	 *
	 * @param Player $player
	 * @param string $kitName
	 *
	 * @return array
	 *
	 */
	public function runKitCommands(Player $player, string $kitName){
		$kit = $this->kits->get($kitName);

		if($kit !== null){

			foreach($kit['commands'] as &$command){
				$command = str_replace(["@player", "@kit", "@level"],
					[$player->getName(), $kitName, KitLevels::KIT_LEVELS[$kitName]], $command);
				$command = RandomUtils::colorMessage($command);
			}

			$this->sendCommands($kit['commands']);

		}

		return null;

	}

	/**
	 *
	 * @param array $commands
	 *
	 */
	public function sendCommands(array $commands){
		foreach($commands as $command){
			$this->core->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);
		}
	}

	/**
	 *
	 * @param Player $player
	 * @param string $kitName
	 *
	 * @return array
	 *
	 */
	public function setKitEffects(Player $player, string $kitName){
		$kit = $this->kits->get($kitName);

		if($kit !== null){

			$effects = $this->parseEffects($kit['effects']);

			foreach($effects as $effect){
				$player->addEffect(new EffectInstance($effect));
			}

		}

		return null;

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
}