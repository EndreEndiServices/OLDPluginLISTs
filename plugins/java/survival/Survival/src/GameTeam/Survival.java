package GameTeam;

import GTCore.MTCore;
import GameTeam.MySQL.JoinQuery;
import GameTeam.Object.PlayerData;
import cn.nukkit.Player;
import cn.nukkit.block.Block;
import cn.nukkit.event.EventHandler;
import cn.nukkit.event.Listener;
import cn.nukkit.event.player.PlayerInteractEvent;
import cn.nukkit.event.player.PlayerJoinEvent;
import cn.nukkit.event.player.PlayerQuitEvent;
import cn.nukkit.plugin.PluginBase;

import java.util.HashMap;

public class Survival extends PluginBase implements Listener{

    public HashMap<String, PlayerData> players = new HashMap<>();
    public MTCore mtcore;

    @Override
    public void onEnable(){
        getLogger().info("§aSurvival ENABLED!");
        getServer().getPluginManager().registerEvents(this, this);
        mtcore = (MTCore) getServer().getPluginManager().getPlugin("GTCore");
    }

    public static String getPrefix(){
        return MTCore.getPrefix();
    }

    @EventHandler
    public void onJoin(PlayerJoinEvent e){
        new JoinQuery(this, e.getPlayer().getName().toLowerCase());
        PlayerData pl = new PlayerData(e.getPlayer());
        players.put(e.getPlayer().getName().toLowerCase(), pl);
    }

    @EventHandler
    public void onLeave(PlayerQuitEvent e){
        players.remove(e.getPlayer().getName().toLowerCase());
    }

    @EventHandler
    public void onTouch(PlayerInteractEvent e){
        Player p = e.getPlayer();
        PlayerData pl = players.get(p.getName().toLowerCase());
        Block b = e.getBlock();
        p.sendMessage(getPrefix() + (b.getFloorX() + ":" + b.getFloorY() + ":" + b.getFloorZ()));
        switch (b.getFloorX() + ":" + b.getFloorY() + ":" + b.getFloorZ()){
            case "X:Y:Z":

                break;
        }
    }








}
