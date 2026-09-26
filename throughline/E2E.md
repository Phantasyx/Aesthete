# End-to-end notes

Checked on 26 September 2026. The board behavior below was checked before the rename, on the same pipe rules. After the page became Throughline, a second pass rechecked the name, the host, the board size, and the sound control against `dist/` at `http://127.0.0.1:8092/`. Rule tests (`node test/rules.test.js`) passed after the rename, and `node scripts/build.mjs` left `dist/` identical to `site/`.

## What passed

- The document title is `Throughline · PhantasyX` and the heading is `Throughline`. The visible page does not say Conduit.
- The canonical URL is `https://throughline.phantasyx.com/`. The page names that host and does not tell anyone to use `/examples/aesthete/`. The connection note says “That sealed path is the throughline.”
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

## Rename

Rechecked the same day on `http://127.0.0.1:8092/` after Conduit was renamed Throughline.

- Title, heading, and canonical URL match the list above. Body text includes `throughline.phantasyx.com` and does not include Conduit.
- The heading fits on a 1280px-wide window and on a 390px-wide window. Neither viewport scrolls sideways. The board still has 48 cells, one live leak, and five pieces.
- Sound is paused on load. A real click starts the loop (`currentTime` moved from about 0.31s to 0.71s) and the control reads Sound on.
- In the desktop browser the heading read Throughline, the sealed-path sentence was on screen, and Sound changed to Sound on.

## Polish

Checked on 26 September 2026 against `dist/` at `http://127.0.0.1:8093/`, after the public copy was rewritten and boards were randomized. `node test/rules.test.js` passed, including twenty generated seeds that all seal and are not all the same route.

- The title is `Throughline · PhantasyX`. The kicker is Interaction. Visible text does not include Aesthete, Conduit, MySQL, PHP, CC0, Studios, semester, professor, coursework, or study.
- The page describes the example as interaction craft for PhantasyX, and the footer says the pipe rules began as earlier personal work.
- The first board’s solution route was `1,0 1,1 2,1 2,0 3,0 4,0 4,1 5,1 6,1`. New board changed it to `1,5 2,5 3,5 4,5 5,5 5,4 4,4 4,3 5,3 6,3`. A third board was `1,3 1,4 2,4 3,4 4,4 4,3 5,3 6,3`.
- Opening the valve on an unfinished line did not show the celebration. It played a lower two-note tone (220 Hz, then 165 Hz) from that click. The status said the valve opened while the line was still unfinished.
- Watch a finished line still played the rising chime (523 Hz, 659 Hz, 784 Hz) with the banner. The miss tone and the win chime are different sounds.
- Watch a finished line ended with the banner “The line is sealed.”, sixteen rising dots, a gold ring on the board, and three chime tones started from that click. The status read “The line is sealed. Every opening meets another opening, and the gauge is connected.”
- At 390px the heading still fit, the document did not scroll sideways, and the board still had 48 cells.
- In the desktop browser, New board moved the source and the gold mark, and Watch a finished line ended on the same banner.

## What this did not do

This pass did not deploy to Cloudflare and did not open `https://throughline.phantasyx.com/`. That name is attached by `npx wrangler deploy` from an account that owns the `phantasyx.com` zone, as written in `HOSTING.md`.
