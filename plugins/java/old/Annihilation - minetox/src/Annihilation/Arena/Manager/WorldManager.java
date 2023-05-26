package Annihilation.Arena.Manager;

import org.apache.commons.io.FileUtils;

import java.io.*;

public class WorldManager {

	public static void addWorld(String name, String path) {
		File from = new File(path + "/worlds/annihilation/" + name);
		File to = new File(path + "/worlds/" + name);

		try {
			FileUtils.copyDirectory(from, to);
		} catch (IOException e) {
			e.printStackTrace();
		}
	}

	public static void deleteWorld(String name, String path) {
		try {
			File directory = new File(path + "/worlds/" + name);
			FileUtils.deleteDirectory(directory);
		} catch (IOException e) {
			e.printStackTrace();
		}
	}
}

