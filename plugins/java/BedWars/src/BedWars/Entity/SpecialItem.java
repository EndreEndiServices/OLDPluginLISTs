package BedWars.Entity;

import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.entity.Entity;
import cn.nukkit.entity.item.EntityItem;
import cn.nukkit.item.Item;
import cn.nukkit.level.format.FullChunk;
import cn.nukkit.math.AxisAlignedBB;
import cn.nukkit.nbt.tag.CompoundTag;
import cn.nukkit.network.protocol.TakeItemEntityPacket;

public class SpecialItem extends EntityItem {

    public SpecialItem(FullChunk chunk, CompoundTag nbt) {
        super(chunk, nbt);
    }

    /*@Override
    public void initEntity() {
        super.initEntity();

        for (Entity item : this.level.getNearbyEntities(new AxisAlignedBB(x - 2, y - 1, z - 2, x + 2, y + 1, z + 2), this)) {
            if (!(item instanceof EntityItem)) {
                continue;
            }

            EntityItem itemm = (EntityItem) item;

            if (!itemm.getItem().equals(this.item, true, false) || itemm.getItem().getCount() >= 64) {
                continue;
            }

            this.item.count += ((EntityItem) item).getItem().getCount();

            item.close();
        }
    }*/

    /*@Override
    public void spawnToAll() {
        Entity[] entities = this.level.getNearbyEntities(new AxisAlignedBB(x - 1, y - 1, z - 1, x + 1, y + 1, z + 1), this);

        for (Entity entity : entities) {
            if (entity instanceof EntityItem) {
                return;
            }
        }

        super.spawnToAll();
    }

    @Override
    public void spawnTo(Player p) {
        Entity[] entities = this.level.getNearbyEntities(new AxisAlignedBB(x - 1, y - 1, z - 1, x + 1, y + 1, z + 1), this);

        for (Entity entity : entities) {
            if (entity instanceof EntityItem) {
                return;
            }
        }

        super.spawnTo(p);
    }*/

    @Override
    public boolean onUpdate(int diff) {
        boolean result = super.onUpdate(diff);

        if (!closed && isAlive()) {
            Entity[] entities = this.level.getNearbyEntities(this.boundingBox.grow(1.0D, 1D, 1.0D), this);

            for (Entity entity : entities) {
                if (entity instanceof Player) {
                    Player p = (Player) entity;

                    TakeItemEntityPacket pk = new TakeItemEntityPacket();
                    pk.entityId = p.getId();
                    pk.target = this.getId();

                    Server.broadcastPacket(entity.getViewers().values(), pk);

                    pk = new TakeItemEntityPacket();
                    pk.entityId = 0L;
                    pk.target = this.getId();
                    p.dataPacket(pk);
                    p.getInventory().addItem(this.item.clone());
                    result = false;
                    this.close();
                    break;
                }
            }
        }

        return result;
    }
}
