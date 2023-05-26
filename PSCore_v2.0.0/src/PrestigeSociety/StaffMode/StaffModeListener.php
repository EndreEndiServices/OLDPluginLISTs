<?php

namespace PrestigeSociety\StaffMode;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\inventory\ChestInventory;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\tile\Chest;
use pocketmine\tile\Tile;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class StaffModeListener implements Listener {
	/** @var PrestigeSocietyCore */
	private $plugin;

	/**
	 *
	 * PrestigeSocietyStaffMode constructor.
	 *
	 * @param PrestigeSocietyCore $core
	 *
	 */
	public function __construct(PrestigeSocietyCore $core){
		$this->plugin = $core;
	}

	/**
	 *
	 * @param PlayerDropItemEvent $event
	 *
	 */
	public function dropItem(PlayerDropItemEvent $event){
		if($this->plugin->PrestigeSocietyStaffMode->isInStaffMode($event->getPlayer())){
			$messagePlayer = $this->plugin->getMessage('staff_mode', 'cannot_drop_items');
			$event->getPlayer()->sendMessage(RandomUtils::colorMessage($messagePlayer));
			$event->setCancelled();
		}
	}

	/**
	 *
	 * @param PlayerCommandPreprocessEvent $event
	 *
	 */
	public function playerCMD(PlayerCommandPreprocessEvent $event){

		if($this->plugin->PrestigeSocietyStaffMode->isPlayerFrozen($event->getPlayer()) && $event->getMessage()[0] === '/'){

			$messagePlayer = $this->plugin->getMessage('staff_mode', 'no_commands');

			$event->getPlayer()->sendMessage(RandomUtils::colorMessage($messagePlayer));
			$event->setCancelled();
		}
	}

	/**
	 *
	 * @param BlockBreakEvent $event
	 *
	 */
	public function onBreak(BlockBreakEvent $event){
		if($this->plugin->PrestigeSocietyStaffMode->isInStaffMode($event->getPlayer())){
			$messagePlayer = $this->plugin->getMessage('staff_mode', 'cannot_break_blocks');
			$event->getPlayer()->sendMessage(RandomUtils::colorMessage($messagePlayer));
			$event->setCancelled();
		}
	}

	/**
	 *
	 * @param BlockPlaceEvent $event
	 *
	 */
	public function onPlace(BlockPlaceEvent $event){

		if($this->plugin->PrestigeSocietyStaffMode->isInStaffMode($event->getPlayer())){
			$messagePlayer = $this->plugin->getMessage('staff_mode', 'cannot_place_blocks');
			$event->getPlayer()->sendMessage(RandomUtils::colorMessage($messagePlayer));
			$event->setCancelled();
		}
	}

	/**
	 *
	 * @param InventoryTransactionEvent $event
	 *
	 */
	public function transaction(InventoryTransactionEvent $event){
		$player = $event->getTransaction()->getSource();
		if($this->plugin->PrestigeSocietyStaffMode->isInStaffMode($player)){

			$nbt = new CompoundTag("", [
				new ListTag("Items", []),
				new StringTag("id", Tile::$tileCount++),
				new IntTag("x", $player->x),
				new IntTag("y", $player->y),
				new IntTag("z", $player->z),
			]);

			$tile = Tile::createTile(Tile::CHEST, $player->level, $nbt);

			if($tile instanceof Chest){
				$eChest = new ChestInventory($tile);
				$player->addWindow($eChest);
				$player->removeWindow($eChest);
			}

			$messagePlayer = $this->plugin->getMessage('staff_mode', 'cannot_change_items');
			$player->sendMessage(RandomUtils::colorMessage($messagePlayer));
			$event->setCancelled();
		}
	}

	/**
	 *
	 * @param PlayerInteractEvent $e
	 *
	 */
	public function playerInteract(PlayerInteractEvent $e){
		$player = $e->getPlayer();

		if($this->plugin->PrestigeSocietyStaffMode->isInStaffMode($player)){
			$item = $player->getInventory()->getItemInHand();
			$config = $this->plugin->getConfig()->getAll()['staff_mode'];

			if($item->getId() === (int)$config['first_item_id'] && $item->getDamage() === (int)$config['first_item_meta']){
				$player->setDataFlag(Player::DATA_FLAGS, Player::DATA_FLAG_INVISIBLE, true);
				$player->getInventory()->setItem(0, Item::get($config['first_item_id_2'], $config['first_item_meta_2'], 1)->setCustomName(RandomUtils::colorMessage($config['first_item_name_2'])));
				$messagePlayer = $this->plugin->getMessage('staff_mode', 'invisible_enabled');
				$player->sendMessage(RandomUtils::colorMessage($messagePlayer));
			}elseif($item->getId() === (int)$config['first_item_id_2'] && $item->getDamage() === (int)$config['first_item_meta_2']){
				$player->setDataFlag(Player::DATA_FLAGS, Player::DATA_FLAG_INVISIBLE, false);
				$player->getInventory()->setItem(0, Item::get($config['first_item_id'], $config['first_item_meta'], 1)->setCustomName(RandomUtils::colorMessage($config['first_item_name'])));
				$messagePlayer = $this->plugin->getMessage('staff_mode', 'invisible_disabled');
				$player->sendMessage(RandomUtils::colorMessage($messagePlayer));
			}elseif($item->getId() === (int)$config['third_item_id'] && $item->getDamage() === (int)$config['third_item_meta']){
				$players = $this->plugin->getServer()->getOnlinePlayers();
				if(count($players) <= 1){
					$messagePlayer = $this->plugin->getMessage('staff_mode', 'server_empty');
					$player->sendMessage(RandomUtils::colorMessage($messagePlayer));

					return;
				}
				$random = $players[array_rand($players)];
				while($random === $player){
					$random = $players[array_rand($players)];
				}
				$player->teleport($random);
				$messagePlayer = $this->plugin->getMessage('staff_mode', 'random_tp');
				$messagePlayer = str_replace('@player', $random->getName(), $messagePlayer);
				$player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_TELEPORT);
				$player->sendMessage(RandomUtils::colorMessage($messagePlayer));
			}elseif($item->getId() === (int)$config['fourth_item_id'] && $item->getDamage() === (int)$config['fourth_item_meta']){
				$motion = $player->getDirectionVector();
				$motion->multiply(3);
				$motion->y = 1.0;
				$player->setMotion($motion);
				$player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_BOW);
			}
		}
	}

	/**
	 * @param EntityDamageEvent $e
	 *
	 */
	public function entityDamage(EntityDamageEvent $e){
		$cause = $e->getEntity()->getLastDamageCause();

		if($cause instanceof EntityDamageByEntityEvent){

			$player = $e->getEntity();
			$damager = $cause->getDamager();

			if($damager instanceof Player && $player instanceof Player){

				if($this->plugin->PrestigeSocietyStaffMode->isInStaffMode($damager)){


					$item = $damager->getInventory()->getItemInHand();

					$config = $this->plugin->getConfig()->getAll()['staff_mode'];

					if($item->getId() === (int)$config['second_item_id'] && $item->getDamage() === (int)$config['second_item_meta']){

						if($this->plugin->PrestigeSocietyStaffMode->isPlayerFrozen($player)){

							$this->plugin->PrestigeSocietyStaffMode->unfreezePlayer($player);
							$player->setImmobile(false);

							$messageDamanger = $this->plugin->getMessage('staff_mode', 'unfrozen_staff');
							$messagePlayer = $this->plugin->getMessage('staff_mode', 'unfrozen_player');

							$messageDamanger = str_replace('@player', $player->getName(), $messageDamanger);
							$messagePlayer = str_replace('@staff', $damager->getName(), $messagePlayer);

							$player->sendMessage(RandomUtils::colorMessage($messagePlayer));
							$damager->sendMessage(RandomUtils::colorMessage($messageDamanger));

							$e->setCancelled();

						}else{

							$player->setImmobile(true);
							$this->plugin->PrestigeSocietyStaffMode->freezePlayer($player);
							$messageDamanger = $this->plugin->getMessage('staff_mode', 'frozen_staff');
							$messagePlayer = $this->plugin->getMessage('staff_mode', 'frozen_player');

							$messageDamanger = str_replace('@player', $player->getName(), $messageDamanger);
							$messagePlayer = str_replace('@staff', $damager->getName(), $messagePlayer);

							$player->sendMessage(RandomUtils::colorMessage($messagePlayer));
							$damager->sendMessage(RandomUtils::colorMessage($messageDamanger));

							$e->setCancelled();

						}


					}else{
						if(!$e->isCancelled()){
							$messagePlayer = $this->plugin->getMessage('staff_mode', 'cannot_damage');
							$damager->sendMessage(RandomUtils::colorMessage($messagePlayer));
							$e->setCancelled();
						}
					}
				}
			}

		}
	}
}