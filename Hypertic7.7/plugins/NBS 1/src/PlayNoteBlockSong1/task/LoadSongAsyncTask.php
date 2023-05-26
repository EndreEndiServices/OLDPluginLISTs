<?php
namespace PlayNoteBlockSong1\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class LoadSongAsyncTask extends AsyncTask{
	public function __construct(){
	}

	public function onCompletion(Server $server){
		$server->getPluginManager()->getPlugin("PlayNoteBlockSong1")->loadSong();
	}

	public function onRun(){
	}
}