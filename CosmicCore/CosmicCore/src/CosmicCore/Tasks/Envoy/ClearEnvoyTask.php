<?php
namespace CosmicCore\Tasks\Envoy;

use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

class ClearEnvoyTask extends AsyncTask
{

    public function __construct($issuer)
    {
        $this->issuer = $issuer;
    }

    public function onRun()
    {
    }

    public function removeEnvoys($issuer, Server $server)
    {
        $level = $server->getLevelByName("world");
        $issuer->sendMessage(TF::YELLOW . "This might take up to a minute.");
        $core = $server->getPluginManager()->getPlugin("CosmicCore");
        $cfg = new Config($core->getDataFolder() . "xyz.yml", Config::YAML);
        $array = array("1, 2, 1", "1, 2, 2");
        $x1 = $cfg->get("protX1");
        $x2 = $cfg->get("protX2");
        $y1 = $cfg->get("protY1");
        $y2 = $cfg->get("protY2");
        $z1 = $cfg->get("protZ1");
        $z2 = $cfg->get("protZ2");
        for ($cx = $x1 - 1; $cx <= $x2; $cx++) {
            for ($cy = 20; $cy <= 127; $cy++) {
                for ($cz = $z1 - 1; $cz <= $z2; $cz++) {
                    if ($level->getBlockIdAt($cx, $cy, $cz) == 146 || $level->getBlockIdAt($cx, $cy, $cz) == 54) array_push($array, "$cx, $cy, $cz");
                }
            }
        }
        return $array;
    }

    public function onCompletion(Server $server)
    {
        $pos = $this->removeEnvoys($this->issuer, $server);
        foreach ($pos as $envoy) {
            $this->issuer->sendMessage(TF::YELLOW . "Found and removed envoy at " . TF::AQUA . $envoy);
            $server->getLevelByName("world")->setBlock(new Vector3($envoy), Block::get(0));
        }
    }
}