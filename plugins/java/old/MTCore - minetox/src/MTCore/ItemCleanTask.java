package MTCore;

import cn.nukkit.Player;
import cn.nukkit.entity.Entity;
import cn.nukkit.entity.item.EntityItem;
import cn.nukkit.entity.item.EntityXPOrb;
import cn.nukkit.entity.projectile.EntityArrow;
import cn.nukkit.level.Level;
import cn.nukkit.scheduler.Task;
import cn.nukkit.utils.TextFormat;

import java.util.ArrayList;


public class ItemCleanTask extends Task {

    private MTCore plugin;

    public ItemCleanTask(MTCore plugin){
        this.plugin = plugin;
    }

    @Override
    public void onRun(int currentTick){
        int count = 0;

        for(Level level : this.plugin.getServer().getLevels().values()){
            for(Entity ent : level.getEntities()){
                if(ent.getNetworkId() == EntityArrow.NETWORK_ID || ent.getNetworkId() == EntityItem.NETWORK_ID  || ent.getNetworkId() == EntityXPOrb.NETWORK_ID){
                    count++;
                    ent.close();
                }
            }
        }

        for(Player p : new ArrayList<>(this.plugin.getServer().getOnlinePlayers().values())){
            p.sendMessage(MTCore.getPrefix() + TextFormat.GREEN + "Removed " + count + " items.");
        }
        this.plugin.getLogger().info(MTCore.getPrefix() + TextFormat.GREEN + "Removed " + count + " items.");
    }
}
