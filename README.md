# CHIM MindMap 3D Visualizer

This project provides a web-based 3D visualization of memory summaries and their vector embeddings for CHIM.
It fetches data from a PostgreSQL database, including summaries, embeddings, companion data, and a timestamp (`gamets_truncated`), then renders these as interactive 3D points in the browser.

## Features

*   **3D Visualization**: Displays data points in a 3D space using Three.js.
*   **Data Source**: Connects to a local PostgreSQL database (`dwemer`) to fetch data from the `memory_summary` table.
*   **Point Sizing**: The size of each data point (sphere) is dynamically determined by the `gamets_truncated` value (older entries might appear smaller/larger based on normalization).
*   **Point Styling**:
    *   The first data point (oldest by `gamets_truncated`) is colored **Green**.
    *   The last data point (newest by `gamets_truncated`) is colored **Magenta**.
    *   Intermediate points have a gradient color based on their sequence in the `gamets_truncated` order.
    *   Points highlight **Red** and scale up on hover.
*   **Interactive Lines**: Arrows connect sequential data points, indicating the order of `gamets_truncated` (ascending, oldest to newest). These lines can be toggled on/off via a switch in the UI.
*   **Information Panel**: A persistent panel on the right side of the screen displays the `summary` and `companions` data for a clicked point.
*   **Custom Background & Favicon**: Supports a custom background image and a favicon.

## File Structure

```
/ (Plugin Root)
├── api.php                 # PHP backend to fetch data from PostgreSQL.
├── composer.json           # PHP dependencies (currently minimal).
├── database.php            # Hardcoded database connection details.
├── index.html              # Main HTML file with Three.js visualization logic.
├── manifest.json           # Plugin manifest for CHIM integration.
├── README.md               # This file.
├── images/                 # Optional: For storing images like background.jpg, favicon.ico
│   ├── background.jpg      # Example background image name
│   └── favicon.ico         # Example favicon name
└── vendor/                 # Created by Composer, contains PHP dependencies.
```

## Prerequisites

1.  **Web Server**: An Apache (or similar) web server with PHP support.
2.  **PHP**: Version 8.0 or higher.
    *   **`pdo_pgsql` extension**: Must be enabled in your PHP configuration to allow connection to PostgreSQL.
3.  **Composer**: PHP dependency manager ([getcomposer.org](https://getcomposer.org/)).
4.  **PostgreSQL Database**:
    *   A running PostgreSQL server.
    *   A database named `dwemer`.
    *   A user `dwemer` with password `dwemer` having access to the `dwemer` database (credentials are hardcoded in `database.php`).
    *   A table named `memory_summary` with at least the following columns:
        *   `summary` (text)
        *   `embedding` (text, storing vector data like "[0.1,0.2,0.3,...]")
        *   `companions` (text, can be NULL)
        *   `gamets_truncated` (bigint or integer, used for point sizing and determining sequence for lines/coloring)

## Setup Instructions

1.  **Database & Table**:
    *   Ensure your PostgreSQL server is running and the `dwemer` database exists.
    *   Ensure the `memory_summary` table exists with the required columns and data.
    *   Verify the user `dwemer` has the necessary permissions.

2.  **PHP `pdo_pgsql` Extension**:
    *   Check if the `pdo_pgsql` extension is enabled in your `php.ini` file. If not, enable it and restart your web server.
    *   You can typically check by creating a PHP file with `<?php phpinfo(); ?>` and looking for a `pdo_pgsql` section.

3.  **Deploy Files**:
    *   Place all the project files ( `api.php`, `index.html`, `database.php`, `composer.json`, `manifest.json`, and the `images` folder if used) into a directory accessible by your web server (e.g., `/var/www/html/your-plugin-name` or an `/ext/your-plugin-name` directory if using the CHIM installer structure).

4.  **Install Dependencies**:
    *   Navigate to the root directory of the plugin in your terminal.
    *   Run the command: `composer install`
    *   This will create the `vendor` directory.

5.  **Web Server Configuration (if needed)**:
    *   Ensure your web server is configured to serve PHP files from the chosen directory.
    *   If using Apache, an `.htaccess` file might be needed for cleaner URLs or to ensure `index.html` is the default, though the current setup uses direct file access.

## How to Use

1.  Open your web browser.
2.  Navigate to the `index.html` file via your web server.
    *   Example: `http://localhost/your-plugin-name/index.html` or `http://yourserver.com/ext/CHIM-MindMap/index.html` (if using the `config_url` from `manifest.json` as a base).

3.  **Interact with the Visualization**:
    *   **Navigate**: Use mouse (left-click drag to orbit, scroll wheel to zoom, right-click drag to pan) to explore the 3D scene.
    *   **Hover Points**: Hovering over a data point (sphere) will change the mouse cursor to a pointer and highlight the point by changing its color to red and slightly increasing its size.
    *   **Click Points**: Clicking on a data point will display its `summary` and `companions` information in the panel on the right-hand side of the screen.
    *   **Toggle Lines**: Use the "Show Lines" switch in the top-left corner to toggle the visibility of the arrows connecting the data points.
    *   **Background Click**: Clicking on the scene background (not on a point) will reset the information panel to its default message.

## Customization

*   **Database Credentials**: Modify `database.php` if your PostgreSQL details (host, port, dbname, user, password) are different.
*   **Point Scaling (`scaleFactor`)**: In `index.html`, inside the `visualizeData` function, the `scaleFactor` (currently `100`) can be adjusted to globally increase or decrease the spread of points.
*   **Point Size Range (`minPointSize`, `maxPointSize`)**: In `index.html` (`visualizeData`), these constants control the minimum and maximum radius of the spheres based on `gamets_truncated`. They are also influenced by `scaleFactor`.
*   **Arrowhead Appearance**: The size and proportions of arrowheads can be tuned within the loop that creates `ArrowHelper` objects in `visualizeData`.
*   **Colors**: Start/end point colors, line colors, and HSL parameters for intermediate points can be changed in `visualizeData`.
*   **Background/Favicon**: Replace `images/background.jpg` and `images/favicon.ico` with your own files, or update the paths in `index.html` if you place them elsewhere or use different filenames. 