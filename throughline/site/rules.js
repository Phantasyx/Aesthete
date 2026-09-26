/**
 * Throughline board rules, shared by the page and the node tests.
 *
 * x is the column and y is the row. A size of 6 produces an 8 by 6 grid.
 * Each seat has a source on the left and a two-cell gauge on the right.
 * A leak records the opening the next pipe must have in order to connect
 * back into the line. A new game builds a fresh route that can be sealed.
 */
(function (root, factory) {
  var api = factory();
  if (typeof module === 'object' && module.exports) {
    module.exports = api;
  }
  root.Throughline = api;
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
   * Kept so tests can replay one fixed layout. New games build their own.
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

  var LOWER_LINE = [
    { x: 1, y: 5, type: 'straight', rotation: 0 },
    { x: 2, y: 5, type: 'straight', rotation: 0 },
    { x: 3, y: 5, type: 'straight', rotation: 0 },
    { x: 4, y: 5, type: 'straight', rotation: 0 },
    { x: 5, y: 5, type: 'elbow', rotation: 1 },
    { x: 5, y: 4, type: 'elbow', rotation: 3 },
    { x: 6, y: 4, type: 'straight', rotation: 0 }
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

  function sameOpenings(a, b) {
    return a.N === b.N && a.E === b.E && a.S === b.S && a.W === b.W;
  }

  function pieceMatching(openings) {
    var types = ['straight', 'elbow', 'tee', 'cap'];
    for (var t = 0; t < types.length; t++) {
      for (var r = 0; r < 4; r++) {
        var piece = makePiece(types[t], r);
        if (sameOpenings(piece.openings, openings)) {
          return piece;
        }
      }
    }
    return null;
  }

  function dirBetween(from, to) {
    if (to.x === from.x && to.y === from.y - 1) return 'N';
    if (to.x === from.x + 1 && to.y === from.y) return 'E';
    if (to.x === from.x && to.y === from.y + 1) return 'S';
    if (to.x === from.x - 1 && to.y === from.y) return 'W';
    return null;
  }

  function shuffle(rng, list) {
    var copy = list.slice();
    for (var i = copy.length - 1; i > 0; i--) {
      var j = Math.floor(rng() * (i + 1));
      var tmp = copy[i];
      copy[i] = copy[j];
      copy[j] = tmp;
    }
    return copy;
  }

  function inPlay(size, x, y) {
    return x >= 1 && x <= size && y >= 0 && y < size;
  }

  function findPath(rng, size, start, goal, blocked, minLen, maxLen) {
    var seen = {};
    var path = [{ x: start.x, y: start.y }];
    seen[key(start.x, start.y)] = true;

    function walk() {
      var here = path[path.length - 1];
      if (here.x === goal.x && here.y === goal.y && path.length >= minLen) {
        return true;
      }
      if (path.length >= maxLen) {
        return false;
      }
      var dirs = shuffle(rng, DIRS);
      for (var i = 0; i < dirs.length; i++) {
        var dir = dirs[i];
        var nx = here.x + DELTA[dir][0];
        var ny = here.y + DELTA[dir][1];
        if (!inPlay(size, nx, ny)) {
          continue;
        }
        if (blocked[key(nx, ny)] || seen[key(nx, ny)]) {
          continue;
        }
        if (nx === goal.x && ny === goal.y && path.length + 1 < minLen) {
          continue;
        }
        path.push({ x: nx, y: ny });
        seen[key(nx, ny)] = true;
        if (walk()) {
          return true;
        }
        path.pop();
        delete seen[key(nx, ny)];
      }
      return false;
    }

    if (!walk()) {
      return null;
    }
    return path.map(function (cell) {
      return { x: cell.x, y: cell.y };
    });
  }

  function stepsFromPath(path, source, gaugeBottom) {
    var steps = [];
    for (var i = 0; i < path.length; i++) {
      var cell = path[i];
      var openings = { N: false, E: false, S: false, W: false };
      var back = i === 0 ? dirBetween(cell, source) : dirBetween(cell, path[i - 1]);
      var forward = i === path.length - 1 ? dirBetween(cell, gaugeBottom) : dirBetween(cell, path[i + 1]);
      if (!back || !forward || back === forward) {
        return null;
      }
      openings[back] = true;
      openings[forward] = true;
      var piece = pieceMatching(openings);
      if (!piece) {
        return null;
      }
      steps.push({ x: cell.x, y: cell.y, type: piece.type, rotation: piece.rotation });
    }
    return steps;
  }

  function generateSeat(rng, size, blocked) {
    for (var attempt = 0; attempt < 80; attempt++) {
      var sy = Math.floor(rng() * size);
      var gy = 1 + Math.floor(rng() * (size - 1));
      var source = { x: 0, y: sy };
      var gaugeBottom = { x: size + 1, y: gy };
      var gaugeTop = { x: size + 1, y: gy - 1 };
      var start = { x: 1, y: sy };
      var goal = { x: size, y: gy };
      if (blocked[key(source.x, source.y)] || blocked[key(gaugeBottom.x, gaugeBottom.y)] || blocked[key(gaugeTop.x, gaugeTop.y)]) {
        continue;
      }
      if (blocked[key(start.x, start.y)] || blocked[key(goal.x, goal.y)]) {
        continue;
      }
      if (start.x === goal.x && start.y === goal.y) {
        continue;
      }
      var manhattan = Math.abs(goal.x - start.x) + Math.abs(goal.y - start.y);
      var minLen = manhattan + 1;
      var path = findPath(rng, size, start, goal, blocked, minLen, minLen + 2);
      if (!path) {
        continue;
      }
      var steps = stepsFromPath(path, source, gaugeBottom);
      if (!steps) {
        continue;
      }
      return {
        source: source,
        gaugeTop: gaugeTop,
        gaugeBottom: gaugeBottom,
        steps: steps
      };
    }
    return null;
  }

  function occupyLayout(blocked, layout) {
    blocked[key(layout.source.x, layout.source.y)] = true;
    blocked[key(layout.gaugeTop.x, layout.gaugeTop.y)] = true;
    blocked[key(layout.gaugeBottom.x, layout.gaugeBottom.y)] = true;
    for (var i = 0; i < layout.steps.length; i++) {
      blocked[key(layout.steps[i].x, layout.steps[i].y)] = true;
    }
  }

  function serpentine(x0, x1, y0, y1) {
    var path = [];
    var leftToRight = true;
    for (var y = y0; y <= y1; y++) {
      if (leftToRight) {
        for (var x = x0; x <= x1; x++) path.push({ x: x, y: y });
      } else {
        for (var x = x1; x >= x0; x--) path.push({ x: x, y: y });
      }
      leftToRight = !leftToRight;
    }
    return path;
  }

  function hamPath(rng, x0, x1, y0, y1, start, goal) {
    var total = (x1 - x0 + 1) * (y1 - y0 + 1);
    var seen = {};
    var path = [{ x: start.x, y: start.y }];
    seen[key(start.x, start.y)] = true;
    var visits = 0;

    function onward(cell) {
      var count = 0;
      for (var i = 0; i < DIRS.length; i++) {
        var nx = cell.x + DELTA[DIRS[i]][0];
        var ny = cell.y + DELTA[DIRS[i]][1];
        if (nx < x0 || nx > x1 || ny < y0 || ny > y1) continue;
        if (seen[key(nx, ny)]) continue;
        count += 1;
      }
      return count;
    }

    function options(cell) {
      var list = [];
      for (var i = 0; i < DIRS.length; i++) {
        var nx = cell.x + DELTA[DIRS[i]][0];
        var ny = cell.y + DELTA[DIRS[i]][1];
        if (nx < x0 || nx > x1 || ny < y0 || ny > y1) continue;
        if (seen[key(nx, ny)]) continue;
        list.push({ x: nx, y: ny, rank: 0 });
      }
      for (var n = 0; n < list.length; n++) {
        list[n].rank = onward(list[n]) + rng() * 0.3;
      }
      list.sort(function (a, b) { return a.rank - b.rank; });
      return list;
    }

    function walk() {
      visits += 1;
      if (visits > 8000) return false;
      if (path.length === total) {
        var last = path[path.length - 1];
        return last.x === goal.x && last.y === goal.y;
      }
      var list = options(path[path.length - 1]);
      for (var i = 0; i < list.length; i++) {
        var next = list[i];
        if (path.length + 1 < total && next.x === goal.x && next.y === goal.y) {
          continue;
        }
        path.push({ x: next.x, y: next.y });
        seen[key(next.x, next.y)] = true;
        if (walk()) return true;
        path.pop();
        delete seen[key(next.x, next.y)];
      }
      return false;
    }

    if (!walk()) return null;
    return path;
  }

  function fillSeat(index, size, rng) {
    var half = size / 2;
    var y0 = index === 0 ? 0 : half;
    var y1 = y0 + half - 1;
    var source = { x: 0, y: y0 };
    var gaugeBottom = { x: size + 1, y: y1 };
    var gaugeTop = { x: size + 1, y: y1 - 1 };
    var start = { x: 1, y: y0 };
    var goal = { x: size, y: y1 };
    var path = hamPath(rng, 1, size, y0, y1, start, goal);
    if (!path) path = serpentine(1, size, y0, y1);
    var steps = stepsFromPath(path, source, gaugeBottom);
    if (!steps) {
      path = serpentine(1, size, y0, y1);
      steps = stepsFromPath(path, source, gaugeBottom);
    }
    if (!steps) return null;
    return {
      source: source,
      gaugeTop: gaugeTop,
      gaugeBottom: gaugeBottom,
      steps: steps
    };
  }

  function randomLayouts(rng, size, seats) {
    if (size % 2 !== 0) return null;
    var layouts = [];
    for (var i = 0; i < seats; i++) {
      var layout = fillSeat(i, size, rng);
      if (!layout) return null;
      layouts.push(layout);
    }
    return layouts;
  }

  function classicLayouts(size, seats) {
    if (size !== 6) {
      return null;
    }
    var layouts = [{
      source: { x: 0, y: 0 },
      gaugeTop: { x: size + 1, y: 0 },
      gaugeBottom: { x: size + 1, y: 1 },
      steps: UPPER_LINE
    }];
    if (seats === 2) {
      layouts.push({
        source: { x: 0, y: 5 },
        gaugeTop: { x: size + 1, y: 3 },
        gaugeBottom: { x: size + 1, y: 4 },
        steps: LOWER_LINE
      });
    }
    return layouts;
  }

  function makeSeat(layout, rng) {
    var hand = shuffle(rng, layout.steps.map(function (step) {
      return makePiece(step.type, step.rotation);
    }));
    return {
      source: { x: layout.source.x, y: layout.source.y },
      gaugeTop: { x: layout.gaugeTop.x, y: layout.gaugeTop.y },
      gaugeBottom: { x: layout.gaugeBottom.x, y: layout.gaugeBottom.y },
      pipes: [],
      leaks: [{ x: layout.source.x + 1, y: layout.source.y, dir: 'W' }],
      hand: hand,
      supply: layout.steps.length,
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
    var layouts = options.layout === 'classic'
      ? classicLayouts(size, seats)
      : randomLayouts(rng, size, seats);
    if (!layouts) {
      layouts = classicLayouts(size, seats) || randomLayouts(mulberry32((seed + 1) >>> 0), size, seats);
    }
    var players = [];
    var occupied = {};
    for (var i = 0; i < seats; i++) {
      var seat = makeSeat(layouts[i], rng);
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
      solutions: layouts.map(function (layout) {
        return layout.steps.map(function (step) {
          return { x: step.x, y: step.y, type: step.type, rotation: step.rotation };
        });
      }),
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
      snapshot: function () {
        return JSON.parse(JSON.stringify({
          size: state.size,
          seats: state.seats,
          seed: state.seed,
          solutions: state.solutions,
          turn: state.turn,
          players: state.players,
          occupied: state.occupied,
          over: state.over,
          winner: state.winner,
          result: state.result
        }));
      },
      restore: function (snap) {
        var next = JSON.parse(JSON.stringify(snap));
        state.size = next.size;
        state.seats = next.seats;
        state.seed = next.seed;
        state.solutions = next.solutions;
        state.turn = next.turn;
        state.players = next.players;
        state.occupied = next.occupied;
        state.over = next.over;
        state.winner = next.winner;
        state.result = next.result;
      },
      select: function (index) {
        if (state.over || state.busy) {
          return;
        }
        var seat = current();
        if (index >= 0 && index < seat.hand.length) {
          seat.selected = index;
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
        var seat = current();
        var piece = seat.hand[seat.selected];
        if (!piece) return;
        piece.openings = rotateOpenings(piece.openings);
        piece.rotation = (piece.rotation + 1) % 4;
      },
      discard: function () {
        if (state.over || state.busy) {
          return { ok: false, reason: 'closed' };
        }
        var seat = current();
        if (!seat.hand.length) {
          return { ok: false, reason: 'empty' };
        }
        seat.hand.splice(seat.selected, 1);
        if (seat.selected >= seat.hand.length) {
          seat.selected = Math.max(0, seat.hand.length - 1);
        }
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
        if (!piece) {
          return { ok: false, reason: 'empty' };
        }
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
        seat.hand.splice(seat.selected, 1);
        if (seat.selected >= seat.hand.length) {
          seat.selected = Math.max(0, seat.hand.length - 1);
        }
        passTurn();
        return { ok: true, reason: 'placed', score: seat.pipes.length };
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
      },
      replay: function (index) {
        var steps = state.solutions[index];
        var results = [];
        state.holdTurn = true;
        state.turn = index;
        for (var n = 0; n < steps.length; n++) {
          var seat = state.players[index];
          seat.selected = 0;
          seat.hand[0] = makePiece(steps[n].type, steps[n].rotation);
          results.push(this.place(steps[n].x, steps[n].y));
        }
        state.holdTurn = false;
        return results;
      }
    };
  }

  return {
    UPPER_LINE: UPPER_LINE,
    makePiece: makePiece,
    createGame: createGame
  };
});
