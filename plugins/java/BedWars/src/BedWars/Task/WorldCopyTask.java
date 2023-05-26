package BedWars.Task;

import BedWars.Arena.Arena;
import BedWars.BedWars;
import BedWars.Arena.WorldManager;
import cn.nukkit.level.Level;
import cn.nukkit.scheduler.AsyncTask;
import cn.nukkit.Server;
import cn.nukkit.utils.LevelException;
import cn.nukkit.utils.TextFormat;

public class WorldCopyTask extends AsyncTask {

    private String map;
    private String id;

    private BedWars plugin;

    private boolean force;

    public WorldCopyTask(BedWars plugin, String map, String id) {
        this(plugin, map, id, false);
    }

    public WorldCopyTask(BedWars plugin, String map, String id, boolean force) {
        this.map = map;
        this.id = id;
        this.plugin = plugin;
        this.force = force;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    @Override
    public void onRun() {
        WorldManager.resetWorld(this.map, this.id);
    }

    @Override
    public void onCompletion(Server server) {
        try {
            server.loadLevel(this.map + "_" + this.id);
        } catch (LevelException e) {
            e.printStackTrace();
            server.getLogger().error("Error while loading level: " + this.map);
            return;
        }

        Arena arena = plugin.getArena(id);

        if (arena != null) {
            arena.isLevelLoaded = true;

            if (force) {
                arena.startGame();
            }
        }
    }

}