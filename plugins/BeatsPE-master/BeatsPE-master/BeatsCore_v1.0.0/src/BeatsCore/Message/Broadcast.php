<?php
namespace BeatsCore\Message;

use BeatsCore\Core;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\scheduler\PluginTask;

class Broadcast extends PluginTask {

  public $allBroadcast = array();
  
  public function __construct($plugin){
    $this->plugin = $plugin;
    parent::__construct($plugin);
  }
  
  public function onRun(int $currentTick) {
    $allBroadcast = [
      "\n§l§5 » §r§7Follow our server Twitter to stay updated! §dTwitter: @BeatsNetworkPE\n\n",
      "\n§l§5 » §r§7Ranks and others are purchaseable at §dBeatsNetworkPE.buycraft.net\n\n",
      "\n§l§5 » §r§7Vote for our server to get awesome rewards! when your done, type §b/vote §7Vote at: §dhttps://is.gd/VoteBeatsPE\n\n",
      "\n§l§5 » §r§7To view all factions commands, type §d/f help\n\n",
      "\n§l§5 » §r§7Subscribe §bJTJamez §7on §cYou§rTube§7!\n\n",
      "\n§l§5 » §r§7Please invite your friends to play on our server!\n\n",
      "\n§l§5 » §r§7Join our Discord server! §dDiscord: https://discord.gg/rgJ6fKY\n\n"
    ];
    $broadcast = array_rand($allBroadcast);
    $this->plugin->getServer()->broadcastMessage($allBroadcast[$broadcast]);
  }
}