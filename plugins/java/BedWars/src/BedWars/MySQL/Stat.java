package BedWars.MySQL;

import lombok.Getter;

public enum Stat {
    KILLS(50, 1),
    DEATHS(0, 0),
    WINS(500, 10),
    LOSSES(0, 0),
    BEDS(200, 5),
    //PLACE(1, 0),
    //BREAK(1, 0),
    PLAYED(200, 0);
    //RESOURCES(1, 0);

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
