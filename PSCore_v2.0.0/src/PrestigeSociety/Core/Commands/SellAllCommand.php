<?php

namespace PrestigeSociety\Core\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class SellAllCommand extends Command implements PluginIdentifiableCommand {


	/** @var array */
	protected $config = [];
	/** @var PrestigeSocietyCore */
	private $plugin;

	/**
	 *
	 * EatCommand constructor.
	 *
	 * @param PrestigeSocietyCore $plugin
	 *
	 */
	public function __construct(PrestigeSocietyCore $plugin){
		parent::__construct("sellall", "Sell your items!", RandomUtils::colorMessage("&e/sellall"), ["sellall"]);
		$this->plugin = $plugin;
		$this->config = (new Config($plugin->getDataFolder() . "sell_prices.yml", Config::YAML, [
			"1:0" => 3000,
		]))->getAll();
	}

	/**
	 *
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 *
	 * @return mixed
	 *
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if($sender instanceof Player){

			$hand = $sender->getInventory()->getItemInHand();

			if($hand->getId() !== Item::AIR){

				$price = $this->getPriceFor($sender, $hand);

				if($price === 0){
					$message = $this->plugin->getMessage('sell_all', 'cannot_sell_item');
					$sender->sendMessage(RandomUtils::colorMessage($message));

					return;
				}

				$count = $this->getCountOfItem($sender, $hand);

				$sender->getInventory()->remove($hand);

				$this->plugin->PrestigeSocietyEconomy->addMoney($sender, $price);

				$message = $this->plugin->getMessage('sell_all', 'sold_item');
				$message = str_replace(["@item", "@count", "@price"], [$hand->getName(), $count, $price], $message);
				$sender->sendMessage(RandomUtils::colorMessage($message));

				return;

			}

			$message = $this->plugin->getMessage('sell_all', 'cannot_sell_air');
			$sender->sendMessage(RandomUtils::colorMessage($message));

		}

		return;
	}

	/**
	 *
	 * @param Player $player
	 * @param Item $item
	 *
	 * @return float|int
	 *
	 */
	private function getPriceFor(Player $player, Item $item){
		$index = $item->getId() . ":" . $item->getDamage();
		if(isset($this->config[$index])){
			$count = $this->getCountOfItem($player, $item);
			$price = $this->config[$index];

			return intval($price) * $count;
		}

		return 0;
	}

	/**
	 *
	 * @param Player $player
	 * @param Item $item
	 *
	 * @return int
	 *
	 */
	private function getCountOfItem(Player $player, Item $item){
		$out = 0;
		foreach($player->getInventory()->getContents() as $content){
			if($content->equals($item, true, false)){
				$out += $content->getCount();
			}
		}

		return $out;
	}

	/**
	 *
	 * @return Plugin
	 *
	 */
	public function getPlugin(): Plugin{
		return $this->plugin;
	}

}