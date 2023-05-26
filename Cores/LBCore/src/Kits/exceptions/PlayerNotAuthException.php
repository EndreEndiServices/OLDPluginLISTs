<?php

namespace Kits\exceptions;

use Kits\exceptions\KitBaseException;

class PlayerNotAuthException extends KitBaseException {
	public function __construct($message = null, $code = 0, Exception $previous = null) {
		parent::__construct(KitBaseException::$playerNotAuthException, $code, $previous);
	}
}
