package GTCore.MusicBox;

import java.util.SortedMap;
import java.util.TreeMap;

public class Layer {
    /**
     * The name of this layer.
     */
    private String name;

    /**
     * The volume of this layer with 0 being silent and 1 being full.
     */
    private double volume;

    /**
     * Notes for each tick (if there is one)
     */
    private final SortedMap<Integer, NoteBlock> noteBlocks = new TreeMap<>();

    /**
     * Create a new layer.
     */
    public Layer() {
    }

    /**
     * Create a new layer.
     * @param name Name of the layer
     */
    public Layer(String name) {
        this(name, 1);
    }

    /**
     * Create a new layer.
     * @param name Name of the layer
     * @param volume Volume with 0 being silent and 1 being full
     */
    public Layer(String name, double volume) {
        this.name = name;
        this.setVolume(volume);
    }

    /**
     * Returns the name of this layer.
     * @return The name
     */
    public String getName() {
        return name;
    }

    /**
     * Set the name of this layer.
     * @param name The name
     */
    public void setName(String name) {
        this.name = name;
    }

    /**
     * Returns the volume of this layer.
     * @return The volume with 0 being silent and 1 being full
     */
    public double getVolume() {
        return volume;
    }

    /**
     * Set the volume of this layer.
     * @param volume The volume with 0 being silent and 1 being full
     */
    public void setVolume(double volume) {
        //checkArgument(volume >= 0 && volume <= 1, "volume must be between 0 and 1 (inclusive)");
        this.volume = volume;
    }

    /**
     * Get the note block at the specified time.
     * @param tick Time in ticks
     * @return The note block or {@code null} if there is none
     */
    public NoteBlock getNoteBlock(int tick) {
        return noteBlocks.get(tick);
    }

    /**
     * Set the note block at the specified position.
     * @param tick Time in ticks
     * @param noteBlock The note block, may be {@code null} to remove the note block
     */
    public void setNoteBlock(int tick, NoteBlock noteBlock) {
        noteBlocks.put(tick, noteBlock);
    }

    /**
     * Returns a sorted map containing all note blocks and their time in ticks.
     * Changes to the returned map reflect onto this layer and vice versa.
     * @return Map of all note blocks
     */
    public SortedMap<Integer, NoteBlock> getNotes() {
        return noteBlocks;
    }
}
