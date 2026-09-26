# End-to-end notes

Checked on 26 September 2026 against the built files in `dist/`, served locally at `http://127.0.0.1:8091/`. Chrome headless drove the page. Rule tests (`node test/rules.test.js`) passed first, and `node scripts/build.mjs` left `dist/` identical to `site/`.

## What passed

- The document title is `Conduit · PhantasyX` and the heading is `Conduit`.
- The canonical URL is `https://conduit.phantasyx.com/`. The page names that host and does not tell anyone to use `/examples/aesthete/`.
- `styles.css`, `rules.js`, and `game.js` are relative URLs, so the same `dist/` works at the subdomain root. A second stylesheet is the Google Fonts request for Fraunces and Outfit. The CSS falls back to Palatino and system sans if that request fails.
- The board is 48 cells (8 by 6) with one live leak and five pieces.
- Rotate changed the selected piece from “Opens north.” to “Opens east.”
- Fit the leak, then click the live cell: “The leak moved to the new open end. 2 cells are marked.” One pipe was on the board and the line still had open ends.
- Open valve on that unfinished line: “The valve opened onto an unfinished line. A bare opening, including one that leaves the board, loses the seat.” The valve control was then disabled.
- Two seats: two sources, status “Two seats, one board. The upper line moves first. Pass the device when the turn changes. Nothing is sent to a server.” The turn label started at “Upper line to move”. Discard moved it to “Lower line to move”.
- Show a sealed line: the first step read “Piece 1 of 7. Opens east and west.” The finished status was “Sealed. Every opening meets another opening, and the gauge is in the line.” Seven pipes were on the board and the steam animation was running.
- At a 390px-wide viewport the document did not scroll sideways, the heading was still Conduit, and the board still had 48 cells.

## What this did not do

This pass did not deploy to Cloudflare and did not open `https://conduit.phantasyx.com/`. That name is attached by `npx wrangler deploy` from an account that owns the `phantasyx.com` zone, as written in `HOSTING.md`.
