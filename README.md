# Throughline

A small interactive example for [PhantasyX](https://phantasyx.com/), hosted at [throughline.phantasyx.com](https://throughline.phantasyx.com/).

Each seat owns a source and a gauge on one shared board. A turn places a pipe on an open end in that seat’s own line, or discards the piece. The line counts only when every opening meets another opening and the gauge is connected. Opening the valve while anything is still open loses the line. New board deals a different route, and each route can be finished.

The page runs in the browser. Nothing is uploaded. The pipe rules began as earlier personal work. An older PHP and MySQL version remains in [Phantasyx/Aesthete](https://github.com/Phantasyx/Aesthete). This page does not use it.

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

- **One line** is a solo board.
- **Two seats** is pass-and-play on one device.
- **New board** deals another solvable route.
- **Watch a finished line** plays one solution for a fresh board.
- **Fit the opening** turns a piece so it can continue the line. You still click the cell to place it.
- **Sound** starts a looping factory ambience. It does not play until that control is pressed. Opening the valve plays a short rising chime when the line is sealed, and a lower tone when the line is still open. Both sounds start from that click.

The loop is “Factory ambiance” by yd, released into the public domain on [OpenGameArt](https://opengameart.org/content/factory-ambiance). The file in `site/audio/` is a 96 kbps MP3 re-encode of that upload. Credit is in the page footer.

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
