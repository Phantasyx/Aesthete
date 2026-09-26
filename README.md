# Throughline

A static connectivity study for [PhantasyX](https://phantasyx.com/), hosted at [throughline.phantasyx.com](https://throughline.phantasyx.com/).

Each seat owns a source and a gauge on one shared board. A turn places a pipe on a leak in that seat’s own line, or discards the piece. The line counts only when every opening meets another opening and the gauge is connected. Opening the valve while anything is still open loses the seat.

This is not a peer-to-peer network. The pipe rules come from the 2017 Aesthete study, which stored the shared turn in MySQL. Each browser talked to PHP, and the waiting seat reloaded every two seconds. The browsers never connected to each other. That server remains in [Phantasyx/Aesthete](https://github.com/Phantasyx/Aesthete). Throughline plays the same rules in the browser. The board is not uploaded.

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
- **Sound** starts a looping factory ambience. It does not play until that control is pressed.

The loop is “Factory ambiance” by yd, dedicated to the public domain (CC0) on [OpenGameArt](https://opengameart.org/content/factory-ambiance). The file in `site/audio/` is a 96 kbps MP3 re-encode of that upload. Credit is in the page footer.

```bash
node test/rules.test.js
```

What the last browser pass checked is in `E2E.md`. How to attach the subdomain is in `HOSTING.md`.

## Repository

This project is meant to be `Phantasyx/throughline`. Creating organization repositories is not granted to the automation token. Until that empty public repo exists, the standalone history is the branch `cursor/throughline-demo-2798` on [Phantasyx/Aesthete](https://github.com/Phantasyx/Aesthete/tree/cursor/throughline-demo-2798).

```bash
git fetch origin cursor/throughline-demo-2798
git push https://github.com/Phantasyx/throughline.git FETCH_HEAD:main
```

There are no secrets in this project. Do not add any.
