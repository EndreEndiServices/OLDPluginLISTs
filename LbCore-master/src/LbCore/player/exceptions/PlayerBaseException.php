<?php

/**
 * All exceptions for LbPlayer class
 *
 * @author fuduin
 */

namespace LbCore\player\exceptions;

use LbCore\player\LbPlayer;

class PlayerBaseException extends \Exception {
	protected static $kitDuplication = "Player already have this kit.";
	protected static $kitWrongAddData = "Error occurred in time of kit obtaining or it was obtained by illegal way.";
	/* @param LbPlayer */
	protected $player;
	
	public function __construct($message = null, $code = 0, Exception $previous = null, LbPlayer $player) {
		$this->player = $player;
		parent::__construct($message, $code, $previous);
	}
		
	public function getPlayer() {
		return $this->player;
	}
}
