<?php

namespace Kits\exceptions;

class KitBaseException extends \Exception {
	protected static $prefix = "[Kit Component] : ";
	protected static $kitsNotEnableException = "Kit Component isn't enable.";
	protected static $playerNotAuthException = "Player must be authorized for using kits.";
	protected static $kitDoesntExistException = "You trying activate not existing kit.";
	
	public function __construct($message = null, $code = 0, Exception $previous = null) {
		parent::__construct(self::$prefix . $message, $code, $previous);
	}
}
