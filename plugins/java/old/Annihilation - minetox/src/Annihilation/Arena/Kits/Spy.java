package Annihilation.Arena.Kits;

import Annihilation.Arena.Kits.Task.SpyTask;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.item.Item;
import cn.nukkit.utils.TextFormat;

import java.util.HashSet;

public class Spy{

    public static Item[] items;
    public static HashSet<String> players = new HashSet<>();

    public static void onSneak(Player p){
        Server.getInstance().getScheduler().scheduleDelayedTask(new SpyTask(p), 100);
        Spy.players.add(p.getName().toLowerCase());
        p.sendMessage(TextFormat.YELLOW+"You are now invisible");
    }

    public static void onUnsneak(Player p){
        if(Spy.players.contains(p.getName().toLowerCase())){
            Spy.players.remove(p.getName().toLowerCase());
            p.spawnToAll();
            p.sendMessage(TextFormat.YELLOW+"You are no longer invisible.");
        }
    }
}