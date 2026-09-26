/**
 * Conduit board rules, shared by the static demo and the node tests.
 * The coordinates follow the 2017 Aesthete pipe study.
 *
 * Coordinates match the 2017 PHP game: x is the column, y is the row.
 * A size of 6 produces an 8 by 6 grid. Each seat has a source on the
 * left and a two-cell gauge on the right. A leak records the opening
 * the next pipe must have in order to connect back into the line.
 */
(function (root, factory) {
  var api = factory();
  if (typeof module === 'object' && module.exports) {
    module.exports = api;
  }
  root.Conduit = api;
})(typeof globalThis !== 'undefined' ? globalThis : this, function () {
  var DIRS = ['N', 'E', 'S', 'W'];
  var DELTA = { N: [0, -1], E: [1, 0], S: [0, 1], W: [-1, 0] };
  var OPPOSITE = { N: 'S', E: 'W', S: 'N', W: 'E' };
  var BASE = {
    cap: { N: false, E: false, S: true, W: false },
    elbow: { N: false, E: false, S: true, W: true },
    straight: { N: false, E: true, S: false, W: true },
    tee: { N: false, E: true, S: true, W: true }
  };

  /**
   * A known sealed route for the upper line on a size-6 board.
   * Used by "Show a sealed line" and by the rules test.
   */
  var UPPER_LINE = [
    { x: 1, y: 0, type: 'straight', rotation: 0 },
    { x: 2, y: 0, type: 'straight', rotation: 0 },
    { x: 3, y: 0, type: 'straight', rotation: 0 },
    { x: 4, y: 0, type: 'straight', rotation: 0 },
    { x: 5, y: 0, type: 'elbow', rotation: 0 },
    { x: 5, y: 1, type: 'elbow', rotation: 2 },
    { x: 6, y: 1, type: 'straight', rotation: 0 }
  ];

  function cloneOpenings(openings) {
    return { N: openings.N, E: openings.E, S: openings.S, W: openings.W };
  }

  function rotateOpenings(openings) {
    return {
      N: openings.W,
      E: openings.N,
      S: openings.E,
      W: openings.S
    };
  }

  function makePiece(type, rotation) {
    var openings = cloneOpenings(BASE[type]);
    var turns = ((rotation % 4) + 4) % 4;
    for (var i = 0; i < turns; i++) {
      openings = rotateOpenings(openings);
    }
    return { type: type, rotation: turns, openings: openings };
  }

  function mulberry32(seed) {
    var a = seed >>> 0;
    return function () {
      a = (a + 0x6D2B79F5) >>> 0;
      var t = Math.imul(a ^ (a >>> 15), 1 | a);
      t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
      return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
  }

  function randomPiece(rng) {
    var types = ['cap', 'elbow', 'straight', 'tee'];
    var type = types[Math.floor(rng() * types.length)];
    var rotation = Math.floor(rng() * 4);
    return makePiece(type, rotation);
  }

  function key(x, y) {
    return x + ',' + y;
  }

  function inBounds(size, x, y) {
    return x >= 0 && y >= 0 && x < size + 2 && y < size;
  }

  function seatLayout(index, size) {
    var mid = size / 2;
    if (index === 0) {
      return {
        source: { x: 0, y: mid - 3 },
        gaugeTop: { x: size + 1, y: mid - 3 },
        gaugeBottom: { x: size + 1, y: mid - 2 }
      };
    }
    return {
      source: { x: 0, y: mid + 2 },
      gaugeTop: { x: size + 1, y: mid },
      gaugeBottom: { x: size + 1, y: mid + 1 }
    };
  }

  function makeSeat(index, size, rng) {
    var layout = seatLayout(index, size);
    var hand = [];
    for (var i = 0; i < 5; i++) {
      hand.push(randomPiece(rng));
    }
    return {
      source: layout.source,
      gaugeTop: layout.gaugeTop,
      gaugeBottom: layout.gaugeBottom,
      pipes: [],
      leaks: [{ x: layout.source.x + 1, y: layout.source.y, dir: 'W' }],
      hand: hand,
      selected: 0
    };
  }

  function networkTiles(seat) {
    return [
      {
        x: seat.source.x,
        y: seat.source.y,
        openings: { N: false, E: true, S: false, W: false },
        fixture: 'source'
      },
      {
        x: seat.gaugeBottom.x,
        y: seat.gaugeBottom.y,
        openings: { N: false, E: false, S: false, W: true },
        fixture: 'gauge'
      },
      {
        x: seat.gaugeTop.x,
        y: seat.gaugeTop.y,
        openings: { N: false, E: false, S: false, W: false },
        fixture: 'gauge-top'
      }
    ].concat(seat.pipes);
  }

  function tileAt(tiles, x, y) {
    for (var i = 0; i < tiles.length; i++) {
      if (tiles[i].x === x && tiles[i].y === y) {
        return tiles[i];
      }
    }
    return null;
  }

  function checkConnection(tile, tiles, size) {
    for (var i = 0; i < DIRS.length; i++) {
      var dir = DIRS[i];
      if (!tile.openings[dir]) {
        continue;
      }
      var nx = tile.x + DELTA[dir][0];
      var ny = tile.y + DELTA[dir][1];
      if (!inBounds(size, nx, ny)) {
        return false;
      }
      var neighbor = tileAt(tiles, nx, ny);
      if (!neighbor || !neighbor.openings[OPPOSITE[dir]]) {
        return false;
      }
    }
    return true;
  }

  function isSealed(seat, size) {
    var tiles = networkTiles(seat);
    var gaugeX = seat.gaugeBottom.x - 1;
    var gaugeY = seat.gaugeBottom.y;
    var gaugeConnected = false;
    for (var i = 0; i < seat.pipes.length; i++) {
      var pipe = seat.pipes[i];
      if (!checkConnection(pipe, tiles, size)) {
        return false;
      }
      if (pipe.x === gaugeX && pipe.y === gaugeY && pipe.openings.E) {
        gaugeConnected = true;
      }
    }
    return gaugeConnected;
  }

  function createGame(options) {
    options = options || {};
    var size = options.size || 6;
    var seats = options.seats === 2 ? 2 : 1;
    var seed = options.seed == null ? (Date.now() >>> 0) : (options.seed >>> 0);
    var rng = mulberry32(seed);
    var players = [];
    var occupied = {};
    for (var i = 0; i < seats; i++) {
      var seat = makeSeat(i, size, rng);
      players.push(seat);
      occupied[key(seat.source.x, seat.source.y)] = true;
      occupied[key(seat.gaugeTop.x, seat.gaugeTop.y)] = true;
      occupied[key(seat.gaugeBottom.x, seat.gaugeBottom.y)] = true;
      occupied[key(seat.leaks[0].x, seat.leaks[0].y)] = true;
    }

    var state = {
      size: size,
      seats: seats,
      seed: seed,
      turn: 0,
      players: players,
      occupied: occupied,
      over: false,
      busy: false,
      winner: null,
      result: null,
      rng: rng
    };

    function current() {
      return state.players[state.turn];
    }

    function passTurn() {
      if (state.seats === 2 && !state.holdTurn) {
        state.turn = 1 - state.turn;
      }
    }

    return {
      getState: function () {
        return state;
      },
      isSealed: function (index) {
        return isSealed(state.players[index], state.size);
      },
      select: function (index) {
        if (state.over || state.busy) {
          return;
        }
        if (index >= 0 && index < 5) {
          current().selected = index;
        }
      },
      prepareFit: function () {
        if (state.over || state.busy) {
          return { ok: false, reason: 'closed' };
        }
        var seat = current();
        if (seat.leaks.length === 0) {
          return { ok: false, reason: 'no-leak' };
        }
        var dir = seat.leaks[0].dir;
        var best = null;
        for (var i = 0; i < seat.hand.length; i++) {
          var piece = seat.hand[i];
          var openings = cloneOpenings(piece.openings);
          for (var r = 0; r < 4; r++) {
            if (openings[dir]) {
              var extra = 0;
              for (var d = 0; d < DIRS.length; d++) {
                if (DIRS[d] !== dir && openings[DIRS[d]]) {
                  extra += 1;
                }
              }
              if (!best || extra > best.extra) {
                best = { index: i, turns: r, extra: extra };
              }
              break;
            }
            openings = rotateOpenings(openings);
          }
        }
        if (!best) {
          return { ok: false, reason: 'none' };
        }
        var chosen = seat.hand[best.index];
        for (var turn = 0; turn < best.turns; turn++) {
          chosen.openings = rotateOpenings(chosen.openings);
          chosen.rotation = (chosen.rotation + 1) % 4;
        }
        seat.selected = best.index;
        return { ok: true, reason: 'fitted', index: best.index, dir: dir };
      },
      rotate: function () {
        if (state.over || state.busy) {
          return;
        }
        var piece = current().hand[current().selected];
        piece.openings = rotateOpenings(piece.openings);
        piece.rotation = (piece.rotation + 1) % 4;
      },
      discard: function () {
        if (state.over || state.busy) {
          return { ok: false, reason: 'closed' };
        }
        var seat = current();
        seat.hand[seat.selected] = randomPiece(state.rng);
        passTurn();
        return { ok: true, reason: 'discarded' };
      },
      place: function (x, y) {
        if (state.over || state.busy) {
          return { ok: false, reason: 'closed' };
        }
        var seat = current();
        var leak = null;
        for (var i = 0; i < seat.leaks.length; i++) {
          if (seat.leaks[i].x === x && seat.leaks[i].y === y) {
            leak = seat.leaks[i];
            break;
          }
        }
        if (!leak) {
          return { ok: false, reason: 'not-leak' };
        }
        var piece = seat.hand[seat.selected];
        if (!piece.openings[leak.dir]) {
          return { ok: false, reason: 'facing' };
        }
        seat.leaks = seat.leaks.filter(function (item) {
          return item !== leak;
        });
        var pipe = {
          x: x,
          y: y,
          type: piece.type,
          rotation: piece.rotation,
          openings: cloneOpenings(piece.openings)
        };
        seat.pipes.push(pipe);
        for (var d = 0; d < DIRS.length; d++) {
          var dir = DIRS[d];
          if (!pipe.openings[dir]) {
            continue;
          }
          var nx = x + DELTA[dir][0];
          var ny = y + DELTA[dir][1];
          if (!inBounds(state.size, nx, ny)) {
            continue;
          }
          if (state.occupied[key(nx, ny)]) {
            continue;
          }
          state.occupied[key(nx, ny)] = true;
          seat.leaks.push({ x: nx, y: ny, dir: OPPOSITE[dir] });
        }
        seat.hand[seat.selected] = randomPiece(state.rng);
        passTurn();
        return { ok: true, reason: 'placed' };
      },
      openValve: function () {
        if (state.over || state.busy) {
          return { ok: false, reason: 'closed' };
        }
        var sealed = isSealed(current(), state.size);
        state.over = true;
        if (sealed) {
          state.winner = state.turn;
          state.result = 'sealed';
        } else if (state.seats === 2) {
          state.winner = 1 - state.turn;
          state.result = 'leak';
        } else {
          state.winner = null;
          state.result = 'leak';
        }
        return { ok: true, reason: state.result };
      },
      concede: function () {
        if (state.over || state.busy) {
          return { ok: false, reason: 'closed' };
        }
        state.over = true;
        state.result = 'concede';
        state.winner = state.seats === 2 ? 1 - state.turn : null;
        return { ok: true, reason: 'concede' };
      },
      scriptedPlace: function (step) {
        state.holdTurn = true;
        state.turn = 0;
        var seat = state.players[0];
        seat.selected = 0;
        seat.hand[0] = makePiece(step.type, step.rotation);
        var result = this.place(step.x, step.y);
        state.holdTurn = false;
        return result;
      }
    };
  }

  return {
    UPPER_LINE: UPPER_LINE,
    makePiece: makePiece,
    createGame: createGame
  };
});
