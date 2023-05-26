package BedWars.Entity;


import cn.nukkit.Player;
import cn.nukkit.entity.EntityCreature;
import cn.nukkit.level.format.FullChunk;
import cn.nukkit.nbt.tag.CompoundTag;
import cn.nukkit.nbt.tag.IntTag;
import cn.nukkit.network.protocol.AddEntityPacket;

public class Villager extends EntityCreature implements NPC {

    public static final int NETWORK_ID = 15;
    public static final int PROFESSION_FARMER = 0;
    public static final int PROFESSION_LIBRARIAN = 1;
    public static final int PROFESSION_PRIEST = 2;
    public static final int PROFESSION_BLACKSMITH = 3;
    public static final int PROFESSION_BUTCHER = 4;
    public static final int PROFESSION_GENERIC = 5;
    public float width = (float) 0.938;
    public float length = (float) 0.609;
    public float height = (float) 2;

    public Villager(FullChunk chunk, CompoundTag nbt) {
        super(chunk, nbt);
    }

    @Override
    public String getName() {
        return "Villager";
    }

    @Override
    public int getNetworkId() {
        return NETWORK_ID;
    }

    @Override
    protected void initEntity() {
        this.setMaxHealth(20);
        super.initEntity();
        if (!this.namedTag.contains("Profession")) {
            this.setProfession(1);
        }
    }

    @Override
    public void spawnTo(Player player) {
        AddEntityPacket pk = new AddEntityPacket();
        pk.type = this.getNetworkId();
        pk.eid = this.getId();
        pk.x = (float) this.x;
        pk.y = (float) this.y;
        pk.z = (float) this.z;
        pk.speedX = (float) this.motionX;
        pk.speedY = (float) this.motionY;
        pk.speedZ = (float) this.motionZ;
        pk.metadata = this.dataProperties;
        player.dataPacket(pk);

        super.spawnTo(player);
    }

    public void setProfession(int profession) {
        this.namedTag.put("Profession", new IntTag("Profession", profession));
    }

    public String getProfession() {
        return this.namedTag.get("Profession").toString();
    }

    public boolean isBaby() {
        return this.getDataFlag(14, 0);
    }


}