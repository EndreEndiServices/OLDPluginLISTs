<?php

namespace Kits;

/**
 * KitData builds total kits object and contains static data for kits add-on
 */
class KitData
{
    // Kit ID's
    const CLASSIC_SG = 1;
    const MIDAS = 2;
    const ARCHER = 3;
    const ASSASSIN = 4;
    const TANK = 5;
    const TELEPORTER = 6;
    const BRAWLER = 7;
    const ATHLETE = 8;
    const PROSPECTOR = 9;
    const CREEPER = 10;

	/**@var array - total kit info from json*/
    private static $kits = [];
	/**@var array - types of kits*/
    private static $categories = [];
	/**@var int - total amount of kits*/
    public static $kitCount = 0;
	
	/**@var array - contains weekday as key and kit id as value
	 * maybe move this to kitData json
	 * 
	 */
//	private static $kitByWeekday = array(
//		0 => self::DEFENDER,
//		1 => self::WARRIOR,
//		2 => self::SENTRY,
//		3 => self::MIDAS,
//		4 => self::ARCHER,
//		5 => self::ATHLETE,
//		6 => self::EXPLORER
//	);

	//protect
	private function __construct() {}
	private function __clone() {}	
	public function __destruct() {}	
	
	/**
	 * Create kits from json data, prepare fields for kit system
	 */
	public static function enable() {
		self::buildKits();
        self::$kitCount = count(self::$kits);
	}
	
	/**
     * Reads in a json file of all the kits and puts them 
	 * into instance variables $this->kits and $this->categories
     *
     * @return bool
     */
    private static function buildKits(){
        $jsonRaw = file_get_contents(__DIR__."/data/kitData.json");
        if($jsonRaw !== false) {
            $jsonDecoded = json_decode($jsonRaw);
            self::$kits = $jsonDecoded->kits;
            self::$categories = $jsonDecoded->categories;
        } else {
            return false;
        }
    }

    /**
     * Returns an array of all the kit objects.
     *
     * @return array|bool
     */
    public static function getKits(){
        if(count(self::$kits) == 0){
            return false;
        } else {
            return self::$kits;
        }
    }

    /**
     * Returns an object of a single kit of the given id
     *
     * @param $kitId
     * @return bool
     */

    public static function getKit($kitId){
        if(self::$kits !== false){
            foreach(self::$kits as $kit){
                if($kit->id == $kitId){
                    return $kit;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * Return the name of a kit.
     *
     * @param $kitId
     * @return bool
     */

    public static function getKitName($kitId){
        if(self::$kits !== false){
            foreach(self::$kits as $kit){
                if($kit->id == $kitId){
                    return $kit->name;
                }
            }
        } else {
            return false;
        }
    }

	/**
	 * Return description of specified kit
	 * 
	 * @param int $kitId
	 * @return string|boolean
	 */
    public static function getKitDesc($kitId){
        if(self::$kits !== false){
            foreach(self::$kits as $kit){
                if($kit->id == $kitId){
                    return $kit->description;
                }
            }
        } else {
            return false;
        }
    }
	
	/**
	 * 
	 * @param type $kitName
	 * @return boolean
	 */
	public static function getKitIdByName($kitName) {
		if(self::$kits !== false){
			$kitName = ucfirst($kitName);
            foreach(self::$kits as $kit){
                if($kit->name == $kitName){
                    return $kit->id;
                }
            }
        } else {
            return false;
        }
	}


//	public static function getRandomKit() {
//		//get current weekday
//		$weekDay = jddayofweek(cal_to_jd(CAL_GREGORIAN,date("m"),date("d"),date("Y")));
//		if (isset(self::$kitByWeekday[$weekDay])) {
//			return self::$kitByWeekday[$weekDay];
//		} else {
//			return false;
//		}
//		
//	}

}