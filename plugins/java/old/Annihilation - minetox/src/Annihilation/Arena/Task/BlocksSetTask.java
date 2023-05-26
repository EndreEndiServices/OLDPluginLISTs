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


public class BlocksSetTask extends Task{

    private HashMap<String, Vector3> data;
    private Arena plugin;

    public BlocksSetTask(Arena plugin, HashMap<String, Vector3> data){
        this.data = data;
        this.plugin = plugin;

        Annihilation.getInstance().getServer().getScheduler().scheduleDelayedTask(this, 100);
    }

    public void onRun(int currentTick){
        Vector3 brewingShopData = this.data.get("brewing");
        Vector3 weaponShopData = this.data.get("weapons");

        plugin.level.setBlock(brewingShopData, Block.get(Item.CHEST));
        plugin.level.setBlock(weaponShopData, Block.get(Item.CHEST));

        CompoundTag nbt = new CompoundTag()
                .putList(new ListTag<>("Items"))
                .putString("id", BlockEntity.CHEST)
                .putInt("x", (int) brewingShopData.x)
                .putInt("y", (int) brewingShopData.y)
                .putInt("z", (int) brewingShopData.z)
                .putString("CustomName", "Brewing Shop");

        BlockEntityChest brewingShop = new BlockEntityChest(this.plugin.level.getChunk((int) brewingShopData.x >> 4, (int) brewingShopData.z >> 4), nbt);

        Item[][] items = new Item[][]{{Item.get(Item.BREWING_STAND), Item.get(Item.GOLD_INGOT, 0, 10)}, {Item.get(374, 0, 3), Item.get(Item.GOLD_INGOT, 0, 1)}, {Item.get(372), Item.get(Item.GOLD_INGOT, 0, 5)}, {Item.get(331), Item.get(Item.GOLD_INGOT, 0, 3)}, {Item.get(376), Item.get(Item.GOLD_INGOT, 0, 3)}, {Item.get(378), Item.get(Item.GOLD_INGOT, 0, 2)}, {Item.get(353), Item.get(Item.GOLD_INGOT, 0, 2)}, {Item.get(382), Item.get(Item.GOLD_INGOT, 0, 2)}, {Item.get(370), Item.get(Item.GOLD_INGOT, 0, 15)}, {Item.get(396), Item.get(Item.GOLD_INGOT, 0, 2)}, {Item.get(375), Item.get(Item.GOLD_INGOT, 0, 2)}, {Item.get(377), Item.get(Item.GOLD_INGOT, 0, 15)}, {Item.get(Item.GUNPOWDER), Item.get(Item.GOLD_INGOT, 0, 30)}};

        int slot = 0;
        for(Item[] item : items){
            brewingShop.getInventory().setItem(slot, item[0]);
            slot++;
            brewingShop.getInventory().setItem(slot, item[1]);
            slot++;
        }

        CompoundTag nbt1 = new CompoundTag()
                .putList(new ListTag<>("Items"))
                .putString("id", BlockEntity.CHEST)
                .putInt("x", (int) weaponShopData.x)
                .putInt("y", (int) weaponShopData.y)
                .putInt("z", (int) weaponShopData.z)
                .putString("CustomName", "Weapon Shop");

        BlockEntityChest weaponsShop = new BlockEntityChest(this.plugin.level.getChunk((int) this.data.get("chest").x >> 4, (int) this.data.get("chest").z >> 4), nbt1);

        items = new Item[][]{{Item.get(Item.IRON_HELMET), Item.get(Item.GOLD_INGOT, 0, 10)}, {Item.get(Item.IRON_CHESTPLATE), Item.get(Item.GOLD_INGOT, 0, 18)}, {Item.get(Item.IRON_LEGGINGS), Item.get(Item.GOLD_INGOT, 0, 14)}, {Item.get(Item.IRON_BOOTS), Item.get(Item.GOLD_INGOT, 0, 8)}, {Item.get(Item.IRON_SWORD), Item.get(Item.GOLD_INGOT, 0, 5)}, {Item.get(Item.BOW), Item.get(Item.GOLD_INGOT, 0, 5)}, {Item.get(Item.ARROW, 0, 16), Item.get(Item.GOLD_INGOT, 0, 5)}, {Item.get(Item.CAKE), Item.get(Item.GOLD_INGOT, 0, 5)}, {Item.get(Item.MELON, 0, 16), Item.get(Item.GOLD_INGOT, 0, 1)}};

        slot = 0;
        for(Item[] item : items){
            weaponsShop.getInventory().setItem(slot, item[0]);
            slot++;
            weaponsShop.getInventory().setItem(slot, item[1]);
            slot++;
        }

        Vector3 furnaceData = this.data.get("furnace");

        CompoundTag nbt2 = new CompoundTag()
                .putList(new ListTag<>("Items"))
                .putString("id", BlockEntity.FURNACE)
                .putInt("x", (int) furnaceData.x)
                .putInt("y", (int) furnaceData.y)
                .putInt("z", (int) furnaceData.z)
                .putString("CustomName", "Ender Furnace");

        EnderFurnace furnace = new EnderFurnace(plugin.level.getChunk((int) furnaceData.x >> 4, (int) furnaceData.z >> 4), nbt2);

        Vector3 brewingData = this.data.get("enderbrewing");

        CompoundTag nbt3 = new CompoundTag()
                .putList(new ListTag<>("Items"))
                .putString("id", BlockEntity.BREWING_STAND)
                .putInt("x", (int) brewingData.x)
                .putInt("y", (int) brewingData.y)
                .putInt("z", (int) brewingData.z)
                .putString("CustomName", "Ender Brewing");

        EnderBrewing brewing = new EnderBrewing(plugin.level.getChunk((int) brewingData.x >> 4, (int) brewingData.z >> 4), nbt3);

        Vector3 chestData = this.data.get("chest");

        CompoundTag nbt4 = new CompoundTag()
                .putList(new ListTag<>("Items"))
                .putString("id", BlockEntity.CHEST)
                .putInt("x", (int) chestData.x)
                .putInt("y", (int) chestData.y)
                .putInt("z", (int) chestData.z)
                .putString("CustomName", "Ender Chest");

        new BlockEntityChest(plugin.level.getChunk((int) brewingData.x >> 4, (int) brewingData.z >> 4), nbt4);
    }
}
