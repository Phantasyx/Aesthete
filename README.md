# Aesthete

Aesthete is a two-seat connectivity puzzle. Each seat owns a source and a gauge on one shared board. A turn places a pipe on a leak in that seat’s own line, or discards the piece. The line counts only when every opening meets another opening and the gauge is connected. Opening the valve while anything is still open loses the seat.

This repository has two ways to run it.

- The portfolio page in `examples/aesthete/` plays in the browser with no server. That is the version to put on phantasyx.com.
- The original PHP application stores a shared turn in MySQL. Each browser talks to that server, and the waiting seat reloads every two seconds. The browsers do not connect to each other.

## Portfolio page

Serve this folder at `/examples/aesthete/` on the marketing site. Copy these files onto the Worker’s static root (for example `public/examples/aesthete/`):

- `examples/aesthete/index.html`
- `examples/aesthete/styles.css`
- `examples/aesthete/rules.js`
- `examples/aesthete/game.js`

Links inside the page are relative, so the folder can move as a unit. There is no build step and no database.

Preview it locally with any static file server:

```bash
cd examples/aesthete
python3 -m http.server 8080
```

Then open `http://127.0.0.1:8080/`. “One line” is a solo puzzle. “Two seats” is pass-and-play on one device. “Show a sealed line” walks a finished route from source to gauge. “Fit the leak” rotates a piece so it opens toward the current leak, then you still click the cell to place it.

Rule checks for that page:

```bash
node tests/aesthete-rules.test.js
```

## PHP match server

The PHP app is the original shared-turn match. It needs PHP with PDO MySQL and a database. It is not part of the static marketing page.

Configuration comes from the environment, or from a gitignored `.env` file. `lib/localize.inc.php` reads `.env` without overriding variables that are already set.

```bash
cp .env.example .env
```

Set `AESTHETE_DB_DSN`, `AESTHETE_DB_USER`, and `AESTHETE_DB_PASSWORD`. Create the tables with `deploy/schema.sql` (the prefix there matches the default `aesthete_`). An older database that used the prefix `p2_` should set `AESTHETE_TABLE_PREFIX=p2_` instead of running that script against it.

`AESTHETE_ROOT` is the path prefix when the app is not hosted at `/`. Leave it empty for the built-in server. `AESTHETE_PUBLIC_ORIGIN` is the scheme and host used in account email, such as `https://play.example.com`.

```bash
php -S localhost:8080
```

Guest seats get a random password and are signed in from that insert. There is no shared guest password.

The PHP board still requests tile art from the old class-project image host. Those images are not in this repository. Use `examples/aesthete/` when you need a self-contained board.

## Credentials

Database settings used to be hard-coded in `lib/localize.inc.php`. That password was removed. Rotate it anywhere it was used, and keep new values in `.env` or the host’s environment. Do not commit `.env`.

Account email and password updates use bound parameters. Password links use `AESTHETE_PUBLIC_ORIGIN` plus `AESTHETE_ROOT` instead of a fixed host.

## Deploy notes for phantasyx.com

1. Copy the four files listed above so they are served at `https://phantasyx.com/examples/aesthete/`.
2. Leave the PHP application off the marketing Worker. It needs MySQL and a session store.
3. The page credits PhantasyX. PhantasyX Studios is named only in the footer, for this interactive study.
