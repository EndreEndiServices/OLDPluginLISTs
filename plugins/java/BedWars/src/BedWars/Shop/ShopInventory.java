package BedWars.Shop;

import cn.nukkit.Player;
import cn.nukkit.block.BlockAir;
import cn.nukkit.inventory.BaseInventory;
import cn.nukkit.inventory.InventoryType;
import cn.nukkit.item.Item;
import cn.nukkit.item.ItemBlock;
import cn.nukkit.math.Vector3;
import cn.nukkit.nbt.NBTIO;
import cn.nukkit.nbt.tag.CompoundTag;
import cn.nukkit.network.protocol.*;

import java.io.IOException;
import java.nio.ByteOrder;
import java.util.*;

public class ShopInventory extends BaseInventory {

    private HashMap<String, Vector3> spawnedBlocks = new HashMap<>();

    public ShopInventory() {
        super(new FakeHolder(), InventoryType.get(InventoryType.CHEST));
    }

    @Override
    public FakeHolder getHolder() {
        return (FakeHolder) this.holder;
    }

    public Vector3 pos = new Vector3();

    @Override
    public void onOpen(Player who) {
        super.onOpen(who);

        UpdateBlockPacket bp = new UpdateBlockPacket();
        List<UpdateBlockPacket.Entry> list = new ArrayList<>();
        Collections.addAll(list, bp.records);

        pos = new Vector3(who.getFloorX(), who.getFloorY() + 3, who.getFloorZ());
        spawnedBlocks.put(who.getName().toLowerCase(), pos.clone());

        list.add(new UpdateBlockPacket.Entry((int) pos.x, (int) pos.z, (int) pos.y, Item.CHEST, 0, UpdateBlockPacket.FLAG_ALL_PRIORITY));
        bp.records = list.stream().toArray(UpdateBlockPacket.Entry[]::new);
        who.dataPacket(bp);

        BlockEntityDataPacket bep = new BlockEntityDataPacket();
        bep.x = (int) pos.x;
        bep.y = (int) pos.y;
        bep.z = (int) pos.z;

        try {
            bep.namedTag = NBTIO.write(getSpawnCompound(pos), ByteOrder.LITTLE_ENDIAN);
        } catch (IOException e) {
            e.printStackTrace();
        }

        who.dataPacket(bep);

        ContainerOpenPacket pk = new ContainerOpenPacket();
        pk.windowid = (byte) who.getWindowId(this);
        pk.type = (byte) this.getType().getNetworkType();
        pk.slots = this.getSize();

        pk.x = (int) pos.x;
        pk.y = (int) pos.y;
        pk.z = (int) pos.z;

        who.dataPacket(pk);

        this.sendContents(who);
    }

    @Override
    public void onClose(Player who) {
        ContainerClosePacket pk2 = new ContainerClosePacket();
        pk2.windowid = (byte) who.getWindowId(this);
        who.dataPacket(pk2);

        Vector3 v = spawnedBlocks.get(who.getName().toLowerCase());

        if (v != null && who.getLevel().isChunkLoaded((int) v.x >> 4, (int) v.z >> 4)) {
            who.getLevel().sendBlocks(new Player[]{who}, new Vector3[]{v});
        }

        spawnedBlocks.remove(who.getName().toLowerCase());

        super.onClose(who);
    }

    private CompoundTag getSpawnCompound(Vector3 v) {
        CompoundTag c = new CompoundTag().putString("id", "Chest").putInt("x", (int) v.x).putInt("y", (int) v.y).putInt("z", (int) v.z);
        c.putString("CustomName", "Shop");

        return c;
    }

    @Override
    public void sendContents(Player p) {
        int id = p.getWindowId(this);

        if (id != -1) {
            ContainerSetContentPacket pk = new ContainerSetContentPacket();
            pk.slots = new Item[27];
            pk.windowid = id;
            Arrays.fill(pk.slots, new ItemBlock(new BlockAir()));

            p.dataPacket(pk);
        }

        super.sendContents(p);
    }
}
