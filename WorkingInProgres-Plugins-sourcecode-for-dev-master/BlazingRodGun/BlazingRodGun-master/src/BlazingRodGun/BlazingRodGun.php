<?php

namespace BlazingRodGun;

use pocketmine\plugin\PluginBase;
use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\UseItemPacket;
use pocketmine\Player;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\EnumTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\item\Item;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\entity\Snowball;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\scheduler\CallbackTask;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\Server;
use pocketmine\entity\Living;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerFishEvent;
use pocketmine\level\sound\BlazeShootSound;
use pocketmine\level\sound\AnvilFallSound;

class BlazingRodGun extends PluginBase implements Listener
{
	public function onEnable()
	{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onPacketReceived(DataPacketReceiveEvent $event)
	{

		if ($event->getPlayer()->getLevel()->getName() == "world") {

			$pk = $event->getPacket();
			$player = $event->getPlayer();
			if ($pk instanceof UseItemPacket and $pk->face === 0xff) {
				$item = $player->getInventory()->getItemInHand();
				if ($item->getId() == 346) {
					foreach ($player->getInventory()->getContents() as $item) {
						if ($item->getID() == 351 && $item->getDamage() == 8 && $item->getCount() > 0) {
							$nbt = new CompoundTag ("", [
								"Pos" => new EnumTag ("Pos", [
									new DoubleTag ("", $player->x),
									new DoubleTag ("", $player->y + $player->getEyeHeight()),
									new DoubleTag ("", $player->z)
								]),
								"Motion" => new EnumTag ("Motion", [
									new DoubleTag ("", -\sin($player->yaw / 180 * M_PI) * \cos($player->pitch / 180 * M_PI)),
									new DoubleTag ("", -\sin($player->pitch / 180 * M_PI)),
									new DoubleTag ("", \cos($player->yaw / 180 * M_PI) * \cos($player->pitch / 180 * M_PI))
								]),
								"Rotation" => new EnumTag ("Rotation", [
									new FloatTag ("", $player->yaw),
									new FloatTag ("", $player->pitch)
								])
							]);

							$f = 1.5;
							$snowball = Entity::createEntity("Snowball", $player->chunk, $nbt, $player);
							$snowball->setMotion($snowball->getMotion()->multiply($f));
							$snowball->spawnToAll();
							$player->getLevel()->addSound(new BlazeShootSound($player), [$player]);
							$player->getInventory()->removeItem(Item::get(351, 8, 1));
						}
					}
				}
			}
		}
	}

	public function onDamage(EntityDamageEvent $event)
	{
		if ($event->getEntity()->getLevel()->getName() == "world") {
			$player = $event->getEntity();
			$entity = $event->getEntity();
			if ($player instanceof Player && $event->getCause() === EntityDamageEvent::CAUSE_PROJECTILE) {
				$event->setDamage(5);
				$event->getEntity()->getLevel()->addSound(new AnvilFallSound($event->getEntity()), $event->getEntity()->getLevel()->getPlayers());
			}
		}
	}

	public function onItemHeld(PlayerItemHeldEvent $ev)
	{
		$player = $ev->getPlayer();
		if ($ev->getPlayer()->getInventory()->getItemInHand()->getId() === 346) {
			$ev->getPlayer()->sendTip(TextFormat::GOLD . "Vote GUN");
		}
		if ($ev->getPlayer()->getInventory()->getItemInHand()->getId() === 351) {
			$ev->getPlayer()->sendTip(TextFormat::RED . "Bullets");
		}
	}

	public function onFish(PlayerFishEvent $event){
        $event->setCancelled(true);
	}
}
