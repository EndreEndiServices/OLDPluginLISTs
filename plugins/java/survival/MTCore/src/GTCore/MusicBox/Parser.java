package GTCore.MusicBox;

import com.google.common.io.LittleEndianDataInputStream;

import javax.sound.midi.*;
import java.io.*;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;
import java.util.SortedMap;

public class Parser {

    public static Song parseMidi(File file, String name, String author) throws IOException {
        try (FileInputStream in = new FileInputStream(file)) {
            return parseMidi(in, name, author);
        }
    }

    public static Song parseMidi(InputStream inputStream, String name, String author) throws IOException {
        try {
            Sequence sequence = MidiSystem.getSequence(inputStream);
            double midiTickToMicro = (double) sequence.getMicrosecondLength() / sequence.getTickLength();
            List<Layer> layers = new ArrayList<>();
            for (Track track : sequence.getTracks()) {
                Instrument[] instrument = new Instrument[16];
                for (int i = 0; i < track.size(); i++) {
                    MidiEvent event = track.get(i);
                    MidiMessage message = event.getMessage();
                    if (message instanceof ShortMessage) {
                        ShortMessage m = (ShortMessage) message;
                        if (m.getCommand() == ShortMessage.NOTE_ON) {
                            int tick = (int) (midiTickToMicro * event.getTick() / 50_000);
                            double volume = m.getData2() / 127d;
                            int pitch = (m.getData1() - 6) % 24;
                            Layer layer = null;
                            for (Layer l : layers) {
                                if (l.getVolume() == volume && l.getNoteBlock(tick) == null) {
                                    layer = l;
                                }
                            }
                            if (layer == null) {
                                layers.add(layer = new Layer("Layer" + layers.size(), volume));
                            }
                            layer.setNoteBlock(tick, new NoteBlock(instrument[m.getChannel()], pitch));
                        } else if (m.getCommand() == ShortMessage.PROGRAM_CHANGE) {
                            instrument[m.getChannel()] = programToInstrument(m.getData1(), m.getChannel());
                        }
                    }
                }
            }
            int length = 0;
            for (Layer layer : layers) {
                SortedMap<Integer, NoteBlock> notes = layer.getNotes();
                length = Math.max(length, notes.isEmpty() ? 0 : notes.lastKey());
            }
            Song song = new Song(length, name, author, author, "Imported MIDI file", 1);
            song.getLayers().addAll(layers);
            song.setTempo(20);
            return song;
        } catch (InvalidMidiDataException e) {
            throw new IOException(e);
        }
    }

    public static Song parseNBS(File file) throws IOException {
        try (FileInputStream in = new FileInputStream(file)) {
            return parseNBS(in);
        }
    }

    public static Song parseNBS(InputStream inputStream) throws IOException {

        DataInput in = new LittleEndianDataInputStream(inputStream);

        // Header
        int length = in.readShort();
        int height = in.readShort();
        String name = readString(in);
        String author = readString(in);
        String originalAuthor = readString(in);
        String description = readString(in);
        double tempo = in.readShort() / 100d;
        in.skipBytes(23); // Skip unnecessary info
        in.skipBytes(in.readInt());

        // Note blocks
        Layer[] layers = new Layer[height];
        for (int i = 0; i < layers.length; i++) {
            layers[i] = new Layer();
        }

        int jump;
        int tick = -1;
        while ((jump = in.readShort()) != 0) {
            tick += jump;
            int layer = -1;
            while ((jump = in.readShort()) != 0) {
                layer += jump;

                Instrument instrument = Instrument.forId(in.readByte());
                int pitch = (in.readByte() - 33) % 24;

                layers[layer].setNoteBlock(tick, new NoteBlock(instrument, pitch));
            }
        }

        // Layer info
        for (Layer layer : layers) {
            layer.setName(readString(in));
            layer.setVolume(in.readByte() / 100d);
        }

        Song song = new Song(length, name, author, originalAuthor, description, tempo);
        song.getLayers().addAll(Arrays.asList(layers));
        return song;
    }

    /*private NotePitch notePitchForId(GameRegistry gameRegistry, int id) {
        return Iterators.get(gameRegistry.getAllOf(NotePitch.class).iterator(), id, NotePitches.F_SHARP0);
    }*/

    private static String readString(DataInput in) throws IOException {
        byte[] buf = new byte[in.readInt()];
        in.readFully(buf);
        return new String(buf);
    }

    /**
     * Returns the instrument for the specified program and channel in a midi file.
     * Values according to <a href="http://en.wikipedia.org/wiki/General_MIDI">Wikipedia</a>.
     *
     * @param program The program
     * @param channel The channel
     * @return The instrument
     */
    private static Instrument programToInstrument(int program, int channel) {
        if (channel == 9) { // Note that java counts channels from 0 while wikipedia starts at 1
            return Instrument.BASS_DRUM;
        }

        if (program >= 24 && program <= 39 || program >= 43 && program <= 46) {
            return Instrument.BASS;
        }

        if (program >= 113 && program <= 119) {
            return Instrument.BASS_DRUM;
        }

        if (program >= 120 && program <= 127) {
            return Instrument.SNARE_DRUM;
        }

        return Instrument.PIANO;
    }
}
