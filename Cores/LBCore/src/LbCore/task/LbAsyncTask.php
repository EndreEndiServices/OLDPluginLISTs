<?php

namespace LbCore\task;

use pocketmine\scheduler\AsyncTask;

/**
 * Common parent task with db data
 */
class LbAsyncTask extends AsyncTask {
	const AUTH_STRING = 'AI9tDW7lnVM7uh3vJdnv';
	const API_URI = 'http://intapi.lbsg.net/';
	//const API_URI = 'http://intapidb.app:8000/';
	
	public function onRun() {}
}
