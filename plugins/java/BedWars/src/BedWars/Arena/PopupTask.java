package BedWars.Arena;

import BedWars.Entity.WinParticle;
import BedWars.Object.BedWarsData;
import cn.nukkit.entity.Entity;
import cn.nukkit.level.sound.FizzSound;
import cn.nukkit.nbt.tag.CompoundTag;
import cn.nukkit.nbt.tag.DoubleTag;
import cn.nukkit.nbt.tag.FloatTag;
import cn.nukkit.nbt.tag.ListTag;
import cn.nukkit.scheduler.Task;
import cn.nukkit.math.Vector3;
import cn.nukkit.Player;

import java.util.ArrayList;
import java.util.Random;

public class PopupTask extends Task {

    public Arena plugin;
    public int ending = 0;

    public PopupTask(Arena plugin) {
        this.plugin = plugin;
    }

    @Override
    public void onRun(int tick) {
        if (this.plugin.game == 1 && !this.plugin.ending) {
            this.sendStatus();
        }
        if (this.plugin.ending && this.plugin.game == 1) {
            if (this.ending == 30) {
                this.plugin.ending = false;
                this.plugin.stopGame();
                this.ending = 0;
                return;
            }
            this.ending++;
            this.sendEnding();
        }
        if (this.plugin.game == 0) {
            this.sendVotes();
        }
    }

    public void sendVotes() {
        VotingManager vm = this.plugin.votingManager;
        //$this->plugin->plugin->getServer()->getLogger()->info("{$vm->stats[1]} {$vm->stats[2]} {$vm->stats[3]}");
        String[] votes = new String[]{vm.currentTable[0], vm.currentTable[1], vm.currentTable[2]};

        String tip = "                                                   §8Hlasovani §f| §6/vote <mapa>"
                + "\n                                                 §b[1] §8" + votes[0] + " §c» §a" + vm.stats.get(votes[0]) + " hlasu"
                + "\n                                                 §b[2] §8" + votes[1] + " §c» §a" + vm.stats.get(votes[1]) + " hlasu"
                + "\n                                                 §b[3] §8" + votes[2] + " §c» §a" + vm.stats.get(votes[2]) + " hlasu";

        for (BedWarsData data : new ArrayList<>(this.plugin.playerData.values())) {
            data.getPlayer().sendTip(tip);
        }                        //    |
    }

    public void sendStatus() {
        String status = this.plugin.getGameStatus();

        for (BedWarsData data : new ArrayList<>(this.plugin.playerData.values())) {
            data.getPlayer().sendTip(status);
        }

        for (Player p : new ArrayList<>(plugin.spectators.values())) {
            p.sendTip(status);
        }
    }

    public void sendEnding() {
        int team = this.plugin.winnerTeam;
        String name = this.plugin.teams[team].getColor() + this.plugin.teams[team].getName();

        Player[] tempArray = new Player[1];
        FizzSound tempSound = new FizzSound(new Vector3());

        for (BedWarsData data : new ArrayList<>(this.plugin.playerData.values())) {
            Player p = data.getPlayer();
         /*p.sendTip(TextFormat.GRAY + "                    ================[ " + TextFormat.DARK_AQUA + "Postup" + TextFormat.GRAY + " ]================\n"
         +  "                               Tym " + TextFormat.BOLD + name + TextFormat.GREEN + " vyhral hru\n"
         + TextFormat.GRAY          +  "======================================================");*/
            tempArray[0] = p;
            tempSound.setComponents(p.x, p.y + p.getEyeHeight(), p.z);
            this.plugin.level.addSound(tempSound, tempArray[0]);

            Vector3 random = randomVector(p);

            CompoundTag nbt = new CompoundTag()
                    .putList(new ListTag<DoubleTag>("Pos")
                            .add(new DoubleTag("", random.x))
                            .add(new DoubleTag("", random.y))
                            .add(new DoubleTag("", random.z)))
                    .putList(new ListTag<DoubleTag>("Motion")
                            .add(new DoubleTag("", 0))
                            .add(new DoubleTag("", 0))
                            .add(new DoubleTag("", 0)))

                    .putList(new ListTag<FloatTag>("Rotation")
                            .add(new FloatTag("", (float) 0))
                            .add(new FloatTag("", (float) 0)));

            new WinParticle(plugin.level.getChunk((int) random.x >> 4, (int) random.z >> 4), nbt);
        }

        for (Player p : new ArrayList<>(plugin.spectators.values())) {
            tempArray[0] = p;
            tempSound.setComponents(p.x, p.y + p.getEyeHeight(), p.z);
            this.plugin.level.addSound(tempSound, tempArray[0]);
        }
    }

    private Vector3 randomVector(Vector3 center) {
        Random rnd = new Random();

        double x = rnd.nextInt(8) - 4;
        double z = rnd.nextInt(8) - 4;

        return new Vector3(center.x + x, center.y, center.z + z);
    }

}