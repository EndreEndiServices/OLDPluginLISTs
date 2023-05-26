<?php

namespace LbCore\player\exceptions;

use LbCore\player\LbPlayer;
use LbCore\player\exceptions\PlayerBaseException;

class KitDuplicationException extends PlayerBaseException {
	public function __construct(LbPlayer $player, $kitId) {
		$kitName = \Kits\KitData::getKitName($kitId);
		parent::__construct(self::$kitDuplication . " Player: {$player->getName()} | Kit: {$kitName}", 0, null, $player);
	}
}

