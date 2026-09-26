# Aesthete

Aesthete is a two-seat connectivity puzzle. Each seat owns a source and a gauge on one shared board. A turn places a pipe on a leak in that seat’s own line, or discards the piece. The line counts only when every opening meets another opening and the gauge is connected. Opening the valve while anything is still open loses the seat.

The playable portfolio project is `aesthete-demo/`. It is a static browser page, not a change to the PHP match. It is also its own git history on the branch `cursor/aesthete-standalone-2798`, ready to become `Phantasyx/aesthete-demo` when that repository can be created. The automation token can push here and cannot create organization repositories.

The PHP application stores a shared turn in MySQL. Each browser talks to that server, and the waiting seat reloads every two seconds. The browsers do not connect to each other.

## Portfolio page

Copy `aesthete-demo/public/` onto the marketing Worker so it is served at `/examples/aesthete/`:

- `aesthete-demo/public/index.html`
- `aesthete-demo/public/styles.css`
- `aesthete-demo/public/rules.js`
- `aesthete-demo/public/game.js`

See `aesthete-demo/README.md` for local play, the optional Worker config, and how to push the standalone branch into an empty `Phantasyx/aesthete-demo` repository.

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

The PHP board still requests tile art from the old class-project image host. Those images are not in this repository. Use `aesthete-demo/public/` when you need a self-contained board.

## Credentials

Database settings used to be hard-coded in `lib/localize.inc.php`. That password was removed. Rotate it anywhere it was used, and keep new values in `.env` or the host’s environment. Do not commit `.env`.

Account email and password updates use bound parameters. Password links use `AESTHETE_PUBLIC_ORIGIN` plus `AESTHETE_ROOT` instead of a fixed host.

## Deploy notes for phantasyx.com

1. Copy `aesthete-demo/public/` so it is served at `https://phantasyx.com/examples/aesthete/`.
2. Leave the PHP application off the marketing Worker. It needs MySQL and a session store.
3. The page credits PhantasyX. PhantasyX Studios is named only in the footer, for this interactive study.
