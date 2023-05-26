<?php

namespace SarchCore\Bounties;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\Player;
use SarchCore\SarchCore;
use onebone\economyapi\EconomyAPI;

class BountyManager implements Listener{

  private $plugin, $bounties;

  public function __construct(SarchCore $plugin) {
    $this->plugin = $plugin;
    $this->bounties = (new Config($this->plugin->getDataFolder() . "/bounties.json", Config::JSON))->getAll();
  }
  public function getPrice(Player $player) {
  	return isset($this->bounties[$player->getName()]) ? $this->bounties[$player->getName()] : 0;
  }
  public function addBounty(Player $player, Int $price) {
  	isset($this->bounties[$player->getName()]) ? $this->bounties[$player->getName()] = $this->bounties[$player->getName()] + $price : $this->bounties[$player->getName()] = $price;
  }
  public function getbounties() {
  	return $this->bounties;
  }
  public function onDeath(PlayerDeathEvent $ev) {
    if(!$ev->getPlayer()->getLastDamageCause() instanceof EntityDamageByEntityEvent) {
  		return;
  	}
  	if($this->getPrice($ev->getPlayer()) === 0) {
  		return;
  	}
  	$bounty = $this->getPrice($ev->getPlayer());
  	$killer = $ev->getPlayer()->getLastDamageCause()->getDamager();
    $killer->sendMessage(TextFormat::DARK_RED . str_repeat("-", 30) . TextFormat::RESET);
    $killer->sendMessage(TextFormat::RED . TextFormat::BOLD . "You killed a bountied player!" . TextFormat::RESET);
    $killer->sendMessage(TextFormat::GOLD . TextFormat::BOLD . "+ " . TextFormat::RESET . TextFormat::GOLD . "$" . $bounty);
    $killer->sendMessage(TextFormat::DARK_RED . str_repeat("-", 30) . TextFormat::RESET);
  	$this->dispatchCommand(new ConsoleCommandSender(), 'givemoney '. $killer . ' ' . $bounty);
  	unset($this->bounties[$ev->getPlayer()->getName()]);
  	return;
  }

  public function save() {
  	@unlink($this->plugin->getDataFolder() . "/bounties.json");
  	(new Config($this->plugin->getDataFolder() . "/bounties.json", Config::JSON, $this->bounties))->save();
  }
}
