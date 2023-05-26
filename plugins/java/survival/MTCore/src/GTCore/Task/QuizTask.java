package GTCore.Task;


import GTCore.MTCore;
import cn.nukkit.scheduler.Task;

public class QuizTask extends Task {

    public static int END = 0;
    public static int START = 1;

    private MTCore plugin;
    private int action;

    public QuizTask(MTCore plugin, int action) {
        this.plugin = plugin;
        this.action = action;
    }

    @Override
    public void onRun(int tick) {
        switch (action) {
            case 0:
                plugin.endQuiz();
                break;
            case 1:
                plugin.startQuiz();
                break;
        }
    }

}
