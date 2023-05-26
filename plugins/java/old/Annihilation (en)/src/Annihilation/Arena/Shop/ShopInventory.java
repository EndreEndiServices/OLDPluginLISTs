package Annihilation.Arena.Shop;

import cn.nukkit.Player;
import cn.nukkit.block.BlockAir;
import cn.nukkit.blockentity.BlockEntityChest;
import cn.nukkit.inventory.BaseInventory;
import cn.nukkit.inventory.InventoryType;
import cn.nukkit.item.Item;
import cn.nukkit.item.ItemBlock;
import cn.nukkit.math.Vector3;
import cn.nukkit.nbt.NBTIO;
import cn.nukkit.nbt.tag.CompoundTag;
import cn.nukkit.network.protocol.*;
import lombok.Setter;

import java.io.IOException;
import java.nio.ByteOrder;
import java.util.*;

public class ShopInventory extends BaseInventory {

    private HashMap<String, Vector3> spawnedBlocks = new HashMap<>();

    public ShopInventory() {
        super(new FakeHolder(), InventoryType.get(InventoryType.CHEST));
    }

    @Setter
    public String customName = "Shop";

    @Override
    public FakeHolder getHolder() {
        return (FakeHolder) this.holder;
    }

    @Override
    public void onOpen(Player who) {
        super.onOpen(who);

        UpdateBlockPacket bp = new UpdateBlockPacket();
        List<UpdateBlockPacket.Entry> list = new ArrayList<>();
        Collections.addAll(list, bp.records);

        Vector3 v = new Vector3(who.getFloorX(), who.getFloorY(), who.getFloorZ());
        spawnedBlocks.put(who.getName().toLowerCase(), v.clone());

        list.add(new UpdateBlockPacket.Entry((int) v.x, (int) v.z, (int) v.y, Item.CHEST, 0, UpdateBlockPacket.FLAG_ALL_PRIORITY));
        bp.records = list.stream().toArray(UpdateBlockPacket.Entry[]::new);
        who.dataPacket(bp);

        BlockEntityDataPacket bep = new BlockEntityDataPacket();
        bep.x = (int) v.x;
        bep.y = (int) v.y;
        bep.z = (int) v.z;

        try {
            bep.namedTag = NBTIO.write(getSpawnCompound(v), ByteOrder.LITTLE_ENDIAN);
        } catch (IOException e) {
            e.printStackTrace();
        }

        who.dataPacket(bep);

        ContainerOpenPacket pk = new ContainerOpenPacket();
        pk.windowid = (byte) who.getWindowId(this);
        pk.type = (byte) this.getType().getNetworkType();
        pk.slots = this.getSize();

        pk.x = (int) v.x;
        pk.y = (int) v.y;
        pk.z = (int) v.z;

        who.dataPacket(pk);

        this.sendContents(who);
    }

    @Override
    public void onClose(Player who) {
        ContainerClosePacket pk2 = new ContainerClosePacket();
        pk2.windowid = (byte) who.getWindowId(this);
        who.dataPacket(pk2);

        Vector3 v = spawnedBlocks.get(who.getName().toLowerCase());

        if (v != null) {
            /*UpdateBlockPacket bp = new UpdateBlockPacket();
            List<UpdateBlockPacket.Entry> list = new ArrayList<>();
            Collections.addAll(list, bp.records);
            list.add(new UpdateBlockPacket.Entry((int) v.x, (int) v.z, (int) v.y, Item.AIR, 0, UpdateBlockPacket.FLAG_NONE));
            bp.records = list.stream().toArray(UpdateBlockPacket.Entry[]::new);
            who.dataPacket(bp);*/

            who.getLevel().sendBlocks(new Player[]{who}, new Vector3[]{v});
        }

        spawnedBlocks.remove(who.getName().toLowerCase());

        super.onClose(who);
    }

    private CompoundTag getSpawnCompound(Vector3 v) {
        CompoundTag c = new CompoundTag().putString("id", "Chest").putInt("x", (int) v.x).putInt("y", (int) v.y).putInt("z", (int) v.z);
        c.putString("CustomName", this.customName);

        return c;
    }
}
