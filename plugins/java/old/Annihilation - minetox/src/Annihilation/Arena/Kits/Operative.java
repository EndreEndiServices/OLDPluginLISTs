package Annihilation.Arena.Kits;

import Annihilation.Annihilation;
import Annihilation.Arena.Kits.Task.OperativeTask;
import cn.nukkit.Player;
import Annihilation.Arena.Arena;
import cn.nukkit.Server;
import cn.nukkit.block.Block;
import cn.nukkit.item.Item;
import cn.nukkit.utils.TextFormat;

import java.util.HashMap;

public class Operative{

    private static HashMap<String, Block> blocks = new HashMap<>();
    
    public static boolean placed(Player p){
        return Operative.blocks.containsKey(p.getName().toLowerCase());
    }
    
    public static void onPlace(Player p, Block b){
        p.sendMessage(Annihilation.getPrefix()+ TextFormat.YELLOW+"You will be teleported back in 30 seconds...");
        Operative.blocks.put(p.getName().toLowerCase(), b);
        Server.getInstance().getScheduler().scheduleDelayedTask(new OperativeTask(p, b), 600);
    }
    
    public static void execute(Player p, Block b){
        if(Arena.getInstance().phase <= 0){
            return;
        }

        Item item = Item.get(Item.SOUL_SAND, 0, 1);

        Operative.blocks.remove(p.getName().toLowerCase());
        Arena.getInstance().level.setBlock(b, Block.get(0), true);
        if(Arena.getInstance().inArena(p) && !Arena.getInstance().inLobby(p)){
            p.getInventory().addItem(item);
            p.getInventory().sendContents(p);
            p.teleport(b);
        }
    }
}