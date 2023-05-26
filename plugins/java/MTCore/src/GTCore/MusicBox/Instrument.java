package GTCore.MusicBox;

import cn.nukkit.level.sound.NoteBoxSound;
import com.google.common.base.Predicates;
import com.google.common.collect.Iterators;

public enum Instrument {
    PIANO(NoteBoxSound.INSTRUMENT_PIANO),
    BASS(NoteBoxSound.INSTRUMENT_BASS),
    BASS_DRUM(NoteBoxSound.INSTRUMENT_BASS_DRUM),
    SNARE_DRUM(NoteBoxSound.INSTRUMENT_TABOUR),
    STICKS(NoteBoxSound.INSTRUMENT_CLICK);

    /**
     * The sound corresponding to this instrument.
     */
    private final int soundType;

    Instrument(int soundType) {
        this.soundType = soundType;
    }

    /**
     * Returns the sound corresponding to instrument.
     * @return The sound type
     */
    public int getSoundType() {
        return soundType;
    }

    /**
     * Converts the specified note pitch to a sound effect pitch.
     * @param pitch The note
     * @return The sound effect pitch in range [0, 2]
     */
    public float getPitch(int pitch) {
        //Iterator<? extends NotePitch> iter = registry.getAllOf(NotePitch.class).iterator();
        //return PITCH[Iterators.indexOf(iter, Predicates.equalTo(pitch))];
        return 0;
    }

    /**
     * Returns the instrument corresponding to the id in the nbs file.
     * If the id is a custom instrument, PIANO is returned.
     * @param id The id
     * @return The instrument
     */
    public static Instrument forId(int id) {
        if (id < 0 || id >= values().length) {
            return PIANO;
        }
        return values()[id];
    }

    /**
     * Maps each NotePitch id to a pitch value as used by the /playsound command.
     * Values taken from the <a href="http://minecraft.gamepedia.com/Note_block?cookieSetup=true#Usage"> minecraft wiki</a>.
     */
    private static final float[] PITCH = {
            0.5f, 0.53f, 0.56f, 0.6f, 0.63f, 0.67f, 0.7f, 0.75f, 0.8f, 0.85f, 0.9f, 0.95f,
            1.0f, 1.05f, 1.1f, 1.2f, 1.25f, 1.32f, 1.4f, 1.5f, 1.6f, 1.7f, 1.8f, 1.9f, 2.0f
    };
}
