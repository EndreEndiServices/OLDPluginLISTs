package MTCore;

import cn.nukkit.item.Item;
import cn.nukkit.scheduler.Task;
import cn.nukkit.Player;

public class JoinDelay extends Task
{

    private MTCore plugin;
    private Player player;

    public JoinDelay(MTCore plugin, Player p)
    {
        this.plugin = plugin;
        this.player = p;
    }

    @Override
    public void onRun(int currentTick)
    {
        if (!player.isOnline()) {
            return;
        }

        player.teleport(plugin.lobby);
        player.setRotation(270, 0);
        player.setHealth(20);
        //if(count($this->plugin->getServer()->getOnlinePlayers()) >= 18 && !$this->player->isOp()){
        //$this->plugin->ff->transferPlayer($this->player, "93.91.250.135", 27494, null);
        //}
        if (player.getInventory() != null) {
            player.getInventory().clearAll();
            player.getInventory().setItem(0, Item.get(Item.CLOCK, 0, 1));
            player.getInventory().setItem(1, Item.get(Item.GOLD_INGOT, 0, 1));
            player.getInventory().setHotbarSlotIndex(0, 0);
            player.getInventory().setHotbarSlotIndex(1, 1);
            player.getInventory().sendContents(player);
        }
    }
}