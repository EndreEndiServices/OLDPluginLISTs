<?php

namespace SarchCore\Message;

use pocketmine\Server;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat as C;
use SarchCore\SarchCore;

class Bcast extends PluginTask {
	
	public function onRun(/*int*/ $currentTick) {
		$input = [
			"§6Tip§7 » VOTE at §3http://bit.ly/2x2QEZj §7to get Crate Key's, Diamond's, and Money.",
			"§6Tip§7 » Use §f/rules §7to get a list of our Server Rules!",
			"§6Tip§7 » Staff abuse? DM or Tweet to us on twitter §f@SourServers §7with proof and We'll look into it for you.",
			"§6Tip§7 » Donations Keep The Server Online! Buy ranks at §3SHOP.TheSarch.NET",
			"§6Info§7 » We are still in BETA. Please report any bugs on Twitter.",
			"§6Tip§7 » Follow us on Twitter §f@SourServers §7to get the Latest Server Updates and events that happen.",
			"§6Info§7 » Need Emergency Contact with the Owner? Sour's Discord: §fSour #5905",
			"§6Tip§7 » Do §f/f help §7to see all Faction commands.",
			"§6Tip§7 » Do §f/f create §7to make a Factions.",
			"§6Tip§7 » Need Help? Try typing §f/help",
			"§6Tip§7 » Thanks for playing on §dSarchonical Semi-HC Factions!!",
			"§6Info§7 » Hacking is NOT allowed on the Sarchon Factions Server, Disable client mods before playing",
			"§6Tip§7 » We know our Server is not perfect, but we are always trying to improve it.",
			"§6Info§7 » You can Donate for a &n&3Donar-Rank and Donar-Perks&r &7by going to &n&3SHOP.TheSarch.NET &r, &7this is to support the servers growth and upkeep",
			"§6Tip§7 » Report hackers and Bugs!",
			"§6Tip§7 » Want to be part of Staff? Make sure you have atleast Month of play time under your Belt!",
			"§6Tip§7 » You can apply for a staff position at thesarch.enjin.com/forum!",
			"§6Tip§7 » Want to Purchase a Rank or Donate? Go to our shop: SHOP.TheSarch.NET",
			"§6Info§7 » Purchases May Take 5+ Minutes to Completley Finalize (Not Often, but Remember to be In-Game For a few Minutes to recieve your Items)",
			"§6Tip§7 » Please DO NOT ask for a staff position if you are new!",
			"§6Info§7 » Donations are needed to keep the Server Up and Running, Help Contribute At: SHOP.TheSarch.NET",
			"§6Info§7 » §f/spawn §7Is Not Active for regular player's! This is on Purpose so DO NOT ask for it to be fixed! You must walk to Spawn! Thats the Point!",
			"§6Info§7 » Remember, The Top Voter of the Month gets a FREE RANK, Vote at §3http://bit.ly/2x2QEZj",
			"§6Tip§7 » Thanks for Playing with Us!"
		];
		$details = array_rand($input);
		Server::getInstance()->broadcastMessage(C::GRAY . $input[$details]);
	}
}