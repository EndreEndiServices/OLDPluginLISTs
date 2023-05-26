<?php

namespace Kits\exceptions;

use Kits\exceptions\KitBaseException;

class KitNotExistException extends KitBaseException {
	public function __construct($kitName) {
		parent::__construct(KitBaseException::$kitDoesntExistException . " ({$kitName})");
	}
}
