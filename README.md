# Aesthete

A browser rebuild of a two-seat connectivity study, made to be shown on [phantasyx.com](https://phantasyx.com/) at `/examples/aesthete/`.

Each seat owns a source and a gauge on one shared board. A turn places a pipe on a leak in that seat’s own line, or discards the piece. The line counts only when every opening meets another opening and the gauge is connected. Opening the valve while anything is still open loses the seat.

This is not a peer-to-peer network. The 2017 version stored the shared turn in MySQL. Each browser talked to a PHP application, and the waiting seat reloaded every two seconds. The browsers never connected to each other. That server still lives in [Phantasyx/Aesthete](https://github.com/Phantasyx/Aesthete). This repository is the version that can be hosted as static files.

The page is a PhantasyX consultancy example. PhantasyX Studios is named only in the footer, for this interactive study.

## Play it

```bash
cd public
python3 -m http.server 8080
```

Open `http://127.0.0.1:8080/`.

- **One line** is a solo puzzle.
- **Two seats** is pass-and-play on one device.
- **Show a sealed line** walks a finished route from source to gauge.
- **Fit the leak** rotates a piece that can continue the line. You still click the cell to place it.

Rule checks:

```bash
node test/rules.test.js
```

## Host on phantasyx.com

Copy the contents of `public/` onto the marketing Worker’s static root so they are served at `/examples/aesthete/`:

- `public/index.html`
- `public/styles.css`
- `public/rules.js`
- `public/game.js`

There is no build step. Links inside the page are relative, so the same four files work at that path or at the root of their own host.

## Optional Worker

`wrangler.jsonc` points at `public/` and has no Worker script and no bindings. A standalone deploy serves the demo at `/` on that Worker, not at `/examples/aesthete/`. The marketing site should copy the files, rather than replace the consultancy site with this project.

```bash
npx wrangler@4 dev
```

Do not put secrets in this repository. The demo does not need any.

## Where this git history lives

This project is meant to be its own repository, `Phantasyx/aesthete-demo`. The automation token can push to existing Phantasyx repositories and cannot create new ones (`createRepository` is not granted). Until an owner creates that empty public repository, this history is the branch `cursor/aesthete-standalone-2798` on [Phantasyx/Aesthete](https://github.com/Phantasyx/Aesthete).

After the empty repo exists:

```bash
git remote add portfolio https://github.com/Phantasyx/aesthete-demo.git
git push portfolio cursor/aesthete-standalone-2798:main
```
