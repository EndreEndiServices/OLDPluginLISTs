<?php

namespace PrestigeSociety\Core\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class RepairCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $plugin;

	/**
	 *
	 * RepairCommand constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		parent::__construct('repair', 'repair item in your hand!', '/repair', ['fix']);
		$this->plugin = $c;
	}

	/**
	 *
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 *
	 * @return mixed|void
	 *
	 */
	public function execute(CommandSender $sender, $commandLabel, array $args){
		$items = $this->plugin->getConfig()->getAll()['fixable_items'];
		if($sender instanceof Player){

			if($sender->hasPermission("repair.free")){
				if(isset($items[$sender->getInventory()->getItemInHand()->getId()])){
					$item = $sender->getInventory()->getItemInHand();
					$slot = $sender->getInventory()->getHeldItemIndex();
					$item->setDamage(0);
					$item_name = $sender->getInventory()->getItemInHand()->getName();
					$sender->getInventory()->setItem($slot, $item);
					$sender->sendMessage(RandomUtils::colorMessage(
						str_replace(['@item_name'], [$item_name], $this->plugin->getMessage('item_repair', 'repaired_item_vip'))));
				}else{
					$sender->sendMessage(RandomUtils::colorMessage($this->plugin->getMessage('item_repair', 'cannot_repair_item')));
				}

				return;
			}else{
				$coins = $this->plugin->getPrestigeSocietyEconomy()->getMoney($sender);
				if($coins >= 20000){
					$this->plugin->getPrestigeSocietyEconomy()->subtractMoney($sender, 20000);
					$item = $sender->getInventory()->getItemInHand();
					$slot = $sender->getInventory()->getHeldItemIndex();
					$item->setDamage(0);
					$sender->getInventory()->setItem($slot, $item);
				}else{
					$message = "&6[!] &cYou don't have enought silver coins.";
					$message = RandomUtils::colorMessage($message);
					$sender->sendMessage($message);

					return;
				}
			}
		}
	}


	/**
	 *
	 * @return PrestigeSocietyCore
	 *
	 */
	public function getPlugin(): Plugin{
		return $this->plugin;
	}
}