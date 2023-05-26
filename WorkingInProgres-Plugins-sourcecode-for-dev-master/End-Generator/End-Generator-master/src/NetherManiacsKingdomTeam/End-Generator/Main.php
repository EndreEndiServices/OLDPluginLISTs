<?php

namespace NetherManiacsKingdom\End-Generator;

use pocketmine\plugin\PluginBase;
use pocketmine\level\generator\Generator;

class Main extends PluginBase {
	
	public function onEnable(){
		Generator::addGenerator(EndGenerator::class, EndGenerator::NAME);
	}
}