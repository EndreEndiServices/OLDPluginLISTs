package GTCore.Task;

import GTCore.MTCore;
import cn.nukkit.Player;
import cn.nukkit.scheduler.Task;

import java.util.ArrayList;

public class MessageTask extends Task {

    public static ArrayList<String> messages = new ArrayList<>();
    private int i = 0;
    private MTCore plugin;

    public MessageTask(MTCore plugin) {
        this.plugin = plugin;
    }

    @Override
    public void onRun(int currentTick) {
        if (i >= MessageTask.messages.size()) {
            i = 0;
        }

        for (Player p : new ArrayList<>(plugin.getServer().getOnlinePlayers().values())) {
            p.sendMessage(MTCore.getPrefix() + MessageTask.messages.get(i));
        }

        plugin.getServer().getLogger().info(MTCore.getPrefix() + MessageTask.messages.get(i));
        i++;
    }
}