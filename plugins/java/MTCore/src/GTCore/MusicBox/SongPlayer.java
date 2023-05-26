package GTCore.MusicBox;

import GTCore.MTCore;
import cn.nukkit.Player;
import cn.nukkit.math.Vector3;
import cn.nukkit.network.protocol.BlockEventPacket;
import cn.nukkit.scheduler.Task;

import java.io.File;
import java.io.FilenameFilter;
import java.io.IOException;
import java.util.Random;

public class SongPlayer extends Task{

    private MTCore plugin;

    private BlockEventPacket pk = new BlockEventPacket();

    public Song currentSong;

    private int actualTicksPassed = 0;
    private int tick = 0;

    public SongPlayer(MTCore plugin){
        this.plugin = plugin;
        Vector3 loc = plugin.level.getSpawnLocation();

        pk.x = loc.getFloorX();
        pk.y = loc.getFloorY() - 1;
        pk.z = loc.getFloorZ();

        start();
    }

    @Override
    public void onRun(int currentTick){
        actualTicksPassed++;

        while (tick < actualTicksPassed * currentSong.getTempo() / 20) {
            for (Player p : plugin.getServer().getOnlinePlayers().values()) {
                if (p.getLevel().getId() == plugin.level.getId()) {
                    for (Layer layer : currentSong.getLayers()) {
                        NoteBlock b = layer.getNoteBlock(currentTick);

                        pk.case1 = b.getInstrument().getSoundType();
                        pk.case2 = b.getPitch();

                        p.dataPacket(pk);
                    }
                }
            }

            tick++;
            if (tick > currentSong.getLength()) {

                currentSong = null;
                actualTicksPassed = 0;

                if (currentSong != null) {
                    currentSong.updateLength();
                    tick = 0;
                }

                tick = -1;
                this.cancel();

                start();
                return;
            }
        }
    }

    private Song getNextSong(){
        File folder = plugin.getDataFolder();

        File[] list = folder.listFiles(new FilenameFilter() {
            @Override
            public boolean accept(File dir, String name) {
                return name.endsWith(".nbs");
            }
        });

        File random = list[new Random().nextInt(list.length)];

        try {
            Song song = Parser.parseNBS(random);

            if(song == null){
                plugin.getLogger().error("Failed to load the song");
                cancel();
                return null;
            }

            return song;
        } catch(IOException e){
            plugin.getLogger().error("Failed to load the song");
            cancel();
            return null;
        }
    }

    private void start(){
        currentSong = getNextSong();

        if(currentSong == null){
            return;
        }

        plugin.getServer().getScheduler().scheduleRepeatingTask(this, (int) (2000 / currentSong.getTempo()));
    }
}
