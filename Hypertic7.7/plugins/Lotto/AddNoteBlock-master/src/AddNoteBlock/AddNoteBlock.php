<?php
namespace AddNoteBlock;

use pocketmine\plugin\PluginBase;
use pocketmine\item\Item;
use pocketmine\block\Block;
use AddNoteBlock\block\NoteBlock;

class AddNoteBlock extends PluginBase{
	public function onLoad(){
		$this->registerBlock(NoteBlock::NOTEBLOCK, NoteBlock::class);
 	}

	public function registerBlock($id, $class){
		Block::$list[$id] = $class;
		if($id < 255){
			Item::$list[$id] = $class;
			if(!Item::isCreativeItem($item = Item::get($id))){
				Item::addCreativeItem($item);
			}
		}
		for($data = 0; $data < 16; ++$data){
			Block::$fullList[($id << 4) | $data] = new $class($data);
		}		
	}
}