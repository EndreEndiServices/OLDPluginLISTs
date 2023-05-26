package BedWars.Arena;

import cn.nukkit.Server;
import org.apache.commons.io.FileUtils;

import java.io.*;

public class WorldManager {

    public static void addWorld(String name, String id) {
        File from = new File(Server.getInstance().getDataPath() + "/worlds/bedwars/" + name);
        File to = new File(Server.getInstance().getDataPath() + "/worlds/" + name + "_" + id);

        try {
            FileUtils.copyDirectory(from, to);
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    public static void deleteWorld(String name, String id) {
        try {
            File directory = new File(Server.getInstance().getDataPath() + "/worlds/" + name + "_" + id);
            FileUtils.deleteDirectory(directory);
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    public static void resetWorld(String name, String id) {
        deleteWorld(name, id);
        addWorld(name, id);
    }
}

