package GTCore.MusicBox;

import java.util.ArrayList;
import java.util.List;
import java.util.SortedMap;

public class Song {
    /**
     * Duration of this song in ticks.
     */
    private int length;

    /**
     * The name of this song.
     */
    private final String name;

    /**
     * The author of this song.
     */
    private final String author;

    /**
     * The original author (e.g. of the midi).
     */
    private final String originalAuthor;

    /**
     * A description of this song.
     */
    private final String description;

    /**
     * The tempo measured in ticks per second.
     */
    private double tempo;

    /**
     * List of all layers.
     */
    private final List<Layer> layers = new ArrayList<>();

    /**
     * Create a new song.
     * @param length The length in ticks
     * @param name The name
     * @param author The author
     * @param originalAuthor The original author
     * @param description A description
     * @param tempo The temp measured in ticks per second
     */
    public Song(int length, String name, String author, String originalAuthor, String description, double tempo) {
        this.length = length;
        this.name = name;
        this.author = author;
        this.originalAuthor = originalAuthor;
        this.description = description;
        this.tempo = tempo;
    }

    /**
     * Return the length of this song.
     * This value does not update if the underlying layers are modified.
     * To update the length, call {@link #updateLength()}
     * @return The length in ticks
     */
    public int getLength() {
        return length;
    }

    /**
     * Updates the length of this song.
     * Should be called after the underlying layers have been modified.
     */
    public void updateLength() {
        length = 0;
        for (Layer layer : layers) {
            SortedMap<Integer, NoteBlock> notes = layer.getNotes();
            int layerLength = notes.isEmpty() ? 0 : notes.lastKey();
            if (layerLength > length) {
                length = layerLength;
            }
        }
    }

    /**
     * Return the name of this song.
     * @return The name
     */
    public String getName() {
        return name;
    }

    /**
     * Return the author of this song.
     * @return The author
     */
    public String getAuthor() {
        return author;
    }

    /**
     * Return the original author of this song. (e.g. of the converted midi file)
     * @return The original author
     */
    public String getOriginalAuthor() {
        return originalAuthor;
    }

    /**
     * Return a description of this song.
     * @return The description
     */
    public String getDescription() {
        return description;
    }

    /**
     * Return the tempo of this song.
     * @return Tempo measured in ticks per second
     */
    public double getTempo() {
        return tempo;
    }

    /**
     * Set the temp of this song.
     * Note that for some values notes might fall into the same tick and sound strange.
     * @param tempo Tempo measured in ticks per second
     */
    public void setTempo(double tempo) {
        this.tempo = tempo;
    }

    /**
     * Return the layers of this song.
     * Changes to the returned list reflect onto this song and vice versa.
     * @return List of layers
     */
    public List<Layer> getLayers() {
        return layers;
    }
}
