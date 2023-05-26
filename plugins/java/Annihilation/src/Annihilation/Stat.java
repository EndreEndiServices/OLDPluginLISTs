package Annihilation;

import lombok.Getter;

public enum Stat {
    KILLS(50, 1),
    DEATHS(0, 0),
    WINS(500, 50),
    LOSSES(0, 0),
    NEXUSDMG(5, 1),
    NEXUSES(200, 20);

    @Getter
    private String name;
    @Getter
    private int xp;
    @Getter
    private int tokens;

    Stat(int xp, int tokens) {
        this.xp = xp;
        this.tokens = tokens;
        this.name = name().toLowerCase().trim();
    }
}
