# Throughline

Seal a path from the source to the gauge. Hosted at [throughline.phantasyx.com](https://throughline.phantasyx.com/).

Each seat owns a source and a gauge on one shared board. The tray starts as the exact set of pipes that fill that seat’s side. Laying a pipe raises the score. Scrapping a pipe removes it for good, so a shorter run can still seal and banks a lower score. Opening the valve while anything is still open does not bank the score. In a two-browser table, that failed valve gives the round to the other seat.

Solo play stays in the page. Two browsers share one table through a small session service: the local play server, or a Cloudflare Durable Object in production. The waiting browser polls for the next turn. An older PHP and MySQL version remains in [Phantasyx/Aesthete](https://github.com/Phantasyx/Aesthete). This page does not use it.

## Build

Source files are in `site/`. The deployable build is `dist/`.

```bash
node scripts/build.mjs
```

That copies `site/` into `dist/`. Wrangler uploads `dist/` and the Worker in `src/worker.js`.

## Play the build

```bash
node scripts/build.mjs
node scripts/play.mjs
```

Open `http://127.0.0.1:8094/`. That process serves `dist/` and the table API on the same origin. A static file server can still show the page, and solo play works there, but two browsers need `scripts/play.mjs` or the deployed Worker.

- **Solo** deals one puzzle on this page.
- **Two browsers** opens a table. Send the four character code to the other player and they sit down. Turns alternate. The host deals the next puzzle.
- **Rotate** turns the selected pipe. **Scrap pipe** throws it out of the tray.
- **Seal the line** banks the score when the gauge is connected and every opening is closed.
- **Forfeit** gives up the puzzle.
- **Watch a perfect fill** plays one full route on a fresh solo board.
- **New puzzle** deals another board. On a shared table, only the host can do that.
- **Sound** starts a looping factory ambience. It does not play until that control is pressed. Sealing the line plays a short rising chime. Opening the valve on an unfinished line plays a lower tone. Both sounds start from that click.

The loop is “Factory ambiance” by yd, released into the public domain on [OpenGameArt](https://opengameart.org/content/factory-ambiance). The file in `site/audio/` is a 96 kbps MP3 re-encode of that upload. Credit is in the page footer.

```bash
node test/rules.test.js
node test/session.test.js
```

What the last browser pass checked is in `E2E.md`. How to attach the subdomain is in `HOSTING.md`.

## Repository

This project is meant to be `Phantasyx/throughline`. Creating organization repositories is not granted to the automation token. Until that empty public repo exists, the standalone history is the branch `cursor/throughline-demo-2798` on [Phantasyx/Aesthete](https://github.com/Phantasyx/Aesthete/tree/cursor/throughline-demo-2798).

```bash
git fetch origin cursor/throughline-demo-2798
git push https://github.com/Phantasyx/throughline.git FETCH_HEAD:main
```

There are no secrets in this project. Do not add any.
