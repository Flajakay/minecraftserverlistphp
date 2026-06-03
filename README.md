# Minecraft Server List Platform (Work in progress)

A modern, high-performance, MVC-based Minecraft server listing platform built with **PHP 8.2+**, **Bootstrap 5**, and a robust custom architecture. 

This platform provides everything needed to run a Minecraft server listing website, including concurrent server pinging, daily voting with Votifier, user verification, PayPal-powered server highlighting, rate limiting, and multi-language support.

---

## 🚀 Key Features

*   **⚡ Concurrent Async Pinger **: Pings multiple Minecraft servers concurrently using non-blocking TCP sockets and multiplexing via `stream_select()`. Capable of checking scores of servers in seconds, preventing script timeouts.
*   **🔑 Server Ownership Claims**: A self-service verification system. Users claim servers by adding a generated verification token to their Minecraft server's MOTD. The platform verifies this in real-time by query-pinging the server.
*   **🛡️ Leaky-Bucket Rate Limiter**: Granular rate limiting built on Stiphle's leaky-bucket algorithm. It utilizes fast in-memory **APCu** storage (with a transparent fallback to a database/file-based `FileStorage`). Configurable limits protect sensitive routes:
    *   *Authentication*: Max 5 requests / 5 mins (IP-bound).
    *   *Voting*: Max 10 requests / 1 min (IP-bound).
    *   *Server Actions*: Max 5 requests / 1 min (User-bound).
    *   *Payments*: Max 10 requests / 5 mins (User-bound).
    *   *General*: 150 page views / 1 min.
*   **🗳️ Votifier Integration**: Supports sending daily vote notifications to Minecraft game servers. Once a user votes, the platform encrypts the vote packet using RSA public keys and sends it via socket connection directly to the server's Votifier listener.
*   **💳 PayPal v2 REST Integration**: Secure purchasing of server highlight days using the official PayPal Server SDK. Features server-side amount calculation, duration validation (e.g., min/max configurable days), and cryptographically verified transaction captures.
*   **🎨 Automated Favicon Generator Suite**: Administrators can upload a single high-resolution square image (min 512x512px). The `FaviconManager` automatically:
    *   Resizes and generates a complete set of `.png` icons (`16x16`, `32x32`, `180x180`, `192x192`, `512x512`).
    *   Compiles a binary `favicon.ico` containing the formatted icons.
    *   Creates a `site.webmanifest` configuration file.
*   **🌐 Multilingual Engine**: Built-in support for translations (includes complete English and Polish localizations). Detects preferences automatically and persists selected locales via cookies.
*   **📝 Jodit Rich Text Editor**: Sleek content editor integration for writing blog posts and customizable static pages with safe HTML purifying validation.
*   **🔒 Hardened Security**:
    *   **SSRF Protection Shield**: The `resolveSafeHostIp` helper resolves server hostnames and strictly blocks internal and private IP ranges (like `127.0.0.0/8`, `10.0.0.0/8`, `192.168.0.0/16`, etc.) on all outgoing socket queries.
    *   **CSRF Protection**: Automatically generated tokens protect all state-changing HTTP requests.
    *   **Session IP Binding**: Binds active session states to the user's remote IP address to defeat session-hijacking attempts.
    *   **Brute-Force Shield**: Failed login attempts lock out both usernames and IP addresses.

---

## 🛠️ System Requirements

*   **PHP 8.2 or higher**
*   **Required PHP Extensions**:
    *   `gd` (for image manipulation and favicon resizing)
    *   `sockets` (for Votifier and server query connections)
    *   `openssl` (for Votifier RSA encryption)
    *   `curl` (for PayPal API communications)
    *   `pdo` & `pdo_mysql` (for database access)
    *   `fileinfo` (for secure upload MIME-type checking)
    *   `apcu` *(highly recommended for high-performance rate limiting)*
*   **MySQL 8.0+** or similar

---

## 🔧 Installation & Setup

1.  **Clone and Install Dependencies**  
    Clone this repository to your web server directory and install dependencies via Composer:
    ```bash
    composer install --no-dev
    ```

2.  **Run the Installer**  
    Navigate to the project directory in your browser (e.g., `http://localhost:8080/install.php` or your domain). The installer will guide you through:
    *   Verifying requirements and directory permissions.
    *   Creating/connecting the MySQL database.
    *   Creating the database tables and running schema migrations.
    *   Setting up the default admin account credentials.
    *   Generating the initial `.env` file and creating the `storage/installed.lock` file.

3.  **Post-Installation Cleaning**  
    Once installation completes, delete the `install.php` file from the root directory for security:
    ```bash
    rm install.php
    ```
    *Note: Default administrator account is `admin` / `admin`.*

4.  **Set Up the Cron Job**  
    To perform real-time background pinging and expire highlighted/featured servers automatically, add a cron job pointing to `cron.php` running every minute (or at your preferred interval):
    ```bash
    * * * * * php /path/to/minecraftserverlistphp/cron.php > /dev/null 2>&1
    ```

---

## 🗄️ Database Migrations

Database structure updates are managed using raw SQL files inside `database/migrations/`. You can check and run migrations directly using the CLI:

*   **Check migration status:**
    ```bash
    php migrate.php status
    ```
*   **Run all pending migrations:**
    ```bash
    php migrate.php run
    ```
*(Alternatively, migrations can be viewed and triggered in the browser via the Admin panel under `/admin/migrations`)*

---

## 🤝 Contributing

Contributions to the project are welcome! To contribute:

1. **Install Dev Dependencies**: Ensure you run `composer install` without `--no-dev` to install testing and formatting tools.
2. **Write & Run Tests**: Include unit/feature tests for any bug fixes or new features under the `tests/` directory. Run the test suite using PHPUnit:
   ```bash
   ./vendor/bin/phpunit
   ```
3. **Submit a Pull Request**: Create a branch, push your changes, and open a clear Pull Request outlining the changes made.

---

## 🛡️ License

This project is open-source software. Please refer to the [LICENCE](file:///data/Development/minecraftserverlistphp/minecraftserverlistphp/LICENCE) file for original licensing terms.
