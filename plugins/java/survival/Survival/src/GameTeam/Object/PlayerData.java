package GameTeam.Object;

import cn.nukkit.Player;
import lombok.Getter;
import lombok.Setter;

public class PlayerData {

    @Setter
    @Getter
    private Player player;

    public PlayerData(Player player){
        this.player = player;
    }

}
