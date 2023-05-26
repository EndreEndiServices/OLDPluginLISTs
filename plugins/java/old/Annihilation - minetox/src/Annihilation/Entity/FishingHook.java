package Annihilation.Entity;

import cn.nukkit.Player;
import cn.nukkit.entity.data.ByteEntityData;
import cn.nukkit.entity.data.LongEntityData;
import cn.nukkit.entity.projectile.EntityProjectile;
import cn.nukkit.level.format.FullChunk;
import cn.nukkit.nbt.tag.CompoundTag;
import cn.nukkit.network.protocol.AddEntityPacket;

public class FishingHook extends EntityProjectile{

    public static final int NETWORK_ID = 77;

    public static final int DATA_SOURCE_UUID = 23;
    public static final int DATA_TARGET_UUID = 24;

    public float width = 0.2F;
    public float length = 0.2F;
    public float height = 0.2F;

    protected float gravity = 0.04F;
    protected float drag = 0.04F;

    public Player owner;

    public FishingHook(FullChunk chunk, CompoundTag nbt){
        this(chunk, nbt, null);
    }

    public FishingHook(FullChunk chunk, CompoundTag nbt, Player owner){
        super(chunk, nbt);

        if(owner == null){
            this.close();
            return;
        }

        this.owner = owner;

        setDataProperty(new ByteEntityData(DATA_NO_AI, 1));
        setDataProperty(new LongEntityData(DATA_SOURCE_UUID, owner.getId()));
        setDataProperty(new LongEntityData(DATA_TARGET_UUID, getId()));
    }

    public void initEntity(){
        super.initEntity();

        setMaxHealth(1);
        setHealth(1);
    }

    public String getName(){
        return "Fishing Hook";
    }

    public int getNetworkId(){
        return NETWORK_ID;
    }

    public boolean onUpdate(int currentTick)
    {
        if (closed) {
            return false;
        }

        //$hasUpdate = parent::onUpdate($currentTick);
        boolean hasUpdate = false;
        age++;
        if (age > 1200 || owner == null) {
        close();
        hasUpdate = true;
    }
        if (isOnGround() || isCollided) {
        motionX = 0;
        motionY = 0;
        motionZ = 0;
    }
        if (isInsideOfWater()) motionY += 0.02;
        else if (!isOnGround() && !isCollided) motionY -= gravity;
        move(motionX, motionY, motionZ);
        if (!onGround || Math.abs(motionX) > 0.00001 || Math.abs(motionY) > 0.00001 || Math.abs(motionZ) > 0.00001) {
        double f = Math.sqrt(Math.pow(motionX, 2) + Math.pow(motionZ, 2));
        yaw = (Math.atan2(motionX, motionZ) * 180 / Math.PI);
        pitch = (Math.atan2(motionY, f) * 180 / Math.PI);

        hasUpdate = true;
    }
        updateMovement();
        float friction = 1 - drag;
        motionX *= friction;
        motionY *= 1 - drag;
        motionZ *= friction;

        return hasUpdate;
    }
    public void spawnTo(Player player)
    {
        if (owner == null) {
            close();
            return;
        }

        AddEntityPacket pk = new AddEntityPacket();
        pk.eid = getId();
        pk.type = FishingHook.NETWORK_ID;
        pk.x = (float) x;
        pk.y = (float) y;
        pk.z = (float) z;
        pk.speedX = (float) motionX;
        pk.speedY = (float) motionY;
        pk.speedZ = (float) motionZ;
        pk.yaw = (float) yaw;
        pk.pitch = (float) pitch;
        pk.metadata = dataProperties;
        player.dataPacket(pk);
        super.spawnTo(player);
    }
}
