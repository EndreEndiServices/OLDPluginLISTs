package MTCore.Generator;

import java.util.Map;

import cn.nukkit.level.ChunkManager;
import cn.nukkit.level.generator.Generator;
import cn.nukkit.math.NukkitRandom;
import cn.nukkit.math.Vector3;

public class EmptyGenerator extends Generator
{
    private final String NAME = "emptyworld";
    private ChunkManager chunkManager;

    public EmptyGenerator(Map options) {
	/* empty */
    }

    public int getId() {
        return 1;
    }

    public void init(ChunkManager chunkManager, NukkitRandom nukkitRandom) {
        this.chunkManager = chunkManager;
    }

    public void generateChunk(int chX, int chZ) {

    }

    public void populateChunk(int i, int i1) {
	/* empty */
    }

    public Map getSettings() {
        return null;
    }

    public String getName() {
        return "emptyworld";
    }

    public Vector3 getSpawn() {
        return new Vector3(128.0, 65.0, 128.0);
    }

    public ChunkManager getChunkManager() {
        return chunkManager;
    }
}

