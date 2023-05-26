package Annihilation.Arena.Manager;

import Annihilation.Arena.Arena;
import Annihilation.Arena.BossTask;
import cn.nukkit.blockentity.BlockEntity;
import cn.nukkit.blockentity.BlockEntityChest;
import cn.nukkit.entity.Entity;
import cn.nukkit.entity.EntityHuman;
import cn.nukkit.level.Level;
import cn.nukkit.level.format.generic.BaseFullChunk;
import cn.nukkit.math.Vector3;
import cn.nukkit.utils.TextFormat;
import cn.nukkit.item.Item;
import cn.nukkit.block.Block;
import cn.nukkit.nbt.tag.CompoundTag;
import cn.nukkit.nbt.tag.DoubleTag;
import cn.nukkit.nbt.tag.ListTag;
import cn.nukkit.nbt.tag.FloatTag;
import cn.nukkit.Server;
import cn.nukkit.item.enchantment.Enchantment;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.Random;

public class BossManager {

    public Arena plugin;
    public BossTask task1;
    public BossTask task2;

    //Boolean[] bosses = new Boolean[]{false, false};

    public BossManager(Arena arena) {
        plugin = arena;
    }

    public void spawnBoss(int boss) {
        //bosses[boss - 1] = true;

        if (plugin.phase >= 3) {
            plugin.messageAllPlayers(TextFormat.GRAY + "================[ " + TextFormat.DARK_AQUA + "Boss" + TextFormat.GRAY + " ]================\n"
                    + getBossName(boss) + TextFormat.GRAY + " se respawnul! Nevahej a zabij ho jako prvni!\n"
                    + TextFormat.GRAY + "=======================================");
        }

        Vector3 pos = (Vector3) plugin.data.get("boss" + boss + "pos");
        if (!plugin.level.isChunkLoaded((int) pos.x >> 4, (int) pos.z >> 4)) {
            plugin.level.loadChunk((int) pos.x >> 4, (int) pos.z >> 4);
        }

        BaseFullChunk chunk = plugin.level.getChunk((int) pos.x >> 4, (int) pos.z >> 4);

        String name = (String) plugin.data.get("boss" + boss + "name");
        Entity golem = Entity.createEntity("IronGolem", chunk, getNbt().putString("BossName", name));

        golem.setPosition(pos);
        //golem.setNameTag(name);
        golem.spawnToAll();
    }

    public CompoundTag getNbt() {
        return new CompoundTag()
                .putList(new ListTag<DoubleTag>("Pos")
                        .add(new DoubleTag("", 0))
                        .add(new DoubleTag("", 0))
                        .add(new DoubleTag("", 0)))
                .putList(new ListTag<DoubleTag>("Motion")
                        .add(new DoubleTag("", 0))
                        .add(new DoubleTag("", 0))
                        .add(new DoubleTag("", 0)))

                .putList(new ListTag<FloatTag>("Rotation")
                        .add(new FloatTag("", (float) 0))
                        .add(new FloatTag("", (float) 0)))
                .putShort("Health", 200);
    }

    public void spawnChest(Vector3 pos) {
        Level level = this.plugin.level;
        BlockEntityChest tile = (BlockEntityChest) level.getBlockEntity(pos);
        if (tile == null) {
            level.setBlock(pos, Block.get(54, 0), true);
            CompoundTag nbt = new CompoundTag()
                    .putList(new ListTag<>("Items"))
                    .putString("id", BlockEntity.CHEST)
                    .putDouble("x", pos.x)
                    .putDouble("y", pos.y)
                    .putDouble("z", pos.z);

            tile = new BlockEntityChest(this.plugin.level.getChunk((int) pos.x >> 4, (int) pos.z >> 4), nbt);
        }
        tile.getInventory().setItem(new Random().nextInt(27), this.getDrop());
    }

    public Item getDrop() {
        Item item;
        Enchantment ench;

        switch (new Random().nextInt(7) + 1) {
            case 1:
                item = Item.get(310, 0, 1);
                ench = Enchantment.getEnchantment(0);
                ench.setLevel(2);
                item.addEnchantment(ench);
                ench = Enchantment.getEnchantment(6);
                ench.setLevel(4);
                item.addEnchantment(ench);
                item.setCustomName("Oxyger I");
                return item;
            case 2:
                item = Item.get(311, 0, 1);
                ench = Enchantment.getEnchantment(0);
                ench.setLevel(4);
                item.addEnchantment(ench);
                ench = Enchantment.getEnchantment(6);
                ench.setLevel(4);
                item.addEnchantment(ench);
                item.setCustomName("Oxyger II");
                return item;
            case 3:
                item = Item.get(311, 0, 1);
                ench = Enchantment.getEnchantment(0);
                ench.setLevel(4);
                item.addEnchantment(ench);
                ench = Enchantment.getEnchantment(6);
                ench.setLevel(4);
                item.addEnchantment(ench);
                item.setCustomName("TrollPlate");
                return item;
            case 4:
                item = Item.get(312, 0, 1);
                ench = Enchantment.getEnchantment(0);
                ench.setLevel(4);
                item.addEnchantment(ench);
                ench = Enchantment.getEnchantment(5);
                ench.setLevel(2);
                item.addEnchantment(ench);
                item.setCustomName("Antisharp leggs");
                return item;
            case 5:
                item = Item.get(313, 0, 1);
                ench = Enchantment.getEnchantment(0);
                ench.setLevel(4);
                item.addEnchantment(ench);
                ench = Enchantment.getEnchantment(2);
                ench.setLevel(4);
                item.addEnchantment(ench);
                item.setCustomName("Fly Boots");
                return item;
            case 6:
                item = Item.get(276, 0, 1);
                ench = Enchantment.getEnchantment(9);
                ench.setLevel(3);
                item.addEnchantment(ench);
                ench = Enchantment.getEnchantment(17);
                ench.setLevel(3);
                item.addEnchantment(ench);
                ench = Enchantment.getEnchantment(12);
                ench.setLevel(2);
                item.addEnchantment(ench);
                ench = Enchantment.getEnchantment(13);
                ench.setLevel(2);
                item.addEnchantment(ench);
                item.setCustomName("Blood Finger");
                return item;
            case 7:
                item = Item.get(261, 0, 1);
                ench = Enchantment.getEnchantment(Enchantment.ID_BOW_POWER);
                ench.setLevel(3);
                item.addEnchantment(ench);
                ench = Enchantment.getEnchantment(Enchantment.ID_BOW_FLAME);
                ench.setLevel(1);
                item.addEnchantment(ench);
                ench = Enchantment.getEnchantment(Enchantment.ID_DURABILITY);
                ench.setLevel(1);
                item.addEnchantment(ench);
                ench = Enchantment.getEnchantment(20);
                ench.setLevel(2);
                item.addEnchantment(ench);
                item.setCustomName("Rapid Shooter");
                return item;
        }

        return Item.get(0);
    }

    public void onBossDeath(int boss, String pname) {
        /*if(!bosses[boss - 1]){
            return;
        }

        bosses[boss - 1] = false;*/

        this.spawnChest((Vector3) this.plugin.data.get("boss" + boss + "chest"));
        Server.getInstance().getScheduler().scheduleDelayedTask(this.task1 = new BossTask(this, boss), 12000);
        /*Server.getInstance().getScheduler().scheduleDelayedTask(new Runnable() {
            @Override
            public void run() {
                spawnBoss(boss);
            }
        }, 12000);*/

        this.plugin.messageAllPlayers(TextFormat.GRAY + "===========[ " + TextFormat.DARK_AQUA + "Boss Zabit" + TextFormat.GRAY + " ]===========\n"
                + this.getBossName(boss) + TextFormat.GRAY + " byl zabit hracem " + pname + "\n"
                + TextFormat.GRAY + "==================================");
    }

    public String getBossName(int boss) {
        return (String) this.plugin.data.get("boss" + boss + "name");
    }

    public void reset() {
        //bosses[0] = false;
        //bosses[1] = false;

        if (task1 != null) {
            task1.cancel();
        }
        if (task2 != null) {
            task2.cancel();
        }
    }
}