# End-to-end notes

Checked on 26 September 2026 against the built files in `dist/`, served locally at `http://127.0.0.1:8091/`. Chrome headless drove the page. Rule tests (`node test/rules.test.js`) passed first, and `node scripts/build.mjs` left `dist/` identical to `site/`.

## What passed

- The document title is `Throughline · PhantasyX` and the heading is `Throughline`.
- The canonical URL is `https://throughline.phantasyx.com/`. The page names that host and does not tell anyone to use `/examples/aesthete/`.
- `styles.css`, `rules.js`, and `game.js` are relative URLs, so the same `dist/` works at the subdomain root. A second stylesheet is the Google Fonts request for Fraunces and Outfit. The CSS falls back to Palatino and system sans if that request fails.
- The board is 48 cells (8 by 6) with one live leak and five pieces.
- Rotate changed the selected piece from “Opens north.” to “Opens east.”
- Fit the leak, then click the live cell: “The leak moved to the new open end. 2 cells are marked.” One pipe was on the board and the line still had open ends.
- Open valve on that unfinished line: “The valve opened onto an unfinished line. A bare opening, including one that leaves the board, loses the seat.” The valve control was then disabled.
- Two seats: two sources, status “Two seats, one board. The upper line moves first. Pass the device when the turn changes. Nothing is sent to a server.” The turn label started at “Upper line to move”. Discard moved it to “Lower line to move”.
- Show a sealed line: the first step read “Piece 1 of 7. Opens east and west.” The finished status was “Sealed. Every opening meets another opening, and the gauge is in the line.” Seven pipes were on the board and the steam animation was running.
- At a 390px-wide viewport the document did not scroll sideways, the heading was still Throughline, and the board still had 48 cells.

## Sound

Checked again on 26 September 2026, same local server, after the factory loop was added. `node test/rules.test.js` still passed, and `dist/` still matched `site/`.

- On load the audio is paused. The header control reads Sound, `aria-pressed` is false, volume is 0.35, and the element loops `audio/factory.mp3` with `preload="none"`.
- A scripted `element.click()` does not start playback.
- A real mouse click starts it. The control reads Sound on, `aria-pressed` is true, and `currentTime` moved from about 0.39s to 1.10s.
- A second real click pauses it and the label returns to Sound.
- The footer reads “Factory ambiance by yd, CC0, via OpenGameArt. Sound stays off until you turn it on.”
- `http://127.0.0.1:8091/audio/factory.mp3` returned 200 with `Content-Type: audio/mpeg` and `Content-Length: 1476799`.
- At 390px the document still did not scroll sideways while the loop was playing. The heading stayed Throughline.
- In the desktop browser the pill went from an outline Sound, to a filled Sound on, and back to an outline Sound. The footer credit was on screen after scrolling.

## What this did not do

This pass did not deploy to Cloudflare and did not open `https://throughline.phantasyx.com/`. That name is attached by `npx wrangler deploy` from an account that owns the `phantasyx.com` zone, as written in `HOSTING.md`.
