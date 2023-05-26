package Annihilation.Arena.Task;

import Annihilation.Annihilation;
import Annihilation.Arena.Arena;
import Annihilation.Arena.Manager.WorldManager;
import cn.nukkit.Server;
import cn.nukkit.scheduler.AsyncTask;

public class WorldCopyTask extends AsyncTask {

    private String arena;
    private String map;
    private String path;
    private boolean force;

    public WorldCopyTask(String arena, String map, String path, boolean force) {
        this.map = map;
        this.arena = arena;
        this.path = path;
        this.force = force;
    }

    @Override
    public void onRun() {
        WorldManager.deleteWorld(map, path);
        WorldManager.addWorld(map, path);
    }

    @Override
    public void onCompletion(Server server) {
        Annihilation plugin = Annihilation.getInstance();
        Arena arena = plugin.getArena(this.arena);

        if (server.loadLevel(map)) {
            arena.isLevelLoaded = true;
            arena.level = server.getLevelByName(map);
        }

        if (force) {
            arena.startGame(true);
        }
    }
}
