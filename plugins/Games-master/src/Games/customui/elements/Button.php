<?php

namespace Games\customui\elements;

use Exception;
use pocketmine\Player;

class Button extends UIElement{

	const IMAGE_TYPE_PATH = 'path';
	const IMAGE_TYPE_URL = 'url';

	/** @var string May contains 'path' or 'url' */
	protected $imageType = '';

	/** @var string */
	protected $imagePath = '';

	/**
	 *
	 * @param string $text Button text
	 */
	public function __construct($text){
		$this->text = $text;
	}

	/**
	 * Add image to button
	 *
	 * @param string $imageType
	 * @param string $imagePath
	 * @throws Exception
	 */
	public function addImage(string $imageType, string $imagePath){
		if($imageType != self::IMAGE_TYPE_PATH && $imageType != self::IMAGE_TYPE_URL){
            throw new Exception(__CLASS__.'::'.__METHOD__.' Invalid image type');
        }
        
        $this->imageType = $imageType;
        $this->imagePath = $imagePath;

        return $this;

	}

	/**
	 * Return array. Calls only in SimpleForm class
	 *
	 * @return array
	 */
	final public function jsonSerialize(){
		$data = ['text' => $this->text];
		if ($this->imageType != ''){
			$data['image'] = [
				'type' => $this->imageType,
				'data' => $this->imagePath
			];
		}
		return $data;
	}

	public function handle($value, Player $player){

	}

}
