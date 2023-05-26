<?php

namespace SarchCore\Cheat;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use SarchCore\SarchCore;

class CheatManager implements Listener {

	private $plugin, $cheat, $words;

	public function __construct(SarchCore $plugin) {
		$this->plugin = $plugin;
		$this->cheat = [];
		$this->words = ["anal", "anus", "ass", "bastard", "bitch", "boob", "cock", "cum", "cunt", "dick", "dildo", "dyke", "fag", "faggot", "fuck", "fuk", "handjob", "homo", "jizz", "cunt", "kike", "kunt", "muff", "nigger", "penis", "piss", "poop", "pussy", "queer", "rape", "semen", "sex", "shit", "slut", "titties", "twat", "vagina", "vulva", "wank", ".leet.cc", ".net", ".com", ".us", ".co", ".co.uk", ".tk", ".ddns.net", ".cf", "hybridpe", "cosmicpe", "astoria", "FUCK", "BITCH", "FAGGOT", "DICK", "CUNT", "ASS", "COSMIC"];
	}

	public function onDrop(PlayerDropItemEvent $ev) {
		if($ev->getPlayer()->getGamemode() !== Player::SURVIVAL) {
			$ev->setCancelled();
			$ev->getPlayer()->sendMessage(TextFormat::RED . "[No-Cheat] You cannot drop items in creative mode");
			$ev->getPlayer()->setGamemode(Player::SURVIVAL);
			return;
		}
	}

	public function onChat(PlayerChatEvent $ev) {
		$msg = $ev->getMessage();
		$p = $ev->getPlayer();
		foreach($this->words as $word) {
			if(strpos($msg, $word) !== false) {
				$p->sendMessage(TextFormat::RED . "[No-Cheat] Unable to send messages with profanity and/or advertising.");
				$ev->setCancelled();
				return;
			}
		}
	}
}