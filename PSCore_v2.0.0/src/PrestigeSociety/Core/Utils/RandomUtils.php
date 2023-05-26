<?php

namespace PrestigeSociety\Core\Utils;

use pocketmine\math\Vector3;
use pocketmine\Player;
use PrestigeSociety\Core\PrestigeSocietyCore;

class RandomUtils {
	/** @var array */
	private static $t = [];

	/**
	 *
	 * @param            $string
	 * @param array|null $elements
	 *
	 * @return mixed|string
	 *
	 */
	public static function textOptions($string, array $elements = null){
		$f = $string;
		if(isset(self::$t[$f])) $f = self::$t[$f];
		if(is_array($elements) && count($elements) >= 1){
			$v = ["%%" => "%"];
			$i = 0;
			foreach($elements as $ret){
				$v["%$i%"] = $ret;
				++$i;
			}
			$f = strtr($f, $v);
		}
		$f = str_replace("%%", "\xc2\xa7", $f);

		return $f;
	}

	/**
	 *
	 * @param $url
	 *
	 * @return mixed
	 *
	 */
	public static function getUrlContents($url){
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		$data = curl_exec($curl);
		curl_close($curl);

		return $data;
	}

	/**
	 *
	 * @param $str
	 *
	 * @return mixed
	 *
	 */
	public static function colorMessage($str){
		$str = str_replace("@rand_color", exc::randomColor(), $str);
		$str = str_replace("&", "\xc2\xa7", $str);

		return $str;
	}

	/**
	 *
	 * @return Vector3
	 *
	 */
	public static function randomVector(): Vector3{
		return new Vector3(mt_rand(0, 1000), mt_rand(0, 128), mt_rand(0, 1000));
	}

	/**
	 *
	 * @param        $txt
	 * @param Player $p
	 *
	 * @return mixed
	 *
	 */
	public static function authTextReplacer($txt, Player $p){
		$on = count(PrestigeSocietyCore::getInstance()->getServer()->getOnlinePlayers());
		$max = PrestigeSocietyCore::getInstance()->getServer()->getMaxPlayers();
		$pfx = CoreInfo::COMPANY_NAME;
		$onStaff = [];
		foreach(PrestigeSocietyCore::getInstance()->getServer()->getOnlinePlayers() as $p){
			if($p->isOp()){
				$onStaff[] = $p->getName();
			}
		}
		if(!empty($onStaff)){
			$onStaff = implode(", ", $onStaff);
		}else{
			$onStaff = "";
		}
		$txt = str_replace(["@online_players", "@max_players", "@prefix", "@online_staff_names", "@player"],
			[$on, $max, $pfx, $onStaff, $p->getName()], $txt);

		return $txt;
	}

	/**
	 *
	 * @param $txt
	 *
	 * @return mixed
	 *
	 */
	public static function broadcasterTextReplacer($txt){
		$on = count(PrestigeSocietyCore::getInstance()->getServer()->getOnlinePlayers());
		$max = PrestigeSocietyCore::getInstance()->getServer()->getMaxPlayers();
		$pfx = CoreInfo::COMPANY_NAME;
		$onStaff = [];
		foreach(PrestigeSocietyCore::getInstance()->getServer()->getOnlinePlayers() as $p){
			if($p->isOp()){
				$onStaff[] = $p->getName();
			}
		}
		if(!empty($onStaff)){
			$onStaff = implode(", ", $onStaff);
		}else{
			$onStaff = "";
		}
		$tps = PrestigeSocietyCore::getInstance()->getServer()->getTicksPerSecond();
		$load = PrestigeSocietyCore::getInstance()->getServer()->getTickUsage();
		$txt = str_replace(["@online_players", "@max_players", "@prefix", "@online_staff_names", "@tps", "@server_load"],
			[$on, $max, $pfx, $onStaff, $tps, $load], $txt);

		return $txt;
	}

	/**
	 *
	 * @param $txt
	 *
	 * @return mixed
	 *
	 */
	public static function restarterTextReplacer($txt){
		$hours = PrestigeSocietyCore::getInstance()->PrestigeSocietyRestarter->toHours();
		$min = PrestigeSocietyCore::getInstance()->PrestigeSocietyRestarter->toMinutes();
		$secs = PrestigeSocietyCore::getInstance()->PrestigeSocietyRestarter->toSeconds();
		$txt = str_replace(["@hours", "@seconds", "@minutes"], [$hours, $secs, $min], $txt);

		return $txt;
	}

	/**
	 *
	 * @param $data
	 *
	 * @return array
	 *
	 */
	public static function objToArray($data){
		$array = [];
		if($data instanceof \stdClass or $data instanceof \ArrayObject){
			foreach($data as $color => $hex){
				$array[$color] = $hex;
			}
		}

		return $array;
	}

	/**
	 *
	 * @return mixed
	 *
	 */
	public static function getCoreMessagesArray(): array{
		return [
			"auth"                 => [
				"change_password_success" => "&aYour password was successfully changed!",
				"old_password_not_match"  => "&eYour old password does not password, try again",
			],
			"login"                => [
				"join_message"        => "&aPlease type password in chat to login:",
				"login_success"       => "&aSucessfully logged in!",
				"timeout_kick_reason" => "&cYou took too long to login!",
				"wrong_password"      => "&cWrong password, try again",
				"unknown_error"       => "&cAn unknown error occurred...",
			],
			"register"             => [
				"join_message"           => "&aType password in chat to register:",
				"password_confirm"       => "&6Type password again to confirm:",
				"registration_success"   => "&aSuccessfully registered, enjoy :D",
				"timeout_kick_reason"    => "&cYou took too long to login!",
				"wrong_password"         => "&cWrong password, try again",
				"non_matching_passwords" => "&cPasswords don't match, try again.",
			],
			"anti_cheat"           => [
				"kick_kill_aura"       => "&cYou were kicked for using kill aura!",
				"kick_flying"          => "&cYou were kicked for flying!",
				"kick_anti_knock_back" => "&cYou were kicked for anti knock back!",
				"kick_one_hit_kill"    => "&cYou were kicked for using one hit kill!",
			],
			"restarter"            => [
				"restart_message"    => "&aServer restarting...",
				"time_message"       => "&eRestarting server in @hours hours, @minutes minutes, and @seconds seconds",
				"count_down_message" => "&bRestarting in @seconds...",
			],
			"broadcaster_messages" => [
				"&amessage 1",
				"&bmessage 2",
				"&cmessage 3",
			],
			"chat_protector"       => [
				"no_spam"  => "&cPlease don't chat took quickly!",
				"no_swear" => "&cPlease don't swear!",
			],
			"land_protector"       => [
				"select_first_position"  => "&aPlease select first position",
				"select_second_position" => "&aPlease select second position",
				"added_land_success"     => "&aAdded land '@land'",
				"select_positions_first" => "&ePlease select positions before adding a land",
				"missing_land_name"      => "&cPlease enter a land name",
				"missing_mode_name"      => "&cMissing the mode name",
				"mode_change_successful" => "&cSet land mode '@mode' of area '@land' to '@bool'",
				"invalid_land_name"      => "Cannot set '@mode' for land '@land' to '@bool' because it is not a valid land name",
				"invalid_bool_value"     => "'@bool' is not a valid bool. Please enter 'true' or 'false'",
				"remove_land_success"    => "&aSuccessfully removed land '@land'",
				"successful_selection_1" => "&aSuccessfully selected first position at : @x, @y, @z",
				"successful_selection_2" => "&aSuccessfully selected second position at : @x, @y, @z",
				"land_exists"            => "&c'@land' already exists",
				"missing_mode_bool"      => "Please enter a mode bool : 'true' or 'false'",
				"land_does_not_exist"    => "Land '@land' does not exist!",
				"removed_land_success"   => "&d&lSwirlpix &6>> Land '@land' has been deleted!",
			],
			"signs"                => [
				"not_l_or_e_level"     => "&cLevel is not loaded or does not exist",
				"created_world_sign"   => "&aSuccessfully created world sign!",
				"created_garbage_sign" => "&eSuccessfully created garbage sign!",
			],
		];
	}
}