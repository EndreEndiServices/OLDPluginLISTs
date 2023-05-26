<?php

namespace PrestigeSociety\Signs;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class SignsListener implements Listener {

	/**
	 * @priority LOWEST
	 *
	 * @param SignChangeEvent $e
	 *
	 * @throws \InvalidStateException
	 *
	 */
	public function onSignPlace(SignChangeEvent $e){
		$ln = $e->getLines();
		$tl = $e->getBlock()->getLevel()->getTile($e->getBlock());

		if(strtolower(TextFormat::clean($ln[0])) === "world" or strtolower(TextFormat::clean($ln[0])) === "[world]" and $tl instanceof Sign and isset($ln[1])
			and $e->getPlayer()->hasPermission("signs.create.world")){

			if(empty($ln[1]) or !isset($ln[1])){
				return;
			}

			$lines = PrestigeSocietyCore::getInstance()->getConfig()->getAll()["signs"]["world_sign"];
			if(!PrestigeSocietyCore::getInstance()->getServer()->isLevelLoaded($ln[1])){
				PrestigeSocietyCore::getInstance()->getServer()->loadLevel($ln[1]);
				$e->getPlayer()->sendMessage(RandomUtils::colorMessage(PrestigeSocietyCore::getInstance()->getMessage("signs", "not_l_or_e_level")));
			}else{
				$l2 = str_replace("@world", TextFormat::clean($ln[1]), $lines[1]);
				$l2 = RandomUtils::colorMessage($l2);
				$e->setLine(0, RandomUtils::colorMessage($lines[0]));
				$e->setLine(1, $l2);
				$e->setLine(2, RandomUtils::colorMessage($lines[2]));
				$e->setLine(3, RandomUtils::colorMessage($lines[3]));
				$e->getPlayer()->sendMessage(RandomUtils::colorMessage(PrestigeSocietyCore::getInstance()->getMessage("signs", "created_world_sign")));
			}
		}

		if(strtolower(TextFormat::clean($ln[0])) === "garbage" or
			strtolower(TextFormat::clean($ln[0])) === "[garbage]" or
			strtolower(TextFormat::clean($ln[0])) === "gb" or
			strtolower(TextFormat::clean($ln[0])) === "[gb]"
			and $e->getPlayer()->hasPermission("signs.create.garbage")){
			$lines = PrestigeSocietyCore::getInstance()->getConfig()->getAll()["signs"]["garbage_sign"];
			$e->setLine(0, RandomUtils::colorMessage($lines[0]));
			$e->setLine(1, RandomUtils::colorMessage($lines[1]));
			$e->setLine(2, RandomUtils::colorMessage($lines[2]));
			$e->setLine(3, RandomUtils::colorMessage($lines[3]));
			$e->getPlayer()->sendMessage(RandomUtils::colorMessage(PrestigeSocietyCore::getInstance()->getMessage("signs", "created_garbage_sign")));
		}
	}

	/**
	 *
	 * @piority LOWEST
	 *
	 * @param PlayerInteractEvent $e
	 *
	 */
	public function onInteract(PlayerInteractEvent $e){
		$tl = $e->getBlock()->getLevel()->getTile($e->getBlock());

		if($tl instanceof Sign){

			$world = $tl->getText()[1];
			$world = TextFormat::clean($world, true);
			$world = strtolower($world);
			$lvl = $e->getPlayer()->getLevel();
			$x = $lvl->getSpawnLocation()->getX();
			$y = $lvl->getSpawnLocation()->getY();
			$z = $lvl->getSpawnLocation()->getZ();
			switch($world){
				case "world":
					$lvl = PrestigeSocietyCore::getInstance()->getServer()->getLevelByName("world");
					$x = -128;
					$y = 68;
					$z = 431;
					break;
				case "work":
					$lvl = PrestigeSocietyCore::getInstance()->getServer()->getLevelByName("work");
					$x = $lvl->getSpawnLocation()->getX();
					$y = $lvl->getSpawnLocation()->getY();
					$z = $lvl->getSpawnLocation()->getZ();
					break;
				default:
					return;
			}
			$e->getPlayer()->teleport(new Position($x, $y, $z, $lvl));

			if(PrestigeSocietyCore::getInstance()->PrestigeSocietySigns->isGarbageSign($tl)){
				$inv = $e->getPlayer()->getInventory();
				if(!$inv instanceof PlayerInventory) return;
				if($inv->getItemInHand()->getId() == 0) return;
				$inv->setItemInHand(Item::get(0, 0, 1));
			}
		}
	}
}