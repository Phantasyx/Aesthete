(function () {
  if (typeof document === 'undefined') {
    return;
  }

  var DIRS = ['N', 'E', 'S', 'W'];
  var DIR_NAME = { N: 'north', E: 'east', S: 'south', W: 'west' };
  var TYPE_NAME = { cap: 'cap', elbow: 'elbow', straight: 'straight', tee: 'tee' };
  var PALETTE = ['#e4b08a', '#8fd0cb'];
  var NAMES = ['Upper line', 'Lower line'];

  var boardEl = document.getElementById('board');
  var handEl = document.getElementById('hand');
  var statusEl = document.getElementById('status');
  var compassEl = document.getElementById('compass');
  var turnEl = document.getElementById('turn-label');
  var fitButton = document.getElementById('fit');
  var rotateButton = document.getElementById('rotate');
  var discardButton = document.getElementById('discard');
  var valveButton = document.getElementById('valve');
  var concedeButton = document.getElementById('concede');

  var game = null;
  var seats = 1;
  var locked = false;
  var generation = 0;
  var lastPlaced = null;

  function currentSeat(state) {
    return state.players[state.turn];
  }

  function seatName(index) {
    return seats === 1 ? 'Your line' : NAMES[index];
  }

  function describeOpenings(openings) {
    var open = DIRS.filter(function (dir) { return openings[dir]; })
      .map(function (dir) { return DIR_NAME[dir]; });
    if (open.length === 0) {
      return 'No open side.';
    }
    if (open.length === 1) {
      return 'Opens ' + open[0] + '.';
    }
    if (open.length === 2) {
      return 'Opens ' + open[0] + ' and ' + open[1] + '.';
    }
    return 'Opens ' + open.slice(0, -1).join(', ') + ', and ' + open[open.length - 1] + '.';
  }

  function startMessage() {
    return 'The leak beside the source faces west. Fit a piece so it opens west, then click the marked cell.';
  }

  function messageFor(reason, state) {
    if (reason === 'facing') {
      return 'That piece does not open toward the gold mark. Rotate it, or fit a different piece.';
    }
    if (reason === 'not-leak') {
      return 'Pipes land on a leak in the line that is moving. The gold mark is that open end.';
    }
    if (reason === 'other-leak') {
      return 'That leak belongs to the other line. This turn can extend only the line that is moving.';
    }
    if (reason === 'placed') {
      if (state.seats === 2) {
        return seatName(state.turn) + ' to move. Click only the leaks on that line.';
      }
      var leaks = state.players[0].leaks.length;
      if (leaks === 0) {
        return 'No leak is left on the board. Open the valve if the gauge is connected. An opening off the edge still loses.';
      }
      return 'The leak moved to the new open end. ' + leaks + (leaks === 1 ? ' cell is' : ' cells are') + ' marked.';
    }
    if (reason === 'discarded') {
      if (state.seats === 2) {
        return 'Piece discarded. ' + seatName(state.turn) + ' to move.';
      }
      return 'Piece discarded and replaced. The line did not change.';
    }
    if (reason === 'fitted') {
      return 'This piece now opens toward the gold mark. Click the marked cell to lay it.';
    }
    if (reason === 'none') {
      return 'None of these pieces can meet that leak. Discard one to draw another.';
    }
    if (reason === 'sealed') {
      var who = state.seats === 2 ? seatName(state.winner) + ' is sealed. ' : 'Sealed. ';
      return who + 'Every opening meets another opening, and the gauge is in the line.';
    }
    if (reason === 'leak') {
      if (state.seats === 2) {
        return seatName(state.winner) + ' takes the match. The valve was opened before the line was sealed.';
      }
      return 'The valve opened onto an unfinished line. A bare opening, including one that leaves the board, loses the seat.';
    }
    if (reason === 'concede') {
      if (state.seats === 2) {
        return seatName(state.winner) + ' takes the match. The other seat conceded.';
      }
      return 'Line conceded. Reset to try another route.';
    }
    return startMessage();
  }

  function svgPipe(openings, color, flowing) {
    var ends = { N: [50, 8], E: [92, 50], S: [50, 92], W: [8, 50] };
    var paths = '';
    var flowClass = flowing ? ' class="flow"' : '';
    DIRS.forEach(function (dir) {
      if (!openings[dir]) {
        return;
      }
      paths += '<path' + flowClass + ' d="M50 50 L' + ends[dir][0] + ' ' + ends[dir][1] + '"/>';
    });
    return '<svg viewBox="0 0 100 100" class="glyph" aria-hidden="true">' +
      '<g fill="none" stroke="' + color + '" stroke-width="16" stroke-linecap="round">' + paths + '</g>' +
      '<circle cx="50" cy="50" r="6" fill="' + color + '"/></svg>';
  }

  function svgSource(open, color) {
    var handle = open
      ? '<path d="M34 36 V16" stroke="' + color + '" stroke-width="5" stroke-linecap="round"/>'
      : '<path d="M22 30 H46" stroke="' + color + '" stroke-width="5" stroke-linecap="round"/>';
    return '<svg viewBox="0 0 100 100" class="glyph" aria-hidden="true">' +
      '<path d="M40 50 H94" fill="none" stroke="' + color + '" stroke-width="16" stroke-linecap="round"/>' +
      '<circle cx="34" cy="50" r="18" fill="#2c261f" stroke="' + color + '" stroke-width="4"/>' +
      handle + '</svg>';
  }

  function svgGauge(charged, color) {
    var needle = charged ? 'M50 58 L70 34' : 'M50 58 L32 40';
    return '<svg viewBox="0 0 100 100" class="glyph" aria-hidden="true">' +
      '<path d="M18 64 A32 32 0 0 1 82 64" fill="none" stroke="' + color + '" stroke-width="4"/>' +
      '<path d="' + needle + '" stroke="' + color + '" stroke-width="4" stroke-linecap="round"/>' +
      '<circle cx="50" cy="58" r="4" fill="' + color + '"/></svg>';
  }

  function svgInlet(color) {
    return '<svg viewBox="0 0 100 100" class="glyph" aria-hidden="true">' +
      '<path d="M6 50 H64" fill="none" stroke="' + color + '" stroke-width="16" stroke-linecap="round"/>' +
      '<rect x="58" y="30" width="24" height="40" rx="4" fill="none" stroke="' + color + '" stroke-width="4"/></svg>';
  }

  function otherLeak(state, x, y) {
    for (var i = 0; i < state.players.length; i++) {
      if (i === state.turn) {
        continue;
      }
      var leaks = state.players[i].leaks;
      for (var n = 0; n < leaks.length; n++) {
        if (leaks[n].x === x && leaks[n].y === y) {
          return true;
        }
      }
    }
    return false;
  }

  function render(text) {
    var state = game.getState();
    var seat = currentSeat(state);
    var winnerColor = state.winner == null ? null : PALETTE[state.winner];
    statusEl.textContent = text;
    turnEl.textContent = state.over
      ? (state.result === 'sealed' ? 'Line sealed' : 'Line open')
      : seatName(state.turn) + ' to move';

    boardEl.style.setProperty('--cols', String(state.size + 2));
    boardEl.style.setProperty('--rows', String(state.size));
    boardEl.style.aspectRatio = (state.size + 2) + ' / ' + state.size;
    boardEl.innerHTML = '';

    for (var y = 0; y < state.size; y++) {
      for (var x = 0; x < state.size + 2; x++) {
        boardEl.appendChild(renderCell(state, x, y, winnerColor));
      }
    }

    handEl.innerHTML = '';
    seat.hand.forEach(function (piece, index) {
      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'piece' + (index === seat.selected ? ' is-selected' : '');
      button.setAttribute('aria-pressed', index === seat.selected ? 'true' : 'false');
      button.setAttribute('aria-label', TYPE_NAME[piece.type] + ', ' + describeOpenings(piece.openings));
      button.innerHTML = svgPipe(piece.openings, PALETTE[state.turn], false);
      button.addEventListener('click', function () {
        if (locked || state.over) {
          return;
        }
        game.select(index);
        render(describeOpenings(currentSeat(game.getState()).hand[index].openings) + ' Click a marked cell to place it, or rotate it first.');
      });
      handEl.appendChild(button);
    });

    var selected = seat.hand[seat.selected];
    compassEl.textContent = state.over ? 'The hand is closed.' : describeOpenings(selected.openings);

    var frozen = locked || state.over;
    fitButton.disabled = frozen;
    rotateButton.disabled = frozen;
    discardButton.disabled = frozen;
    valveButton.disabled = frozen;
    concedeButton.disabled = frozen;
  }

  function renderCell(state, x, y, winnerColor) {
    var occupied = null;
    var leak = null;
    var owner = null;
    for (var i = 0; i < state.players.length; i++) {
      var seat = state.players[i];
      if (seat.source.x === x && seat.source.y === y) {
        occupied = { kind: 'source', owner: i, open: state.over && state.winner === i };
      }
      if (seat.gaugeTop.x === x && seat.gaugeTop.y === y) {
        occupied = { kind: 'gauge', owner: i, charged: state.over && state.winner === i };
      }
      if (seat.gaugeBottom.x === x && seat.gaugeBottom.y === y) {
        occupied = { kind: 'inlet', owner: i };
      }
      for (var p = 0; p < seat.pipes.length; p++) {
        if (seat.pipes[p].x === x && seat.pipes[p].y === y) {
          occupied = { kind: 'pipe', owner: i, pipe: seat.pipes[p] };
        }
      }
      for (var n = 0; n < seat.leaks.length; n++) {
        if (seat.leaks[n].x === x && seat.leaks[n].y === y) {
          leak = seat.leaks[n];
          owner = i;
        }
      }
    }

    var cell = document.createElement(occupied && occupied.kind !== 'pipe' ? 'div' : 'button');
    if (cell.tagName === 'BUTTON') {
      cell.type = 'button';
    }
    cell.className = 'cell';
    if (leak && owner === state.turn && !state.over) {
      cell.classList.add('is-live');
      cell.dataset.dir = leak.dir;
    } else if (leak) {
      cell.classList.add('is-quiet');
      cell.dataset.dir = leak.dir;
    }
    if (lastPlaced && lastPlaced.x === x && lastPlaced.y === y) {
      cell.classList.add('is-fresh');
    }

    var color = PALETTE[occupied ? occupied.owner : (owner == null ? 0 : owner)];
    if (occupied && occupied.kind === 'source') {
      cell.innerHTML = svgSource(occupied.open, color);
      cell.setAttribute('aria-label', 'Source, opens east');
    } else if (occupied && occupied.kind === 'gauge') {
      cell.innerHTML = svgGauge(occupied.charged, winnerColor && occupied.charged ? winnerColor : color);
      cell.setAttribute('aria-label', occupied.charged ? 'Gauge, holding pressure' : 'Gauge');
    } else if (occupied && occupied.kind === 'inlet') {
      cell.innerHTML = svgInlet(color);
      cell.setAttribute('aria-label', 'Gauge inlet, opens west');
    } else if (occupied && occupied.kind === 'pipe') {
      var flowing = state.result === 'sealed' && state.winner === occupied.owner;
      cell.innerHTML = svgPipe(occupied.pipe.openings, color, flowing);
      cell.setAttribute('aria-label', TYPE_NAME[occupied.pipe.type] + ' pipe, ' + describeOpenings(occupied.pipe.openings));
    } else if (leak) {
      var side = DIR_NAME[leak.dir];
      cell.setAttribute('aria-label', 'Leak, piece must open ' + side);
    } else {
      cell.setAttribute('aria-label', 'Empty cell');
    }

    if (cell.tagName === 'BUTTON') {
      cell.addEventListener('click', function () {
        onCell(x, y);
      });
    }
    return cell;
  }

  function onCell(x, y) {
    if (locked) {
      return;
    }
    var state = game.getState();
    if (state.over) {
      return;
    }
    if (otherLeak(state, x, y)) {
      render(messageFor('other-leak', state));
      return;
    }
    var result = game.place(x, y);
    if (result.reason === 'placed') {
      lastPlaced = { x: x, y: y };
    }
    render(messageFor(result.reason, game.getState()));
  }

  function boot(text) {
    generation += 1;
    locked = false;
    lastPlaced = null;
    game = Conduit.createGame({ seats: seats, seed: (Date.now() ^ (seats * 97)) >>> 0 });
    render(text || startMessage());
    document.getElementById('mode-solo').setAttribute('aria-pressed', seats === 1 ? 'true' : 'false');
    document.getElementById('mode-hotseat').setAttribute('aria-pressed', seats === 2 ? 'true' : 'false');
  }

  document.getElementById('mode-solo').addEventListener('click', function () {
    seats = 1;
    boot(startMessage());
  });
  document.getElementById('mode-hotseat').addEventListener('click', function () {
    seats = 2;
    boot('Two seats, one board. The upper line moves first. Pass the device when the turn changes. Nothing is sent to a server.');
  });
  document.getElementById('reset').addEventListener('click', function () {
    boot(startMessage());
  });
  fitButton.addEventListener('click', function () {
    if (locked) {
      return;
    }
    var result = game.prepareFit();
    render(messageFor(result.reason, game.getState()));
  });
  rotateButton.addEventListener('click', function () {
    if (locked || game.getState().over) {
      return;
    }
    game.rotate();
    var piece = currentSeat(game.getState()).hand[currentSeat(game.getState()).selected];
    render(describeOpenings(piece.openings) + ' Place it on the gold mark, or rotate again.');
  });
  discardButton.addEventListener('click', function () {
    if (locked) {
      return;
    }
    var result = game.discard();
    lastPlaced = null;
    render(messageFor(result.reason, game.getState()));
  });
  valveButton.addEventListener('click', function () {
    if (locked) {
      return;
    }
    var result = game.openValve();
    render(messageFor(result.reason, game.getState()));
  });
  concedeButton.addEventListener('click', function () {
    if (locked) {
      return;
    }
    var result = game.concede();
    render(messageFor(result.reason, game.getState()));
  });
  document.getElementById('watch').addEventListener('click', function () {
    playSealedLine();
  });

  function delay(ms) {
    return new Promise(function (resolve) {
      window.setTimeout(resolve, ms);
    });
  }

  function playSealedLine() {
    generation += 1;
    var token = generation;
    seats = 1;
    locked = true;
    lastPlaced = null;
    game = Conduit.createGame({ seats: 1, seed: 11 });
    var local = game;
    document.getElementById('mode-solo').setAttribute('aria-pressed', 'true');
    document.getElementById('mode-hotseat').setAttribute('aria-pressed', 'false');
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var steps = Conduit.UPPER_LINE;

    if (reduced) {
      steps.forEach(function (step) {
        local.scriptedPlace(step);
      });
      local.openValve();
      locked = false;
      render(messageFor('sealed', local.getState()));
      return;
    }

    render('Building a sealed line from the source to the gauge.');
    (async function () {
      for (var i = 0; i < steps.length; i++) {
        if (token !== generation) {
          return;
        }
        var step = steps[i];
        var preview = local.getState();
        preview.players[0].selected = 0;
        preview.players[0].hand[0] = Conduit.makePiece(step.type, step.rotation);
        render('Piece ' + (i + 1) + ' of ' + steps.length + '. ' + describeOpenings(preview.players[0].hand[0].openings));
        await delay(700);
        if (token !== generation) {
          return;
        }
        local.scriptedPlace(step);
        lastPlaced = { x: step.x, y: step.y };
        render('The line grows along the open end.');
        await delay(420);
      }
      if (token !== generation) {
        return;
      }
      local.openValve();
      locked = false;
      render(messageFor('sealed', local.getState()));
    })();
  }

  boot(startMessage());
})();
