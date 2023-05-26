package Annihilation.Arena.Task;

import Annihilation.Annihilation;
import Annihilation.Arena.Arena;
import Annihilation.Arena.BlockEntity.EnderBrewing;
import Annihilation.Arena.BlockEntity.EnderFurnace;
import cn.nukkit.block.Block;
import cn.nukkit.blockentity.BlockEntity;
import cn.nukkit.blockentity.BlockEntityChest;
import cn.nukkit.item.Item;
import cn.nukkit.math.Vector3;
import cn.nukkit.nbt.tag.CompoundTag;
import cn.nukkit.nbt.tag.ListTag;
import cn.nukkit.scheduler.Task;

import java.util.HashMap;


public class BlocksSetTask extends Task {

    private HashMap<String, Vector3> data;
    private Arena plugin;

    public BlocksSetTask(Arena plugin, HashMap<String, Vector3> data) {
        this.data = data;
        this.plugin = plugin;

        Annihilation.getInstance().getServer().getScheduler().scheduleDelayedTask(this, 100);
    }

    public void onRun(int currentTick) {
        Vector3 brewingShopData = this.data.get("brewing");
        Vector3 weaponShopData = this.data.get("weapons");

        plugin.level.setBlock(brewingShopData, Block.get(Item.CHEST));
        plugin.level.setBlock(weaponShopData, Block.get(Item.CHEST));
    }
}
