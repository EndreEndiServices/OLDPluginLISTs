<?php
namespace FaigerSYS\MultiProtocol\packet;

use pocketmine\utils\Binary;

use pocketmine\network\protocol\Info;
use pocketmine\network\protocol\LoginPacket;

class ModernLoginPacket extends LoginPacket {
	
	public static $SUPPORTED_PROTOCOLS = [];
	
	public $realProtocol;
	
	public function decode() {
		$buffer = $this->buffer;
		parent::decode();
		$this->realProtocol = $this->protocol;
		if ($this->realProtocol !== Info::CURRENT_PROTOCOL && in_array($this->realProtocol, self::$SUPPORTED_PROTOCOLS)) {
			$buffer = substr($buffer, $this->offset);
			$this->offset = 0;
			$this->buffer = Binary::writeInt(Info::CURRENT_PROTOCOL) . $buffer;
			parent::decode();
		}
	}
	
}
