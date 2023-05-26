<?php
namespace CosmicCore\Tasks\Envoy;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class StartEnvoyTask extends AsyncTask
{

    public function onRun()
    {
    }

    public function onCompletion(Server $server)
    {
        $server->getPluginManager()->getPlugin("CosmicCore")->startEnvoy();
    }
}