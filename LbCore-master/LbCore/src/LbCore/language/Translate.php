<?php
namespace LbCore\language;

use pocketmine\utils\TextFormat;
use pocketmine\Server;
use LbCore\player\LbPlayer;

/**
 * messages localization
 */
class Translate {
	
	const DEFAULT_LANGUAGE = 'English';
	/*Common prefixes for language strings*/
	const PREFIX_PLAYER_ACTION = TextFormat::DARK_BLUE."- ".TextFormat::YELLOW;
	const PREFIX_ACTION_FAILED = TextFormat::DARK_PURPLE."- ".TextFormat::RED;
	const PREFIX_GAME_EVENT = TextFormat::BLUE."> ".TextFormat::DARK_AQUA;
	const PREFIX_PLAYER_DEATH = TextFormat::DARK_RED."> ".TextFormat::GRAY;
	const PREFIX_ANNOUNCEMENT = TextFormat::DARK_GRAY;
	const PREFIX_SYSTEM_MESSAGE = TextFormat::BLUE."> ".TextFormat::GRAY;
	
	/** @var Translate*/
	private static $instance;	
	/*array of allowed languages, needs to check language with command /lang */
	private static $allowedLanguages = array(
		'en' => 'English',
		'es' => 'Spanish',
		'de' => 'German',
		'du' => 'Dutch'
	);
	/**@var string - contains path to find language files*/
	private $translatePath = 'LbCore\language\\core\\';
	/**@var array - common translates for current plugin*/
	private $translates = array();
	
		
	private function __construct() {
		//create arrays for each language in core folder
		$this->createTranslations($this->translatePath);
//		$this->langToTable(); //this code creates csv file with translates
//		$this->csvToLines();//this code moves csv file to array
//		$this->makeAlertFileFromCsv();//use it to create json alert file from csv
//		$this->makeCsvFromAlertFile();//use it to create scv alert file from json
	}
		
	private function __clone() {
		//protect
	}
	
	static public function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
	
	/**
	 * Use this to call inside game plugin to overwrite core $translates
	 * 
	 * @param string
	 */
	public function createTranslations(string $translatePath) {		
		//create new objects for languages from plugin path
		foreach (self::$allowedLanguages as $language) {
			$languagePath = $translatePath . $language;
			$this->$language = new $languagePath();
			$this->translates[$language] = $this->$language->translates;
		}
	}
	
	/**
	 * Localized broadcast message function
	 *
	 * @param string $msg
	 * @param string $pluginName
	 * @param array $args
	 * @param string $prefix
	 * @param string $suffix
	 */
	public function broadcastMessageLocalized(
			string $msg, 
			array $args = array(), 
			string $prefix = "", 
			string $suffix = "") {
		$players = Server::getInstance()->getDefaultLevel()->getPlayers();
		foreach ($players as $player) {
			$this->sendLocalizedMessage($player, $msg, $args, $prefix, $suffix);
		}
	}
        
	/**
	 * Send to player localized popup (depends on his language)
	 * 
	 * @param LbPlayer $player
	 * @param string $string
	 * @param array $args
	 * @param string $prefix
	 * @param string $suffix
	*/
	public function sendLocalizedPopup(
			LbPlayer $player, 
			string $string, 
			array $args = array(), 
			string $prefix = "", 
			string $suffix = "") {
		$translatedMsg = $this->getTranslatedString($player->language, $string, $prefix, $args, $suffix);
		$player->sendPopup($translatedMsg);
	}	
	
	/**
	 * Send to player localized message (depends on his language)
	 * 
	 * @param LbPlayer $player
	 * @param string $string
	 * @param array $args
	 * @param string $prefix
	 * @param string $suffix
	 */
	public function sendLocalizedMessage(
			LbPlayer $player, 
			string $string, 
			array $args = array(), 
			string $prefix = "", 
			string $suffix = "") {
		$translatedMsg = $this->getTranslatedString($player->language, $string, $prefix, $args, $suffix);
		$player->sendMessage($translatedMsg);
	}
	
	/**
	 * Method translates string by player's current panguage
	 * 
	 * @param string $language
	 * @param string $string
	 * @param string $prefix
	 * @param array $args
	 * @param string $suffix
	 * @return string
	 */
	public function getTranslatedString(
			string $language, 
			string $string, 
			string $prefix = "", 
			array $args = array(), 
			string $suffix = "") {
		//check language
		$lang = self::DEFAULT_LANGUAGE;
		if (in_array($language, self::$allowedLanguages) &&
			isset($this->translates[$language])) {			
			$lang = $language;
		}
		
		//format string and check if array key exists
		$string = trim($string);
		$langKey = strtoupper(str_replace(' ', '_', $string));
		
		if (!isset($this->translates[$lang][$langKey])) {
			Server::getInstance()->getLogger()->info(
					TextFormat::BOLD . TextFormat::GOLD . 
					'EXCEPTION: Translate for string ' . $langKey . 
					' not found in ' . $lang . ' language');
			return $prefix . $string . $suffix;
		} 
		
		$msg = $this->translates[$lang][$string];
		
		if (is_array($msg)) {
			return $msg;
		}
		
		//handling args if needs
		if (stristr($msg, 'arg1')) {
			$msg = self::argumentsToString($msg, $args);
		}
		return $prefix . $msg . $suffix;
	}
	

	/**
	 * Formatting string with args - replacing args with params from array $args
	 * 
	 * @param string $string
	 * @param array $args
	 * @return string
	 */
	private static function argumentsToString(string $string, array $args) {
		$newConstant = $string;
		foreach($args as $key => $val) {
			$num = $key + 1;
			$newConstant = str_replace("arg" . $num, $val, $newConstant);
		}

		return $newConstant;
	}
	
	/**
	 * Language request - returns name of suitable language 
	 * or default value - English (calls in lbCommands)
	 * 
	 * @param string $key
	 * @return string
	 */
	public function getAllowedLanguage(string $key = "") {
		$language = self::DEFAULT_LANGUAGE;
		if ($key && isset(self::$allowedLanguages[$key])) {
			//$language = this element
			$language = self::$allowedLanguages[$key];
		}
		return $language;
	}
	
	/**
	 * Help function to create csv file with actual translates
	 * Call it in onEnable method of plugin to create files
	 */
	private function langToTable() {
		$list = [
			['KEY', 'ENGLISH', 'CUSTOM_LANGUAGE']
		];
		foreach ($this->translates['English'] as $key => $value) {//now works for english translates
			if (is_array($value)) {
				$value = implode('; ', $value);//then we can make array back by this delimiter
			}
			$list[] = array($key, $value);
		}
		
		$fp = fopen('plugins/core-translate.csv', 'w');
		foreach ($list as $field) {
			fputcsv($fp, $field);
		}

		fclose($fp);
	}
	
	/**
	 * Use it to create language strings from csv to insert in language file
	 * Calls in Translate constructor
	 * 
	 * @return void
	 */
	private function csvToLines() {
		$currentLanguage = "german";//must be the same as folder name
		$pluginAbbr = "core";//must be core for lbcore strings
		$translatePath = __DIR__ . "/" . strtolower($currentLanguage) . "/" . $pluginAbbr . "-translate.csv";

        // If no file exists
        if(!file_exists($translatePath)) {
			echo "Could not find language file in that path";
            return;
        }		
        $rows = fopen($translatePath, 'r');
		while (($line = fgetcsv($rows)) !== false) {
			//check for right key and translate exist
			if (strtoupper($line[0]) == 'KEY' ||
					!$line[0] ||
					!$line[1]) {
				continue;
			}
			$key = $line[0];//column of keys
			$value = $line[2];//column of custom language strings
			$value = trim(preg_replace('~[\r\n]+~', '\\n', $value));//replace new lines with \n symbols
			$valueFromArray = "";
			//format if we have an array
			if (strpos($value, ";")) {
				$valueFromArray = '["' . str_replace(";", '", "', $value) . '"]';
			}
			//type into console formatted strings
			if ($pluginAbbr == "core") {
				if ($valueFromArray) {
					echo '"' . $key . '" => ' . $valueFromArray . ",\n";
				} else {
					echo '"' . $key . '" => "' . $value . '"' . ",\n";
				}
			} else {
				if ($valueFromArray) {
					echo '$this->translates["' . $key . '"] = ' . $valueFromArray . ",\n";
				} else {
					echo '$this->translates["' . $key . '"] = "' . $value . '"' . ";\n";
				}
			}
		}
		fclose($rows);

	}
	
	/**
	 * Prepare json file for Alert system from scv file
	 * TODO - look for a better place for current files, now it's a language folder  (the same as this file)
	 */
	private function makeAlertFileFromCsv() {
		//prepare vars - lang and filepaths
		$targetLang = 'de';//use language abbr here
		$targetFileName = 'lobby_alerts_sg';//original and target filenames must be the same
		$targetPath = './plugins/lbcore/src/Alert/data/';//path must be inside Alert sub-plugin
		//get csv file with alert strings
		$currentPath = __DIR__ . "/" . $targetFileName . ".csv";
		if (!file_exists($currentPath)) {
			echo "Could not find language file in that path";
            return;
		}
		$alerts = fopen($currentPath, 'r');
		//prepare array from strings
		$alertArray = [];
		while (($line = fgetcsv($alerts)) !== false) {
			//make subarray
			$line[0] = trim(preg_replace('~[\r\n]+~', ';', $line[0]));//replace new lines with ; symbols
			$subArray = explode(';', $line[0]);
			$alertArray[] = $subArray;
		}
		fclose($alerts);
		//create or replace json file, then move array to json file
		$fp = fopen($targetPath . $targetLang . '/' . $targetFileName . '.json', 'w');
		$rawJson = json_encode($alertArray);
		$resultJson = str_replace('],', "],\n", $rawJson);
		fwrite($fp, $resultJson);
		fclose($fp);
			
	}
	
	/**
	 * Prepare csv file from Alert system
	 * As usual it is English strings file
	 * TODO - look for a better place for target files, now it's a language folder (the same as this file)
	 */
	private function makeCsvFromAlertFile() {
		//prepare vars - lang and filepaths
		$targetLang = 'en';//use language abbr here
		$targetFileName = 'lobby_alerts_sg';//original and target filenames must be the same
		$currentPath = './plugins/lbcore/src/Alert/data/';//path must be inside Alert sub-plugin
		//get json file contents as array
		if (!file_exists($currentPath . $targetLang . '/' . $targetFileName . '.json')) {
			echo "Could not find language file in that path";
            return;
		}
		$alertArray = json_decode(file_get_contents($currentPath . $targetLang . '/' . $targetFileName . '.json'));
		if (!$alertArray || !is_array($alertArray)) {
            return;
		}
		foreach ($alertArray as $key => &$value) {
			if (count($value) > 1) {
				$subLine = implode('\n', $value);
				$value = [$subLine];
			}
		}
		//prepare scv file and put array lines there
		$fp = fopen(__DIR__ . "/" . $targetFileName . '.csv', 'w');
		foreach ($alertArray as $field) {
			fputcsv($fp, $field);
		}
		fclose($fp);
	}
}
