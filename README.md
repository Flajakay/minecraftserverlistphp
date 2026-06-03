# Game Server List Platform (Work in progress)

A modern game server listing platform built with **PHP 8.2+** and **Bootstrap 5**. List and promote servers for **Minecraft Java** and **Steam/Source** games (CS2, TF2, Rust, ARK, and more) through a single website.

---

## 🚀 Key Features

*   **Multi-Protocol Support**: Supports both Minecraft Java and Steam/Source Query (A2S) protocols. Adding a new protocol is straightforward — just implement a simple interface.
*   **Fast Batch Pinging**: Checks many servers at once instead of one by one. Uses smart routing — Minecraft servers ping concurrently over TCP, Steam servers query individually over UDP. All servers get updated status in seconds.
*   **Game Definitions**: Common Steam games are pre-loaded (CS2, Team Fortress 2, Rust, ARK, Garry's Mod, and many more). Admins can add, edit, or disable games from the admin panel.
*   **Server Ownership Verification**: Minecraft Java server owners can prove ownership by adding a short token to their server MOTD. (Not available for Steam servers in this version.)
*   **Rate Limiting**: Protects against spam and abuse — login attempts, voting, server actions, and payments are all rate-limited with configurable thresholds.
*   **Voting System**: Players can vote for their favorite servers once per day. For Minecraft Java servers, votes can be forwarded directly to the server via Votifier.
*   **Premium Highlights**: Server owners can purchase highlighted listings via PayPal to attract more players. Duration and pricing are fully configurable.
*   **Multi-Language**: Built-in English and Polish translations. Easy to add more.
*   **Security**: SSRF protection, CSRF tokens, session IP binding, and brute-force lockout.

---

## 🛠️ Requirements

*   **PHP 8.2+** with extensions: `gd`, `sockets`, `openssl`, `curl`, `pdo`, `pdo_mysql`, `fileinfo`
*   **MySQL 8.0+** (or compatible)

---

## 🔧 Installation

```bash
composer install --no-dev
```

Open `install.php` in your browser and follow the wizard. It will create the database, run migrations, and set up your admin account.

Once done, remove `install.php` and set up a cron job to keep server status up to date:

```bash
* * * * * php /path/to/your/site/cron.php > /dev/null 2>&1
```

Default admin login: `admin` / `admin`

---

## 🗄️ Database Migrations

```bash
php migrate.php status   # see what's applied and what's pending
php migrate.php run      # apply pending migrations
```

Migrations can also be run from the admin panel at `/admin/migrations`.

---

## 🧪 Running Tests

```bash
composer test
```

The test suite uses a separate `serverlist_test` database and runs each test in its own transaction.

---

## 🤝 Contributing

Pull requests are welcome. Please include tests for any new features.

---

## 📄 License

See [LICENSE](LICENSE).
