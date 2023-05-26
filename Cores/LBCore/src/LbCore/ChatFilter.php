<?php

namespace LbCore;

use LbCore\language\Translate;
use LbCore\chat\ChatClasser;
use LbCore\chat\ChatLogger;
use LbCore\player\LbPlayer;

/**
 * Class to check for allowed message 
 * (prevent passwords in chat, prevent dating, short and repeating messages)
 */
class ChatFilter {
	/**@var ChatClasser*/
	protected $profanityChecker;
	/**@var ChatLogger*/
	protected $chatLogger;
	/**@var array*/
	protected $recentMessages = array();
	/**@var bool*/
	protected $enableMessageFrequency;
	/**@var array*/
	protected $recentChat = array();

	public function __construct($logger, $enableMsgFrequency = true) {
		$this->profanityChecker = new ChatClasser();
		$this->chatLogger = new ChatLogger($logger);
		$this->enableMessageFrequency = $enableMsgFrequency;
	}
	
	public function clearRecentChat() {
 		$this->recentChat = array();
 	}

	/**
	 * Check for valid message
	 * 
	 * @param LbPlayer $player
	 * @param string $message
	 * @param boolean $needCheck
	 * @return boolean
	 */
	public function check($player, $message, $needCheck = true) {
		if ($player instanceof LbPlayer) {
			$username = $player->getName();
			
			if ($player->isAuthorized() && $player->checkPass($message)) {
				$player->sendLocalizedMessage("CANNOT_SAY_PASSWORD");
				return false;
			}

			// Check the message and log the result.
			$checkResult = $this->profanityChecker->check($message);
			$this->chatLogger->log($message, $checkResult, $this->profanityChecker->getTerseReason(), $username);

			$errorMessage = $this->getErrorMessage($message, $player);
			if (!empty($errorMessage)) {
				$player->sendLocalizedMessage($errorMessage, array(), Translate::PREFIX_ACTION_FAILED);
				//rarely (3% chance) pet can say something about that
				if ($player->isVip() &&
						$player->getState() === LbPlayer::IN_LOBBY &&
						$player->getPet() !== null &&
						rand(1,33) === 1) {
					\Pets\Pets::sendPetMessage($player, \Pets\Pets::OWNER_PROFANITY);
				}				
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
		return false;
	}

	/**
	 * Get message with suitable error
	 * 
	 * @param string $message
	 * @param LbPlayer $player
	 * @return string
	 */
	private function getErrorMessage($message, $player) {
		$errorMsg = '';

		if (strlen($message) === 0) {
			$errorMsg = 'MSG_SHORT';
		} elseif (isset($this->recentChat[$player->getID()])) {
 			/* player already posted message in last 3 seconds */
 			$errorMsg = 'RATE_LIMITED';
 		} elseif (isset($this->recentMessages[$player->getID()]) &&
				$this->recentMessages[$player->getID()] === $message) {
			/* player's message repeated his previous message */
			$errorMsg = 'MSG_REPEATED';
		} elseif ($player->checkPass($message)) {
			$errorMsg = 'CANNOT_SAY_PASSWORD';
		} elseif ($this->profanityChecker->getIsProfane()) {
			$errorMsg = 'MSG_INAPPROPRIATE';
		} elseif ($this->profanityChecker->getIsDating()) {
			$errorMsg = 'NO_DATING';
		} elseif ($this->profanityChecker->getIsAdvertising()) {
			$errorMsg = 'NO_ADVERTISING';
		}

		return $errorMsg;
	}

}
