# Aesthete

Aesthete is a two-seat connectivity puzzle. Each seat owns a source and a gauge on one shared board. A turn places a pipe on a leak in that seat’s own line, or discards the piece. The line counts only when every opening meets another opening and the gauge is connected. Opening the valve while anything is still open loses the seat.

The playable portfolio project is `throughline/`, hosted at `throughline.phantasyx.com`. The page is static, and two browsers share a table through the Worker in `throughline/src/worker.js` (one Durable Object per room). It is not a change to the PHP match. Its own git history is the branch `cursor/throughline-demo-2798`, ready to become `Phantasyx/throughline` when that repository can be created. The automation token can push here and cannot create organization repositories.

The PHP application stores a shared turn in MySQL. Each browser talks to that server, and the waiting seat reloads every two seconds. The browsers do not connect to each other.

## Portfolio page

Build output is `throughline/dist/`, produced by `node throughline/scripts/build.mjs`. Host that directory with the Worker for `throughline.phantasyx.com`. See `throughline/HOSTING.md`. Local two-browser play is `node throughline/scripts/play.mjs`. A path under `/examples/` is only a fallback if the subdomain cannot be added.

- `throughline/dist/index.html`
- `throughline/dist/styles.css`
- `throughline/dist/rules.js`
- `throughline/dist/game.js`
- `throughline/dist/audio/factory.mp3`

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

The PHP board still requests tile art from the old image host. Those images are not in this repository. Use `throughline/dist/` when you need a self-contained board.

## Credentials

Database settings used to be hard-coded in `lib/localize.inc.php`. That password was removed. Rotate it anywhere it was used, and keep new values in `.env` or the host’s environment. Do not commit `.env`.

Account email and password updates use bound parameters. Password links use `AESTHETE_PUBLIC_ORIGIN` plus `AESTHETE_ROOT` instead of a fixed host.

## Deploy notes for phantasyx.com

1. Deploy `throughline/dist/` to the Worker named `throughline` and attach the custom domain `throughline.phantasyx.com`. Steps are in `throughline/HOSTING.md`.
2. Leave the PHP application off that host. It needs MySQL and a session store.
3. The page credits PhantasyX. Do not deploy this Worker from an unattended session unless you own the zone.
