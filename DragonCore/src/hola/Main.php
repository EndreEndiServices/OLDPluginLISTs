<?php

namespace hola;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\math\Vector3;
use pocketmine\plugin\Plugin;
use pocketmine\level\Position;
use pocketmine\level\Position\getLevel;
use pocketmine\Server;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\level\Level;


class Main extends PluginBase implements Listener{
    
    public $prefix = "§7[§dDragonCraft§7]§r ";
    public $error = "§7[§cERROR§7]§4 ";
    public $titan = "§b§kii§r§3TITAN§b§kii§r§e ";
    public $dragon = "§d§kii§5§r§5DRAGON§d§kii§r§b ";
    public $master = "§e§kiii§r§6MASTER§e§kiii§r§5 ";
    public $king = "§a§kii§r§2KING§a§kii§r§a ";
    public $yt = "§7[§cY§fT§7]§e ";
    public $ytmas = "§b§kii§r§cYou§fTuber§b§kii§r§e ";
    public $cr = "§4§kiii§r§6Creador§4§kiii§r§7 ";
    public $admin = "§0§kiii§r§4Admin§0§kiii§r§a ";
     public $fbi = "§7[§eFBI§7]§6 ";
    public function onEnable()
	{
     
      
   $this->getLogger()->info("DragonCRaftCore enable cifrado by hostinger");  
        $this->getServer()->getPluginManager()->registerEvents($this ,$this);
        $this->getServer()->getPluginManager()->getPlugin("BUHCJOSE");
        $this->getServer()->getPluginManager()->getPlugin("IRONSOUPJOSE");
    
  @mkdir($this->getDataFolder());
                $config = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
                
		$config->save();
        
        
                
        
 $this->getServer()->getScheduler()->scheduleRepeatingTask(new Task($this), 20);
       
    }


 public function getAttachment(Player $player)
	{
		if(!isset($this->attachments[$player->getName()]))
		{
			$this->attachments[$player->getName()] = $player->addAttachment($this);
		}
		
		return $this->attachments[$player->getName()];
	}
    
    
    
    
    public function onJoin(PlayerJoinEvent $event){ 
       $rank = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
       $player = $event->getPlayer();
       $player->setNametag("§b*§8".$player->getName());
        $player->setDisplayName("§e".$player->getName());
 
        $r = $rank->get($player->getName());
        
        if($r == $this->titan)
        {
          
            $player->setNameTag($this->titan.$player->getName());
            $player->setAllowFlight(true);
            $attachment->setPermission("vote.use", true);
           
            
        }
        elseif($r == $this->dragon)
        {
           
            $player->setNameTag($this->dragon.$player->getName());
            $player->setAllowFlight(false);
              $attachment->setPermission("vote.use", true);
        }
        elseif($r == $this->master)
        {
             
            $player->setNameTag($this->master.$player->getName());
            $player->setAllowFlight(true);
             $attachment->setPermission("vote.use", true);
        }
        elseif($r == $this->king)
        {
             
            $player->setNameTag($this->king.$player->getName());
            $player->setAllowFlight(false);
              $attachment->setPermission("vote.use", true);
        }
        elseif($r == $this->yt)
        {
            
            $player->setNameTag($this->yt.$player->getName());
            $player->setAllowFlight(false);
             $attachment->setPermission("vote.use", true);
        }
        elseif($r == $this->ytmas)
        {
            
            $player->setNameTag($this->ytmas.$player->getName());
            $player->setAllowFlight(true);
              $attachment->setPermission("vote.use", true);
        }
        elseif($r == $this->fbi)
        {
             
            $player->setNameTag($this->fbi.$player->getName());
            $player->setAllowFlight(false);
            $attachment->setPermission("pocketmine.command.say", true);
                $attachment->setPermission("pocketmine.command.gamemode", true);
                $attachment->setPermission("pocketmine.command.ban.ip", true);
                $attachment->setPermission("pocketmine.command.unban.ip", true);
                $attachment->setPermission("pocketmine.command.ban.player", true);
                $attachment->setPermission("pocketmine.command.kick", true);
                $attachment->setPermission("pocketmine.command.teleport", true);
             $attachment->setPermission("vote.use", true);
        }
        elseif($r == $this->cr)
        {
          
            $player->setNameTag($this->cr.$player->getName());
            $player->setAllowFlight(true);   
        }
        elseif($r == $this->admin)
        {
             $event->setJoinMessage($this->prefix.$player->getNametag()." §bse unio");
            $player->setNameTag($this->admin.$player->getName());
            $player->setAllowFlight(true);
        }
       
        $this->menu($player);
        
    }
    
   
    
    
    
    
     public function enRespawn(PlayerRespawnEvent $event)
        {
          $config = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
            $player = $event->getPlayer();
         $this->menu($player);
          $r = $rank->get($player->getName());
         if($r == $this->titan)
         {
            $player->setAllowFlight(true);
              $this->menu($player);
              $player->setNameTag($r.$player->getName());
         }
         elseif($r == $this->dragon)
         {
              $player->setAllowFlight(false);
              $this->menu($player);
              $player->setNameTag($r.$player->getName());
         }
         elseif($r == $this->master)
         {
              $player->setAllowFlight(true);
              $this->menu($player);
              $player->setNameTag($r.$player->getName());
         }
         elseif($r == $this->king)
         {
              $player->setAllowFlight(false);
              $this->menu($player);
              $player->setNameTag($r.$player->getName());
         }
         elseif($r == $this->ytmas)
         {
              $player->setAllowFlight(true);
              $this->menu($player);
              $player->setNameTag($r.$player->getName());
         }
         elseif($r == $this->fbi)
         { 
             $player->setAllowFlight(true);
              $this->menu($player);
              $player->setNameTag($r.$player->getName());
         }
         elseif($r == $this->admin)
         {
              $player->setAllowFlight(true);
              $this->menu($player);
              $player->setNameTag($r.$player->getName());
         }
         elseif($r == $this->cr)
         {
              $player->setAllowFlight(true);
              $this->menu($player);
              $player->setNameTag($r.$player->getName());
         }
         elseif($r == $this->yt)
         {
              $player->setAllowFlight(false);
              $this->menu($player);
              $player->setNameTag($r.$player->getName());
         }
         
         
         
         
     }
    
    
     public function onQuit(PlayerQuitEvent $event)
        {
            $player = $event->getPlayer();
            $player->setNameTag($player->getName());
            $event->setQuitMessage("");
            $player->removeAllEffects();
         
         $rank = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
            $r = $rank->get($player->getName());
         
         if($r == $this->titan)
         { unset($this->attachments[$player->getName()]);}
         elseif($r == $this->dragon)
         { unset($this->attachments[$player->getName()]);}
         elseif($r == $this->master)
         { unset($this->attachments[$player->getName()]);}
         elseif($r == $this->king)
         { unset($this->attachments[$player->getName()]);}
         elseif($r == $this->fbi)
         { unset($this->attachments[$player->getName()]);}
         elseif($r == $this->ytmas)
         { unset($this->attachments[$player->getName()]);}
         
         
   
     }
    
    
   /*Seccion de players function*/
    
    public function menu($player)
    {
    $player->getInventory()->clearAll();
        
        
     $player->getInventory()->setItem(0, Item::get(Item::PAPER, 0, 1));
        
     $player->getInventory()->setItem(2, Item::get(Item::BLAZE_POWDER, 0, 1));
        
$player->getInventory()->setItem(4, Item::get(Item::STICK, 0, 1));
        $player->getInventory()->setItem(6, Item::get(Item::NETHER_STAR, 0, 1));
        
        $player->getInventory()->setItem(8, Item::get(Item::BOOK, 0, 1));
        
     return true;   
    }
    
    

   public function lobby($player)
   {
        $this->getServer()->loadLevel("world");
				$this->getServer()->getLevelByName("world")->loadChunk($this->getServer()->getLevelByName("world")->getSafeSpawn()->getFloorX(), $this->getServer()->getLevelByName("world")->getSafeSpawn()->getFloorZ());
$player->setGamemode(0);
$player->teleport($this->getServer()->getLevelByName("world")->getSafeSpawn(),0,0);
       $player->sendMessage($this->prefix." §6Regreso al Lobby");
       return true;
   }
    
     public function sky($player)
   {
        $this->getServer()->loadLevel("HubSky");
				$this->getServer()->getLevelByName("HubSky")->loadChunk($this->getServer()->getLevelByName("HubSky")->getSafeSpawn()->getFloorX(), $this->getServer()->getLevelByName("HubSky")->getSafeSpawn()->getFloorZ());
$player->setGamemode(0);
$player->teleport($this->getServer()->getLevelByName("HubSky")->getSafeSpawn(),0,0);
         $player->sendTitle("§aHub SkyWars"); 
    
       return true;
   }
   
    
    /*Commands*/
    
     public function onCommand(CommandSender $player, Command $cmd, $label, array $args) {
        switch($cmd->getName()){
            case "setrank":
                if($player->isOp())
                {
                    if(!empty($args[0]))
                    {
                        if(!empty($args[1]))
                        {
     $rank = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
                            if($args[0]=="titan")
                            {$r = $this->titan;}
                            elseif($args[0]=="dragon")
                            {$r = $this->dragon;}
                            elseif($args[0]=="master")
                            {$r = $this->master;}
                            elseif($args[0]=="king")
                            {$r = $this->king;}
                            elseif($args[0]=="yt")
                            {$r = $this->yt;}
                            elseif($args[0]=="yt+")
                            {$r = $this->ytmas;}
                            elseif($args[0]=="cr")
                            {$r = $this->cr;}
                            elseif($args[0]=="admin")
                            {$r = $this->admin;}
                            elseif($args[0]=="fbi")
                            {$r = $this->fbi;}
                            else
                            {
                                goto fin;
                            }
                            
                            
                           $rank->set($args[1], $r);
                                     $rank->save();
                                                        fin:
                        }
                    }
                }
                            
                            
                            
                            
            
                return true;
            case "dtp":
                if(!empty($args[0]))
                {
                    if($args[0] == "lobby")
                    {
                        $this->lobby($player);
                        $this->menu($player);
                    }
                    elseif($args[0] == "sky")
                    {
                        $this->sky($player);
                    }
                    
                }else{
                    $player->sendMessage($this->prefix." Este args no esta disponible");
                }
                return true;
           
        }
     }
   
 public function onBreak(BlockBreakEvent $event){
            if($event->getPlayer()->getLevel()->getFolderName()=="world")
            {
		
			$event->setCancelled();
		}
     if($event->getPlayer()->getLevel()->getFolderName()=="games")
            {
		
			$event->setCancelled();
		}
     if($event->getPlayer()->getLevel()->getFolderName()=="HubSky")
            {
		
			$event->setCancelled();
		}
            
	}
    
    
    
    
    public function onPlace(BlockPlaceEvent $event){
            if($event->getPlayer()->getLevel()->getFolderName()=="world")
            {
		
			$event->setCancelled();
		}
        if($event->getPlayer()->getLevel()->getFolderName()=="games")
            {
		
			$event->setCancelled();
		}
        if($event->getPlayer()->getLevel()->getFolderName()=="HubSky")
            {
		
			$event->setCancelled();
		}
            
	} 
    public function onPvP(EntityDamageEvent $eventPvP){
    $map = $eventPvP->getEntity()->getLevel()->getFolderName();
    if($map=="world")
    {
        if($eventPvP instanceof EntityDamageByEntityEvent){
            if($eventPvP->getEntity() instanceof Player && $eventPvP->getDamager() instanceof Player){
                
                    $eventPvP->setCancelled();
                
            }
        }
    }
        if($map=="games")
    {
        if($eventPvP instanceof EntityDamageByEntityEvent){
            if($eventPvP->getEntity() instanceof Player && $eventPvP->getDamager() instanceof Player){
                
                    $eventPvP->setCancelled();
                
            }
        }
    }
        if($map=="HubSky")
    {
        if($eventPvP instanceof EntityDamageByEntityEvent){
            if($eventPvP->getEntity() instanceof Player && $eventPvP->getDamager() instanceof Player){
                
                    $eventPvP->setCancelled();
                
            }
        }
    }
    } 
    
    
    
     public function onPlayerInteractEvent(PlayerInteractEvent $event){
         
       if($event->getPlayer()->getLevel()->getFolderName()=="world")
            {
    
        $i = $event->getItem();
		$player = $event->getPlayer();
         
         if($i->getId() == 339)
         {$this->eye($player);}
         elseif($i->getId() == 377)
         {$this->clf($player);}
         elseif($i->getId() == 280)
         {$this->games($player);}
         elseif($i->getId() == 399)
         {$this->info($player);}
         elseif($i->getId() == 340)
         {$this->infoadmin($player);}
       }
       
         
     }
    
    public function eye($player)
    {
        $player->sendMessage($this->prefix." §7Esto estara muy pronto");
        return true;
    }
    public function clf($player)
    {
        $player->sendMessage($this->prefix." §7Esto estara muy pronto"); 
        return true;
    }
    public function games($player)
    {
        
        $this->getServer()->loadLevel("games");
				$this->getServer()->getLevelByName("games")->loadChunk($this->getServer()->getLevelByName("games")->getSafeSpawn()->getFloorX(), $this->getServer()->getLevelByName("games")->getSafeSpawn()->getFloorZ());
$player->setGamemode(0);
$player->teleport($this->getServer()->getLevelByName("games")->getSafeSpawn(),0,0);
       
$player->sendTitle("§aHub Games"); 
        
        
        
       $player->getInventory()->clearAll();
        
        
     
        
        
   
        
        return true;
    }
   
    
    
    public function info($player)
    {
        $player->sendMessage($this->prefix." §aQuieres Comprar §eRango §ao Solicitar Tag §4Y§fT§a? §bahora puedes hacerlo desde la pagina web §f(§6dragoncraft3.000webhostapp.com§f)");
        return true;
    }
    public function infoadmin($player)
    {
       $player->sendMessage("§fRodragon§b >> §6Creador");
        $player->sendMessage("§fLuggi_lol15§b >> §4Admin§f y §4Y§fT");
        $player->sendMessage("§fJose_wowgame§b >> §aProgramador De §dJuegos ");
        $player->sendMessage("§fDroxo§b >> §4Admin§f y §2Builder");
        $player->sendMessage("§fGael§b >> §eFBI§f y§2 Builder");
        $player->sendMessage("§fTodoGamez§b >> §6Creador §fy §4Y§fT");
        $player->sendMessage("§fJosueDroiid §b>> §6Creador §fy §4Y§fT");

        return true;
    }
    
    
    
    
    
    
     public function onChat(PlayerChatEvent $event)
        {
     $player = $event->getPlayer();
     $message = $event->getMessage();
     $rank = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
    
    $event->setFormat("> ".$player->getName()." >> ".$message);
         if($message == "lag")
            {
             $player->kick("No grocerias");   
            }
    
    
    if($rank->get($player->getName()) != null)
		{
			$r = $rank->get($player->getName());
        
        
        
        if($r==$this->titan)
        {
          $event->setFormat($r." §a".$player->getName()." §6>>§e ".$message);   
        }
        elseif($r==$this->dragon)
        {
          $event->setFormat($r." §e".$player->getName()." §6>>§f ".$message);   
        }
        elseif($r==$this->master)
        {
          $event->setFormat($r." §a".$player->getName()." §6>>§b ".$message);   
        }
        elseif($r==$this->king)
        {
          $event->setFormat($r." §e".$player->getName()." §6>>§f ".$message);   
        }
        elseif($r==$this->yt)
        {
          $event->setFormat($r." §e".$player->getName()." §6>>§f ".$message);   
        }
        elseif($r==$this->ytmas)
        {
          $event->setFormat($r." §a".$player->getName()." §6>>§b ".$message);   
        }
        elseif($r==$this->admin)
        {
          $event->setFormat($r." §b".$player->getName()." §6>>§a ".$message);   
        }
        elseif($r==$this->cr)
        {
          $event->setFormat($r." §b".$player->getName()." §6>>§e ".$message);   
        }
        elseif($r==$this->fbi)
        {
          $event->setFormat($r." §a".$player->getName()." §6>>§f ".$message);   
        }
        
   
        
    }
     }
    
    
 
     public function Puntuar(PlayerItemHeldEvent $event) {
            $player = $event->getPlayer();
            $i = $event->getItem();
     if($event->getPlayer()->getLevel()->getFolderName()=="world")
     {
         
      if($i->getId() == 339){
    $player->sendMessage($this->prefix." §6Click para ver las particulas");
     }
         elseif($i->getId() == 377){
    $player->sendMessage($this->prefix." §6Click para usar armadura multicolor");
     }
          elseif($i->getId() == 280){
    $player->sendMessage($this->prefix." §6Click para ir ala zona de juegos");
     }
         elseif($i->getId() == 399)
         {$player->sendMessage($this->prefix." §6Click para ver informacion");}
         elseif($i->getId() == 340)
         {$player->sendMessage($this->prefix." §6Click para ver informacion de los admins");}
         
         
         
     }
         
    }
    
    
    
    
    
    
    
    
    
    
    
    
}
class Task extends PluginTask{
    
    public function __construct($plugin)
	{
		$this->plugin = $plugin;
		parent::__construct($plugin);
	}
        
    public function onRun($tick)
	{
         
       
        
        
    $lobby = $this->plugin->getServer()->getLevelByName("world");  
             
     if($lobby instanceof Level)
				{    
        
         $players = $lobby->getPlayers();
         
         foreach($players as $pl)
         {
             $online = count($lobby->getPlayers());
            $r = rand(1,5); 
        switch($r){
                   
            case 1:
                
            $pl->sendTip("§9Bienvenido a DragonCraft\n§9Jugadores Online: §e".$online);
                break;
            case 2:
                
                $pl->sendTip("§6Bienvenido a DragonCraft\n§6Jugadores Online: §e".$online);
          
                break;
            case 3:
                $pl->sendTip("§aBienvenido a DragonCraft\n§aJugadores Online: §e".$online);
                break;
            case 4:
                 $pl->sendTip("§bBienvenido a DragonCraft\n§bJugadores Online: §e".$online);
                break;
            case 5:
                 $pl->sendTip("§4Bienvenido a DragonCraft\n§4Jugadores Online: §e".$online);
                break;
             
  
}
             
         }
         
         
            
             
             
         }
         
        
     }
        
        
    }
    
    
    
    
    
    
    
    

