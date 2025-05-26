# CHIM Mind Map 

A web-based 3D visualization tool designed to explore memory data from the Skyrim AI mod **[CHIM](https://www.nexusmods.com/skyrimspecialedition/mods/126330?tab=description)**.

This application renders memories as nodes in a 3D space based on their embeddings, connecting them chronologically and allowing users to inspect details such as summaries and involved companions. It's built with HTML, CSS, JavaScript (Three.js), and uses a PHP backend to fetch data from a PostgreSQL database.

For more general information on CHIM, see the **[CHIM Nexus Mod Page](https://www.nexusmods.com/skyrimspecialedition/mods/126330?tab=description)**.

## ✨ Features

- **3D Memory Visualization:** Renders memory embeddings as interactive nodes in a 3D space.
- **Timeline Connections:** Displays chronological connections between memories with directional arrows.
- **Dynamic Node Coloring:** Node colors are based on the number of companions involved in the memory (White for 0, transitioning to Red-Orange for 10+).
- **Interactive Information Panel:** Click a node to display its summary and associated companions.
- **Companion Filtering:** Filter visible memories by selecting a specific person from a dropdown menu. The menu also shows the count of memories each person is in.



## 🛠️ Tech Stack

- **Frontend:** HTML, CSS, JavaScript
    - **3D Rendering:** [Three.js](https://threejs.org/)
- **Backend (Data Fetching):** PHP
- **Database:** PostgreSQL

## ⚙️ Configuration

Primary configuration is done within the files:

-   **`api.php`:**
    -   Database connection parameters: `$host`, `$port`, `$dbname`, `$user`, `$password`.
-   **`index.html` (JavaScript section):**
    -   `scaleFactor` (around line 275): Adjusts the overall spread of the 3D points.
    -   Various color values and styling options for nodes, panel, and toggles can be found in the `<style>` section or within the Three.js material creations if further customization is desired.

## 📦 Packaging

To compile the project into a distributable package:

### Windows and Linux/macOS
```bash
cd ..
tar -czf CHIM-MindMap.tar.gz --exclude="CHIM-MindMap/.git*" --exclude="CHIM-MindMap/*.log" CHIM-MindMap
```

This will create a `CHIM-MindMap.tar.gz` file in the parent directory that contains the complete folder structure (assuming your project folder is named `CHIM-MindMap`).

## 🤝 Contributing

Feel free to submit issues and enhancement requests!

## 📄 License

This project is licensed under the MIT License. (Assuming, please update if incorrect) 