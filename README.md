# Conduit

A static connectivity study for [PhantasyX](https://phantasyx.com/), hosted at [conduit.phantasyx.com](https://conduit.phantasyx.com/).

Each seat owns a source and a gauge on one shared board. A turn places a pipe on a leak in that seat’s own line, or discards the piece. The line counts only when every opening meets another opening and the gauge is connected. Opening the valve while anything is still open loses the seat.

This is not a peer-to-peer network. The pipe rules come from the 2017 Aesthete study, which stored the shared turn in MySQL. Each browser talked to PHP, and the waiting seat reloaded every two seconds. The browsers never connected to each other. That server remains in [Phantasyx/Aesthete](https://github.com/Phantasyx/Aesthete). Conduit plays the same rules in the browser. The board is not uploaded.

The page is a PhantasyX example. PhantasyX Studios is named only in the footer, for this study.

## Build

Source files are in `site/`. The deployable build is `dist/`.

```bash
node scripts/build.mjs
```

That copies `site/` into `dist/`. Wrangler uploads `dist/` only.

## Play the build

```bash
python3 -m http.server 8080 --directory dist
```

Open `http://127.0.0.1:8080/`.

- **One line** is a solo puzzle.
- **Two seats** is pass-and-play on one device.
- **Show a sealed line** walks a finished route from source to gauge.
- **Fit the leak** selects a piece that can continue the line. You still click the cell to place it.

```bash
node test/rules.test.js
```

What the last browser pass checked is in `E2E.md`. How to attach the subdomain is in `HOSTING.md`.

## Repository

This project is meant to be `Phantasyx/conduit`. Creating organization repositories is not granted to the automation token. Until that empty public repo exists, the standalone history is the branch `cursor/conduit-demo-2798` on [Phantasyx/Aesthete](https://github.com/Phantasyx/Aesthete/tree/cursor/conduit-demo-2798).

```bash
git fetch origin cursor/conduit-demo-2798
git push https://github.com/Phantasyx/conduit.git FETCH_HEAD:main
```

There are no secrets in this project. Do not add any.
