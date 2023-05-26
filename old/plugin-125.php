<?
     
/*
__PocketMine Plugin__
name=Map Reset
version=1.0
author=Antisober552
class=mapReset
apiversion=9,10,11,12
*/

class mapReset implements plugin{
private $api;
private $nr;
private $Notice;
private $leval;
private $server;

public function __construct(ServerAPI $api, $server = false){
        $this->api = $api;
        $this->server = ServerAPI::request();
        }

public function __destruct() { 
}

public function init () {
        $this->nr= 0;
        $this->api->schedule(20 * 60 * 60, array($this, "mapTime"), array(), true);
        $leval = $this->server->api->level->getDefault()->getName();
        $this->Notice = array(
                                "[Notice] Map reset in 23 hours",
                                "[Notice] Map reset in 22 hours",
                                "[Notice] Map reset in 21 hours",
                                "[Notice] Map reset in 20 hours",
                                "[Notice] Map reset in 19 hours",
                                "[Notice] Map reset in 18 hours",
                                "[Notice] Map reset in 17 hours",
                                "[Notice] Map reset in 16 hours",
                                "[Notice] Map reset in 15 hours",
                                "[Notice] Map reset in 14 hours",
                                "[Notice] Map reset in 13 hours",
                                "[Notice] Map reset in 12 hours",
                                "[Notice] Map reset in 11 hours",
                                "[Notice] Map reset in 10 hours",
                                "[Notice] Map reset in 09 hours",
                                "[Notice] Map reset in 08 hours",
                                "[Notice] Map reset in 07 hours",
                                "[Notice] Map reset in 06 hours",
                                "[Notice] Map reset in 05 hours",
                                "[Notice] Map reset in 04 hours",
                                "[Notice] Map reset in 03 hours",
                                "[Notice] Map reset in 02 hours",
                                "[Notice] Map reset in 01 hour"
                                );
                }

public function mapTime(){
                        $this->api->chat->broadcast($this->Notice[$this->nr]);
                        $this->nr++;
                        if ($this->nr == count($this->Notice)-1) {
                                $this->mapReset();
                        }
                }

public function removeWorld() {
        $dir = './worlds/'.$this->server->api->getProperty("level-name").'/';
                foreach(glob($dir.'*.*') as $v){
                        unlink($v);
            }
        }

public function removeWorld1() {
        $dir = './worlds/'.$this->server->api->getProperty("level-name").'/chunks/';
                foreach(glob($dir.'*.*') as $v){
                        unlink($v);
            }
        }

public function mapReset() {
                $this->removeWorld();
                $this->removeWorld1();
                rmdir('./worlds/'.$this->server->api->getProperty("level-name").'/chunks/');
                rmdir('./worlds/'.$this->server->api->getProperty("level-name").'/');
                rmdir('./worlds');
                $this->api->console->run("stop");
                }

 }

?>
