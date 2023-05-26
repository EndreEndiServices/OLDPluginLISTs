package Annihilation.Arena.Manager;

import Annihilation.Arena.Arena;
import Annihilation.Arena.BlockEntity.EnderFurnace;
import Annihilation.Arena.Inventory.EnderBrewingInventory;
import Annihilation.Arena.Inventory.EnderFurnaceInventory;
import Annihilation.Arena.Object.PlayerData;
import cn.nukkit.blockentity.BlockEntity;
import cn.nukkit.blockentity.BlockEntityChest;
import cn.nukkit.inventory.ChestInventory;
import cn.nukkit.Player;
import cn.nukkit.math.Vector3;
import cn.nukkit.nbt.tag.CompoundTag;
import cn.nukkit.nbt.tag.ListTag;

public class EnderManager {

    public Arena plugin;

    private EnderFurnace furnace;

    public EnderManager(Arena plugin) {
        this.plugin = plugin;
    }

    public ChestInventory createChest(Player p) {
        /*int id = this.plugin.getPlayerTeam(p).getId();

        Vector3 data = (Vector3) this.plugin.data.get(id+"Chest");

        CompoundTag nbt = new CompoundTag()
                .putList(new ListTag<>("Items"))
                .putString("id", BlockEntity.CHEST)
                .putInt("x", (int) data.x)
                .putInt("y", (int) data.y)
                .putInt("z", (int) data.z)
                .putString("CustomName", "Ender Chest");

        BlockEntityChest chest = new BlockEntityChest(this.plugin.level.getChunk((int) data.x >> 4, (int) data.z >> 4), nbt);

        ChestInventory inv = new ChestInventory(chest);
        this.plugin.getPlayerData(p).setChest(inv);*/

        PlayerData data = plugin.getPlayerData(p);
        ChestInventory inv = new ChestInventory(data.getTeam().getEnderChest());
        data.setChest(inv);

        return inv;
    }

    public EnderFurnaceInventory createFurnace(Player p) {
        PlayerData data = plugin.getPlayerData(p);

        EnderFurnaceInventory inv = new EnderFurnaceInventory(data.getTeam().getEnderFurnace());

        data.setFurnace(inv);

        /*CompoundTag nbt = new CompoundTag()
                .putList(new ListTag<>("Items"))
                .putString("id", BlockEntity.FURNACE)
                .putInt("x", (int) data.x)
                .putInt("y", (int) data.y)
                .putInt("z", (int) data.z)
                .putString("CustomName", "Ender Furnace");

        EnderFurnace furnace = new EnderFurnace(this.plugin.level.getChunk((int) data.x >> 4, (int) data.z >> 4), nbt);*/

        //this.plugin.getPlayerData(p).setFurnace(furnace);

        return inv;
    }

    public EnderBrewingInventory createBrewing(Player p) {
        PlayerData data = plugin.getPlayerData(p);
        EnderBrewingInventory inv = new EnderBrewingInventory(data.getTeam().getEnderBrewing());
        data.setBrewing(inv);

        return inv;
    }
}