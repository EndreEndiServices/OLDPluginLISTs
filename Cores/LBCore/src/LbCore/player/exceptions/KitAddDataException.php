<?php

namespace LbCore\player\exceptions;

use LbCore\player\LbPlayer;
use LbCore\player\exceptions\PlayerBaseException;

class KitAddDataException extends PlayerBaseException {
	public function __construct(LbPlayer $player) {
		$errorText = '['.$player->getName().'] '.self::$kitWrongAddData;
		parent::__construct($errorText, 0, null, $player);
	}
}

