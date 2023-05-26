package BedWars.Arena;

import BedWars.Object.BedWarsData;
import cn.nukkit.Player;
import cn.nukkit.scheduler.Task;

import java.util.ArrayList;


public class ArenaSchedule extends Task {

    public int gameTime = 3600;
    public int startTime = 120;
    public boolean isShortered = false;
    public int sign = 0;
    public Arena plugin;
    public int drop = 0;

    public ArenaSchedule(Arena pl) {
        this.plugin = pl;
    }


    @Override
    public void onRun(int tick) {
        /*if (!this.plugin.starting && this.plugin.game == 0){
            this.waiting();
        }*/
        if (this.plugin.starting) {
            this.starting();
        } else if (this.plugin.game == 1 && !this.plugin.ending) {
            this.game();
        } /*else if(this.plugin.ending){
            ending();
        }*/
        /*if (this.sign == 2){
            this.updateMainSign();
            if (this.plugin.game == 0){
                this.updateTeamSigns();
                this.plugin.checkLobby();
            }
            this.sign = 0;
        }
        this.sign++;*/
    }

    public void waiting() {
        int count = this.plugin.playerData.size();
        for (BedWarsData p : new ArrayList<>(this.plugin.playerData.values())) {
            p.getPlayer().sendPopup("§eCekam na hrace... §b(§c" + count + "/§a16§b)");
        }
    }

    public void starting() {
        if (this.startTime == 5) {
            this.plugin.selectMap();
        }
        if (this.startTime <= 0) {
            this.plugin.startGame();
            //this.startTime = 120;
            return;
        } else {
            for (BedWarsData data : new ArrayList<>(this.plugin.playerData.values())) {
                Player p = data.getPlayer();

                p.sendExperienceLevel(this.startTime);
                if (this.isShortered) {
                    p.sendExperience((this.startTime / 10));
                } else {
                    p.sendExperience((this.startTime / 120));
                }
            }
        }

        this.startTime--;
    }

    public void game() {
        //this.plugin.checkAlive();
        this.plugin.dropBronze();

        if (this.drop % 7 == 0) {
            this.plugin.dropIron();
        }

        if (this.drop % 30 == 0) {
            this.plugin.dropGold();
        }

        /*switch(this.drop){
            case 0:
                this.plugin.dropIron();
                this.plugin.dropGold();
                break;
            case 7:
                this.plugin.dropIron();
                break;
            case 15:
                this.plugin.dropIron();
                break;
            case 22:
                this.plugin.dropIron();
                break;
            case 29:
                this.drop = 0;
                break;
        }*/

        this.drop++;
    }

    public void ending() {

    }
}
