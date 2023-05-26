<?php

namespace LbCore\player\exceptions;

use LbCore\player\LbPlayer;
use LbCore\player\exceptions\PlayerBaseException;

class WrongPassException extends PlayerBaseException {
	public function __construct(LbPlayer $player) {
		parent::__construct('INCORRECT_PASSWORD', 0, null, $player);
	}
}

