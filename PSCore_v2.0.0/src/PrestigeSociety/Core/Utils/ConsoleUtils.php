<?php

namespace PrestigeSociety\Core\Utils;

use PrestigeSociety\Core\PrestigeSocietyCore;

class ConsoleUtils {
	/**
	 * @param array $a
	 * @param string $breakLine
	 */
	public static function logArray(array $a, $breakLine = "\n"){
		foreach($a as $line){
			PrestigeSocietyCore::getInstance()->getLogger()->info($line . $breakLine);
		}
	}

	/**
	 * @param $str
	 */
	public static function log($str){
		PrestigeSocietyCore::getInstance()->getLogger()->info($str);
	}

	/**
	 * @param $str
	 * @param array $e
	 */
	public static function logWithOpts($str, array $e = []){
		PrestigeSocietyCore::getInstance()->getLogger()->info(RandomUtils::textOptions($str, $e));
	}
}