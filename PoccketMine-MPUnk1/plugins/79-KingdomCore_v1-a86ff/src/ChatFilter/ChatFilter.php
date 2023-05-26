<?php

namespace ChatFilter;

use ChatFilter\chat\ChatClasser;
use pocketmine\utils\TextFormat;

class ChatFilter {

	protected $profanityChecker;
	protected $recentMessages = array();
	protected $enableMessageFrequency;
	protected $recentChat = array();

	public function __construct($enableMsgFrequency = true) {
		$this->profanityChecker = new ChatClasser();
		$this->enableMessageFrequency = $enableMsgFrequency;
	}

	public function clearRecentChat() {
 		$this->recentChat = array();
 	}

	public function check($player, $message, $needCheck = true) {
		$checkResult = $this->profanityChecker->check($message);
		$errorMessage = $this->getErrorMessage($message, $player);
		if (!empty($errorMessage)) {
			$player->sendMessage($errorMessage);
			return false;
		}
		if($needCheck){
			if ($this->enableMessageFrequency) {
				$this->recentChat[$player->getID()] = true;
			}
			$this->recentMessages[$player->getID()] = $message;
		}
		return true;
	}

	private function getErrorMessage($message, $player) {
		$errorMsg = '';
		if (strlen($message) === 0) {
			$errorMsg = TextFormat::RED . 'That message is too short.';
		} elseif (isset($this->recentChat[$player->getID()])) {
 			$errorMsg = TextFormat::RED . 'You are messaging too fast.';
 		} elseif (isset($this->recentMessages[$player->getID()]) &&
				$this->recentMessages[$player->getID()] === $message) {
			$errorMsg = TextFormat::RED . 'You repeated that message.';
		} elseif ($this->profanityChecker->getIsProfane()) {
			$errorMsg = TextFormat::RED . 'That\'s an inappropriate message.';
		} elseif ($this->profanityChecker->getIsDating()) {
			$errorMsg = TextFormat::RED . 'No dating.';
		} elseif ($this->profanityChecker->getIsAdvertising()) {
			$errorMsg = TextFormat::RED . 'No advertising';
		}

		return $errorMsg;
	}

}
