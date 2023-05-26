package main.java.ColorMatch;

import GTCore.MTCore;
import cn.nukkit.command.Command;
import cn.nukkit.command.CommandSender;
import cn.nukkit.plugin.PluginBase;
import cn.nukkit.utils.Config;
import cn.nukkit.utils.TextFormat;
import lombok.Getter;
import main.java.ColorMatch.Arena.Arena;

import java.io.File;
import java.io.FilenameFilter;
import java.util.HashMap;

public class ColorMatch extends PluginBase {

    @Getter
    private HashMap<String, Arena> arenas = new HashMap<>();

    @Getter
    private MTCore mtcore;

    @Override
    public void onEnable() {
        mtcore = MTCore.getInstance();
        setData();
        this.registerArenas();
    }

    public static String getPrefix() {
        return "";
    }

    public void registerArena(String name, Config config) {
        Arena arena = new Arena(this, name, config);
        arenas.put(name, arena);
    }

    private void setData(){

    }

    private void registerArenas() {

    }

    public Arena getArena(String name) {
        return arenas.get(name.toLowerCase());
    }

    @Override
    public boolean onCommand(CommandSender sender, Command cmd, String label, String[] args) {
        if (cmd.getName().toLowerCase().equals("")) {
            if (args.length <= 0) {
                sender.sendMessage("");
                return false;
            }

            switch (args[0].toLowerCase()) {
                case "start":
                    break;
                case "help":
                    break;
            }
        }

        return true;
    }
}
