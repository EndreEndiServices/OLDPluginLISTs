package Annihilation.Arena.Object;

import Annihilation.Arena.Arena;
import Annihilation.Arena.BlockEntity.EnderBrewing;
import Annihilation.Arena.BlockEntity.EnderFurnace;
import Annihilation.Arena.Task.BlocksSetTask;
import cn.nukkit.block.Block;
import cn.nukkit.blockentity.BlockEntity;
import cn.nukkit.blockentity.BlockEntityChest;
import cn.nukkit.item.*;
import cn.nukkit.level.Position;
import cn.nukkit.math.Vector3;
import cn.nukkit.nbt.tag.CompoundTag;
import cn.nukkit.nbt.tag.ListTag;
import cn.nukkit.Player;
import cn.nukkit.utils.TextFormat;
import lombok.Getter;

import java.util.HashMap;
import java.util.Map;

public class Team {

    private String name;
    private String color;
    private int id;

    private HashMap<String, Player> players = new HashMap<>();

    private Nexus nexus;

    //private HashMap<String, Vector3> data = new HashMap<>();

    //private Arena plugin;

    private Position spawn;

    private BlockEntityChest brewingShop;

    private BlockEntityChest weaponsShop;

    private EnderFurnace furnace;

    private EnderBrewing brewing;

    private BlockEntityChest chest;

    private int decColor;

    private Item[] soulBound;

    @Getter
    private String status;

    public Team(int id, String name, String color, Arena plugin, int decColor) {
        this.id = id;
        this.name = name;
        this.color = color;
        this.decColor = decColor;

        CompoundTag nbt = new CompoundTag()
                .putInt("customColor", getDecimalColor())
                .putCompound("display", new CompoundTag().putString("Name", TextFormat.RESET + TextFormat.GOLD + "SoulBound"));

        ItemHelmetLeather helmet = new ItemHelmetLeather();
        helmet.setCompoundTag(nbt);

        ItemChestplateLeather chestplate = new ItemChestplateLeather();
        chestplate.setCompoundTag(nbt);

        ItemLeggingsLeather leggings = new ItemLeggingsLeather();
        leggings.setCompoundTag(nbt);

        ItemBootsLeather boots = new ItemBootsLeather();
        boots.setCompoundTag(nbt);

        soulBound = new Item[]{helmet, chestplate, leggings, boots};
    }

    public void setData(HashMap<String, Vector3> data, Arena plugin) {
        this.nexus = new Nexus(this, new Position(data.get("nexus").x, data.get("nexus").y, data.get("nexus").z, plugin.level));
        recalculateStatus();

        this.spawn = new Position(data.get("spawn").x, data.get("spawn").y, data.get("spawn").z, plugin.level);

        /*new BlocksSetTask(plugin, data);
        Vector3 brewingShopData = data.get("brewing");
        Vector3 weaponShopData = data.get("weapons");

        CompoundTag nbt = new CompoundTag()
                .putList(new ListTag<>("Items"))
                .putString("id", BlockEntity.CHEST)
                .putInt("x", (int) brewingShopData.x)
                .putInt("y", (int) brewingShopData.y)
                .putInt("z", (int) brewingShopData.z)
                .putString("CustomName", "Brewing Shop");

        BlockEntityChest brewingShop = new BlockEntityChest(plugin.level.getChunk((int) brewingShopData.x >> 4, (int) brewingShopData.z >> 4), nbt);

        Item[] items = new Item[]{Item.get(Item.BREWING_STAND), Item.get(Item.GOLD_INGOT, 0, 10), Item.get(374, 0, 3), Item.get(Item.GOLD_INGOT, 0, 1), Item.get(372), Item.get(Item.GOLD_INGOT, 0, 5), Item.get(331), Item.get(Item.GOLD_INGOT, 0, 3), Item.get(376), Item.get(Item.GOLD_INGOT, 0, 3), Item.get(378), Item.get(Item.GOLD_INGOT, 0, 2), Item.get(353), Item.get(Item.GOLD_INGOT, 0, 2), Item.get(382), Item.get(Item.GOLD_INGOT, 0, 2), Item.get(370), Item.get(Item.GOLD_INGOT, 0, 15), Item.get(396), Item.get(Item.GOLD_INGOT, 0, 2), Item.get(375), Item.get(Item.GOLD_INGOT, 0, 2), Item.get(377), Item.get(Item.GOLD_INGOT, 0, 15), Item.get(Item.GUNPOWDER), Item.get(Item.GOLD_INGOT, 0, 30)};

        int slot = 0;
        for (Item item : items) {
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

        BlockEntityChest weaponsShop = new BlockEntityChest(plugin.level.getChunk((int) weaponShopData.x >> 4, (int) weaponShopData.z >> 4), nbt1);

        items = new Item[]{Item.get(Item.IRON_HELMET), Item.get(Item.GOLD_INGOT, 0, 10), Item.get(Item.IRON_CHESTPLATE), Item.get(Item.GOLD_INGOT, 0, 18), Item.get(Item.IRON_LEGGINGS), Item.get(Item.GOLD_INGOT, 0, 14), Item.get(Item.IRON_BOOTS), Item.get(Item.GOLD_INGOT, 0, 8), Item.get(Item.IRON_SWORD), Item.get(Item.GOLD_INGOT, 0, 5), Item.get(Item.BOW), Item.get(Item.GOLD_INGOT, 0, 5), Item.get(Item.ARROW, 0, 16), Item.get(Item.GOLD_INGOT, 0, 5), Item.get(Item.CAKE), Item.get(Item.GOLD_INGOT, 0, 5), Item.get(Item.MELON, 0, 16), Item.get(Item.GOLD_INGOT, 0, 1)};

        slot = 0;
        for (Item[] item : items) {
            weaponsShop.getInventory().setItem(slot, item[0]);
            slot++;
            weaponsShop.getInventory().setItem(slot, item[1]);
            slot++;
        }*/
        Vector3 furnaceData = data.get("furnace");

        /*boolean remove = true;

        while (remove) {
            //System.out.println("\nremoving furnace");
            BlockEntity be = plugin.level.getBlockEntity(furnaceData);

            if (be != null) {
                be.close();
            } else {
                remove = false;
            }
        }*/

        CompoundTag nbt4 = new CompoundTag()
                .putList(new ListTag<>("Items"))
                .putString("id", BlockEntity.FURNACE)
                .putInt("x", (int) furnaceData.x)
                .putInt("y", (int) furnaceData.y)
                .putInt("z", (int) furnaceData.z)
                .putString("CustomName", "Ender Furnace");

        this.furnace = new EnderFurnace(plugin.level.getChunk((int) furnaceData.x >> 4, (int) furnaceData.z >> 4), nbt4);

        Vector3 brewingData = data.get("enderbrewing");

        /*remove = true;

        while (remove) {
            //System.out.println("\nremoving brewing");
            BlockEntity be = plugin.level.getBlockEntity(brewingData);

            if (be != null) {
                be.close();
            } else {
                remove = false;
            }
        }*/

        CompoundTag nbt2 = new CompoundTag()
                .putList(new ListTag<>("Items"))
                .putString("id", BlockEntity.BREWING_STAND)
                .putInt("x", (int) brewingData.x)
                .putInt("y", (int) brewingData.y)
                .putInt("z", (int) brewingData.z)
                .putString("CustomName", "Ender Brewing");

        this.brewing = new EnderBrewing(plugin.level.getChunk((int) brewingData.x >> 4, (int) brewingData.z >> 4), nbt2);

        Vector3 chestData = data.get("chest");

        /*remove = true;

        while (remove) {
            //System.out.println("\nremoving chest");
            BlockEntity be = plugin.level.getBlockEntity(chestData);

            if (be != null) {
                plugin.level.removeBlockEntity(be);
            } else {
                remove = false;
            }
        }*/

        CompoundTag nbt3 = new CompoundTag()
                .putList(new ListTag<>("Items"))
                .putString("id", BlockEntity.CHEST)
                .putInt("x", (int) chestData.x)
                .putInt("y", (int) chestData.y)
                .putInt("z", (int) chestData.z)
                .putString("CustomName", "Ender Chest");

        this.chest = new BlockEntityChest(plugin.level.getChunk((int) brewingData.x >> 4, (int) brewingData.z >> 4), nbt3);
    }

    public int getId() {
        return id;
    }

    public void addPlayer(Player p) {
        players.put(p.getName().toLowerCase(), p);
        //p.setNameTag(getColor() + p.getName() + TextFormat.WHITE);
        //p.setDisplayName(plugin.mtcore.getDisplayRank(p) + " " + getColor() + p.getName() + TextFormat.WHITE);
    }

    public void removePlayer(Player p) {
        players.remove(p.getName().toLowerCase());
    }

    public String getName() {
        return name;
    }

    public String getColor() {
        return color;
    }

    public Nexus getNexus() {
        return nexus;
    }

    public boolean isAlive() {
        return players.size() > 0;
    }

    public HashMap<String, Player> getPlayers() {
        return players;
    }

    public void message(String message) {
        message(message, null, null);
    }

    public void message(String message, Player player, PlayerData data) {
        if (player == null) {
            for (Map.Entry<String, Player> entry : getPlayers().entrySet()) {
                entry.getValue().sendMessage(message);
            }
            return;
        }

        String msg = TextFormat.GRAY + "[" + color + "Team" + TextFormat.GRAY + "]   " + player.getDisplayName() + TextFormat.GRAY + " > " + data.getBaseData().getChatColor() + message;

        for (Map.Entry<String, Player> entry : getPlayers().entrySet()) {
            String color = getColor();
            entry.getValue().sendMessage(msg);
        }
    }

    public Position getSpawnLocation() {
        return spawn;
    }

    public BlockEntityChest getBrewingShop() {
        return brewingShop;
    }

    public BlockEntityChest getWeaponsShop() {
        return weaponsShop;
    }

    public EnderFurnace getEnderFurnace() {
        return this.furnace;
    }

    public EnderBrewing getEnderBrewing() {
        return this.brewing;
    }

    public BlockEntityChest getEnderChest() {
        return chest;
    }

    public int getDecimalColor() {
        return decColor;
    }

    public Item[] getArmor() {
        return soulBound;
    }

    public void recalculateStatus() {
        if (getNexus().getHealth() <= 0) {
            status = "";
            return;
        }

        String space = "\n                                                ";
        String name = getColor() + getName().substring(0, 1).toUpperCase() + getName().substring(1) + " Nexus";

        String nexus = new String(new char[16 - name.length()]).replace("\0", " ") + TextFormat.RED + getNexus().getHealth();

        status = space + name + nexus;
    }
}