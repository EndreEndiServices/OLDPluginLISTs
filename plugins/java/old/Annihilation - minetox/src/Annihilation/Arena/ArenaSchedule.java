package Annihilation.Arena;

import cn.nukkit.Player;
import cn.nukkit.blockentity.BlockEntitySign;
import cn.nukkit.level.Level;
import cn.nukkit.scheduler.Task;
import cn.nukkit.utils.TextFormat;

public class ArenaSchedule extends Task {

    public Arena plugin;
    public int time = 0;
    public int time1 = 120;
    public int popup = 0;

    public ArenaSchedule(Arena plugin) {
        this.plugin = plugin;
    }

    public void onRun(int currentTick) {
        if (this.popup == 0) {
            this.setJoinSigns();
        }
        this.popup++;

        if (this.popup == 2) {
            this.popup = 0;
        }
        if (this.plugin.phase == 0 && !this.plugin.starting) {
            this.plugin.checkLobby();
        }
        if (this.plugin.starting) {
            this.starting();
        }

        if (this.plugin.phase >= 1) {
            this.running();
        }
    }

    public void setJoinSigns() {
        Level lobby = this.plugin.plugin.level;

        BlockEntitySign sign = (BlockEntitySign) lobby.getBlockEntity(this.plugin.maindata.get("sign"));
        BlockEntitySign signb = (BlockEntitySign) lobby.getBlockEntity(this.plugin.maindata.get("1sign"));
        BlockEntitySign signr = (BlockEntitySign) lobby.getBlockEntity(this.plugin.maindata.get("2sign"));
        BlockEntitySign signy = (BlockEntitySign) lobby.getBlockEntity(this.plugin.maindata.get("3sign"));
        BlockEntitySign signg = (BlockEntitySign) lobby.getBlockEntity(this.plugin.maindata.get("4sign"));

        if (sign != null) {
            String map = this.plugin.map;
            if (this.plugin.phase <= 0) {
                map = "---";
            }

            String game = TextFormat.GREEN + "Lobby";
            if (this.plugin.phase >= 1) {
                switch (this.plugin.phase) {
                    case 1:
                        game = TextFormat.GOLD + "Phase: I";
                        break;
                    case 2:
                        game = TextFormat.GOLD + "Phase: II";
                        break;
                    case 3:
                        game = TextFormat.RED + "Phase: III";
                        break;
                    case 4:
                        game = TextFormat.RED + "Phase: IV";
                        break;
                    case 5:
                        game = TextFormat.RED + "Phase: V";
                        break;
                }
            }
            sign.setText(TextFormat.DARK_RED + "■" + this.plugin.id + "■", TextFormat.BLACK + this.plugin.getAllPlayers().size() + "/150", game, TextFormat.BOLD + TextFormat.BLACK + map);
        }

        if (signb != null) {
            signb.setText("", TextFormat.DARK_BLUE + "[BLUE]", TextFormat.GRAY + this.plugin.getTeam(1).getPlayers().size() + TextFormat.GRAY + " players", "");
        }
        if (signr != null) {
            signr.setText("", TextFormat.DARK_RED + "[RED]", TextFormat.GRAY + this.plugin.getTeam(2).getPlayers().size() + TextFormat.GRAY + " players", "");
        }
        if (signy != null) {
            signy.setText("", TextFormat.YELLOW + "[YELLOW]", TextFormat.GRAY + this.plugin.getTeam(3).getPlayers().size() + TextFormat.GRAY + " players", "");
        }
        if (signg != null) {
            signg.setText("", TextFormat.DARK_GREEN + "[GREEN]", TextFormat.GRAY + this.plugin.getTeam(4).getPlayers().size() + TextFormat.GRAY + " players", "");
        }
    }

    public void starting() {
        this.time1--;

        for (Player p : this.plugin.getAllPlayers().values()) {
            p.setExperience(0, Math.max(0, this.time1));
        }

        if (this.time1 == 5) {
            this.plugin.selectMap();
            return;
        }

        if (this.time1 <= 0) {
            this.plugin.startGame();
            //this.time1 = 120;
            //this.plugin.starting = false;
        }
    }

    private int checkAlive = 0;

    public void running() {
        //$this->plugin->checkAlive();
        this.checkAlive++;
        if (this.checkAlive >= 5) {
            this.plugin.checkAlive();
        }

        this.time++;
        if (this.time == 600) {
            this.plugin.changePhase(2);
        }
        if (this.time == 1200) {
            this.plugin.changePhase(3);
        }
        if (this.time == 1800) {
            this.plugin.changePhase(4);
        }
        if (this.time == 2400) {
            this.plugin.changePhase(5);
        }
        if (this.time == 5400) {
            this.plugin.ending = true;
        }
    }

}