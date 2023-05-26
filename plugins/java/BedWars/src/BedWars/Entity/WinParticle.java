package BedWars.Entity;

import cn.nukkit.entity.Entity;
import cn.nukkit.level.format.FullChunk;
import cn.nukkit.level.particle.DustParticle;
import cn.nukkit.level.particle.InstantSpellParticle;
import cn.nukkit.level.particle.Particle;
import cn.nukkit.level.particle.SpellParticle;
import cn.nukkit.math.Vector3;
import cn.nukkit.nbt.tag.CompoundTag;

import java.util.Random;

public class WinParticle extends Entity {

    private Vector3 startPos;

    public WinParticle(FullChunk chunk, CompoundTag nbt) {
        super(chunk, nbt);
    }

    @Override
    public int getNetworkId() {
        return -10;
    }

    @Override
    protected void initEntity() {
        super.initEntity();
        startPos = new Vector3();
        startPos.setComponents(x, y, z);
    }

    @Override
    public boolean onUpdate(int diff) {
        int tick = getServer().getTick();

        Random rnd = new Random();

        this.level.addParticle(new DustParticle(this, rnd.nextInt(256), rnd.nextInt(256), rnd.nextInt(256)));

        if (this.y > (startPos.y + 13)) {
            this.level.addParticle(new InstantSpellParticle(this, rnd.nextInt(256), rnd.nextInt(256), rnd.nextInt(256)));
            this.level.addParticle(new InstantSpellParticle(this, rnd.nextInt(256), rnd.nextInt(256), rnd.nextInt(256)));
            close();
            return false;
        }

        this.y += 0.3;
        lastUpdate = tick;
        return true;
    }
}
