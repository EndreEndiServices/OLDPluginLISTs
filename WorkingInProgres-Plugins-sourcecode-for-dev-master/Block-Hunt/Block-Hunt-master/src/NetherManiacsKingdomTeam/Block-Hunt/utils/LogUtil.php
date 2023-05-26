<?php
namespace NetherManiacsKingdom\Block-Hunt\utils;

use pocketmine\utils\Config;
use pocketmine\plugin\Plugin;


class LogUtil {

	public static function printLog(Plugin $pg, \Exception $e){
		$errout="";
		$errout="message:".$e->getMessage()."\n";
		$errout="file:".$e->getFile()."\n";
		$errout="code:".$e->getCode()."\n";
		$errout="line:".$e->getLine()."\n";
		$errout="trace:".$e->getTraceAsString()."\n";
		$pg->getLogger ()->error ($errout);
	}
	
	public static function logInfo(Plugin $pg, $message){
		$pg->getLogger()->info($message);
	}
}