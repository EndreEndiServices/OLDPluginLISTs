<?php

namespace PrestigeSociety\Kits;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\inventory\PlayerInventory;
use pocketmine\level\sound\DoorSound;
use pocketmine\Player;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;
use PrestigeSociety\Kits\Special\Kit\Acrobat;
use PrestigeSociety\Kits\Special\Kit\Berserker;
use PrestigeSociety\Kits\Special\Kit\Kit;

class KitListener implements Listener {

	/** @var PrestigeSocietyCore */
	protected $core;

	/**
	 *
	 * KitCommand constructor.
	 *
	 * @param PrestigeSocietyCore $core
	 *
	 */
	public function __construct(PrestigeSocietyCore $core){
		;
		$this->core = $core;;
	}

	/**
	 *
	 * @param InventoryCloseEvent $event
	 *
	 */
	public function close(InventoryCloseEvent $event){
		$p = $event->getPlayer();
		$this->core->PrestigeSocietyKits->getNormalKits()->tryResetQueue($p);
	}

	/**
	 *
	 * @param InventoryTransactionEvent $event
	 *
	 */
	public function Transaction(InventoryTransactionEvent $event){
		$transaction = $event->getTransaction();

		$cancel = false;

		$this->core->PrestigeSocietyKits->callTransaction($transaction, $cancel);

		if($cancel){
			$event->setCancelled();
		}
	}

	/**
	 *
	 * @param InventoryPickupItemEvent $event
	 *
	 */
	public function pickup(InventoryPickupItemEvent $event){
		$inv = $event->getInventory();
		$item = $event->getItem();
		if($inv instanceof PlayerInventory){
			$player = $inv->getHolder();
			$data = $item->namedtag;
			if($data->hasTag("scorpio")){
				$launcher = $data->getString("launcher");
				$thower = $this->core->getServer()->getPlayerExact($launcher);

				if($thower instanceof Player){

					if($player === $thower){
						$event->setCancelled();

						return;
					}

					$player->teleport($thower);
					$player->level->addSound(new DoorSound($player));
				}
				$item->close();
				$event->setCancelled();
			}
		}
	}

	/**
	 *
	 * @param PlayerMoveEvent $e
	 *
	 */
	public function move(PlayerMoveEvent $e){
		$this->core->PrestigeSocietyKits->getSpecialKits()->getKitManager()->callEvent([
			'Player' => $e->getPlayer(),
		], Kit::MOVE_PLAYER_MODE);
	}

	/**
	 *
	 * @param PlayerInteractEvent $e
	 *
	 */
	public function interact(PlayerInteractEvent $e){

		$player = $e->getPlayer();

		if($this->core->PrestigeSocietyKits->getSpecialKits()->getVault()->isKitEnabled($player)){
			if($this->core->PrestigeSocietyLandProtector()->canDamage($player->asVector3(), $player->level)){
				$this->core->PrestigeSocietyKits->getSpecialKits()->getKitManager()->callEvent([
					'Player' => $e->getPlayer(),
					'Item'   => $e->getItem(),
					'Block'  => $e->getBlock(),
				], Kit::ALL_CLICK_MODE);
				if($e->getAction() == PlayerInteractEvent::LEFT_CLICK_AIR
					or $e->getAction() == PlayerInteractEvent::LEFT_CLICK_BLOCK){
					$this->core->PrestigeSocietyKits->getSpecialKits()->getKitManager()->callEvent([
						'Player' => $e->getPlayer(),
						'Item'   => $e->getItem(),
						'Block'  => $e->getBlock(),
					], Kit::LEFT_CLICK_MODE);
				}elseif($e->getAction() == PlayerInteractEvent::RIGHT_CLICK_AIR
					or $e->getAction() == PlayerInteractEvent::RIGHT_CLICK_BLOCK){
					$this->core->PrestigeSocietyKits->getSpecialKits()->getKitManager()->callEvent([
						'Player' => $e->getPlayer(),
						'Item'   => $e->getItem(),
						'Block'  => $e->getBlock(),
					],
						Kit::RIGHT_CLICK_MODE);
				}
			}else{
				$message = $this->core->getMessage("special_kits", "cannot_use_here");
				$player->sendMessage(RandomUtils::colorMessage($message));
			}
		}
	}

	/**
	 *
	 * @param EntityDamageEvent $e
	 *
	 *
	 */
	public function damage(EntityDamageEvent $e){

		$target = $e->getEntity();

		if($target instanceof Player){
			$kit = $this->core->PrestigeSocietyKits->getSpecialKits()->getVault()->getPlayerKit($target);
			if($kit instanceof Acrobat && $kit->isAbilityActive() && $e->getCause() === EntityDamageEvent::CAUSE_FALL){
				$e->setCancelled();
			}
		}

		if($e instanceof EntityDamageByEntityEvent){
			$cause = $e->getDamager();
			if($cause instanceof Player and $target instanceof Player){
				if($this->core->PrestigeSocietyKits->getSpecialKits()->getVault()->isKitEnabled($cause)){
					if($this->core->PrestigeSocietyLandProtector()->canDamage($cause->asVector3(), $cause->level)){
						$this->core->PrestigeSocietyKits->getSpecialKits()->getKitManager()->callEvent([
							'Player' => $cause,
							'Target' => $target,
							'Item'   => $cause->getInventory()->getItemInHand(),
						],
							Kit::HIT_PLAYER_MODE);

						$ability = $this->core->PrestigeSocietyKits->getSpecialKits()->getVault()->getPlayerKit($cause);

						if($ability instanceof Berserker && $ability->isAbilityActive()){
							$e->setBaseDamage($e->getFinalDamage() * 2);
						}
					}else{
						$message = $this->core->getMessage("special_kits", "cannot_use_here");
						$cause->sendMessage(RandomUtils::colorMessage($message));
					}
				}
			}
		}
	}

	/**
	 *
	 * @param PlayerDeathEvent $e
	 *
	 */
	public function death(PlayerDeathEvent $e){
		$player = $e->getPlayer();
		if($this->core->PrestigeSocietyKits->getSpecialKits()->getVault()->isKitEnabled($player)){
			$this->core->PrestigeSocietyKits->getSpecialKits()->getVault()->getPlayerKit($player)->onDeath($player);
			$this->core->PrestigeSocietyKits->getSpecialKits()->getVault()->setKitDisabled($player);
			$e->setDrops([]);
		}
	}

	/**
	 *
	 * @param PlayerQuitEvent $e
	 *
	 */
	public function quit(PlayerQuitEvent $e){
		$player = $e->getPlayer();
		if($this->core->PrestigeSocietyKits->getSpecialKits()->getVault()->isKitEnabled($player)){
			$this->core->PrestigeSocietyKits->getSpecialKits()->getVault()->getPlayerKit($player)->onQuit($player);
		}
	}
}