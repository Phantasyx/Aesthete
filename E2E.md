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
- Watch a finished line ended with the banner “The line is sealed.”, sixteen rising dots, a gold ring on the board, and the rising chime (523 Hz, 659 Hz, 784 Hz). The status read “The line is sealed. Every opening meets another opening, and the gauge is connected.” The miss tone and the win chime are different sounds.
- At 390px the heading still fit, the document did not scroll sideways, and the board still had 48 cells.
- In the desktop browser, New board moved the source and the gold mark, and Watch a finished line ended on the same banner.

## Scored table

Checked on 26 September 2026 against `node scripts/play.mjs` at `http://127.0.0.1:8094/`. `node test/rules.test.js` and `node test/session.test.js` passed. Clicks were real mouse events in headless Chrome.

- Solo starts at score 0 with 18 pipes in the tray. The note reads “Perfect fill is 18 pipes. 18 still in the tray.”
- Scrap pipe leaves 17 pipes. The note reads “Perfect fill is 18 pipes. 17 still in the tray. The best you can still reach is 17.” The status says that pipe is scrapped and the best score still reachable is 17.
- Seal the line on that unfinished board does not celebrate. The score kicker reads “Not banked”, the score stays 0, and the status says the score does not bank. The click played 220 Hz, then 165 Hz.
- The controls read Rotate, Scrap pipe, Seal the line, Forfeit, Watch a perfect fill, New puzzle, Solo, and Two browsers. There is no Fit control and no pass-and-play control.
- Two browsers: the host opened table `M8J9` as Copper. The guest sat down as Teal. Both boards had seed `3721071773` and the same route.
- The host scrapped one pipe. The host’s tray went to 17, the host’s turn label became “Waiting on Teal”, and Scrap pipe was disabled. The guest’s score read “0 · 0” and the note read “Copper has 0 laid and 17 left. Teal has 0 laid and 18 left. A perfect fill is 18 pipes on each side.” The guest’s turn label was “Teal to move”.
- At 390px the document did not scroll sideways (`scrollWidth` 390) and the board still had 48 cells.

## Product copy

Checked on 26 September 2026 against `http://127.0.0.1:8094/`, after the public wording was rewritten.

- The meta description, lede, and footer describe sealing a path from the source to the gauge. The notes section is titled “Two browsers” and explains the shared table.
- Visible text does not include “An example”, “Why it is here”, “personal work”, “portfolio”, or “small PhantasyX example”.
- The header still links the PhantasyX name to `https://phantasyx.com/`.
- At 390px the document did not scroll sideways, and the board still had 48 cells.

## What this did not do

This pass did not deploy to Cloudflare and did not open `https://throughline.phantasyx.com/`. The Worker and the Durable Object are in the repo. Attaching the hostname is still `npx wrangler deploy` from an account that owns the `phantasyx.com` zone, as written in `HOSTING.md`.
