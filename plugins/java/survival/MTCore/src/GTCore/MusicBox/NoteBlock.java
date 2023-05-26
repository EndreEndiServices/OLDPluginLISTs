package GTCore.MusicBox;

public class NoteBlock {

    /**
     * The instrument of this note.
     */
    private final Instrument instrument;

    /**
     * The pitch of this note.
     */
    private final int pitch;

    /**
     * Create a new note block.
     *
     * @param instrument The instrument
     * @param pitch      The pitch
     */
    public NoteBlock(Instrument instrument, int pitch) {
        this.instrument = instrument;
        this.pitch = pitch;
    }

    /**
     * Returns the instrument of this note block.
     *
     * @return The instrument
     */
    public Instrument getInstrument() {
        return instrument;
    }

    /**
     * Returns the pitch of this note block.
     *
     * @return The pitch
     */
    public int getPitch() {
        return pitch;
    }
}
