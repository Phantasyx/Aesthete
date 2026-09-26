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
  var celebrated = false;
  var outcomeSounded = false;
  var boardSerial = 1;
  var audioCtx = null;

  function nextSeed() {
    boardSerial += 1;
    return (Date.now() + boardSerial * 997) >>> 0;
  }

  function unlockAudio() {
    var AudioContext = window.AudioContext || window.webkitAudioContext;
    if (!AudioContext) {
      return Promise.resolve(null);
    }
    if (!audioCtx) {
      audioCtx = new AudioContext();
    }
    var ready = audioCtx.state === 'suspended' ? audioCtx.resume() : Promise.resolve();
    return ready.then(function () {
      return audioCtx;
    }).catch(function () {
      return null;
    });
  }

  function playTone(freq, start, duration, peak) {
    var osc = audioCtx.createOscillator();
    var gain = audioCtx.createGain();
    osc.type = 'sine';
    osc.frequency.value = freq;
    gain.gain.setValueAtTime(0.0001, start);
    gain.gain.exponentialRampToValueAtTime(peak, start + 0.02);
    gain.gain.exponentialRampToValueAtTime(0.0001, start + duration);
    osc.connect(gain);
    gain.connect(audioCtx.destination);
    osc.start(start);
    osc.stop(start + duration + 0.02);
  }

  function playChime() {
    if (!audioCtx || audioCtx.state !== 'running') {
      return;
    }
    var now = audioCtx.currentTime;
    [523.25, 659.25, 783.99].forEach(function (freq, index) {
      playTone(freq, now + index * 0.09, 0.55, 0.07);
    });
  }

  function playMiss() {
    if (!audioCtx || audioCtx.state !== 'running') {
      return;
    }
    var now = audioCtx.currentTime;
    playTone(220, now, 0.22, 0.05);
    playTone(164.81, now + 0.12, 0.4, 0.05);
  }

  function clearCelebration() {
    var layer = document.getElementById('celebrate');
    var panel = document.querySelector('.panel');
    celebrated = false;
    outcomeSounded = false;
    if (layer) {
      layer.hidden = true;
      var sparks = layer.querySelectorAll('.spark');
      for (var i = 0; i < sparks.length; i++) {
        sparks[i].remove();
      }
    }
    if (panel) {
      panel.classList.remove('is-sealed');
    }
  }

  function showCelebration() {
    var layer = document.getElementById('celebrate');
    var panel = document.querySelector('.panel');
    if (!layer || celebrated) {
      return;
    }
    celebrated = true;
    layer.hidden = false;
    if (panel) {
      panel.classList.add('is-sealed');
    }
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!reduced) {
      for (var i = 0; i < 16; i++) {
        var spark = document.createElement('span');
        spark.className = 'spark';
        spark.style.left = (8 + Math.random() * 84) + '%';
        spark.style.bottom = (6 + Math.random() * 28) + '%';
        spark.style.animationDelay = (Math.random() * 0.2) + 's';
        spark.style.background = i % 2 === 0 ? '#e6c27a' : '#e4b08a';
        layer.appendChild(spark);
      }
    }
    outcomeSounded = true;
    playChime();
  }

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
      return 'This piece has no open side.';
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
    var state = game.getState();
    var leak = currentSeat(state).leaks[0];
    var side = DIR_NAME[leak.dir];
    if (state.seats === 2) {
      return 'Two people share this board. The upper line moves first, and the open end needs a piece that faces ' + side + '. Pass the device when the turn changes. Nothing is sent to a server.';
    }
    return 'The open end beside the source needs a piece that opens ' + side + '. Turn a piece until it faces that way, then click the gold mark.';
  }

  function messageFor(reason, state) {
    if (reason === 'facing') {
      return 'That piece does not open toward the gold mark. Rotate it, or choose a different piece.';
    }
    if (reason === 'not-leak') {
      return 'You can only place a piece on the gold mark, on the line that is moving.';
    }
    if (reason === 'other-leak') {
      return 'That opening belongs to the other line. On this turn you can only extend the line that is moving.';
    }
    if (reason === 'placed') {
      if (state.seats === 2) {
        return seatName(state.turn) + ' moves next. Place a piece only on the gold marks for that line.';
      }
      var leaks = state.players[0].leaks.length;
      if (leaks === 0) {
        return 'Nothing is left open on the board. If the gauge is connected, open the valve. If a pipe runs off the edge, the line is not finished.';
      }
      return 'The open end moved. ' + leaks + (leaks === 1 ? ' gold mark shows' : ' gold marks show') + ' where you can play next.';
    }
    if (reason === 'discarded') {
      if (state.seats === 2) {
        return 'That piece is gone, and a new one took its place. ' + seatName(state.turn) + ' moves next.';
      }
      return 'That piece is gone, and a new one took its place. The line itself did not change.';
    }
    if (reason === 'fitted') {
      return 'This piece now opens toward the gold mark. Click the marked cell to place it.';
    }
    if (reason === 'none') {
      return 'None of these pieces can meet that opening. Discard one and you will draw another.';
    }
    if (reason === 'sealed') {
      if (state.seats === 2) {
        return seatName(state.winner) + ' finished the line. Every opening meets another opening, and the gauge is connected.';
      }
      return 'The line is sealed. Every opening meets another opening, and the gauge is connected.';
    }
    if (reason === 'leak') {
      if (state.seats === 2) {
        return seatName(state.winner) + ' wins. The valve was opened before the line was sealed.';
      }
      return 'The valve opened while the line was still unfinished. Any bare opening, including one that leaves the board, means the line is lost.';
    }
    if (reason === 'concede') {
      if (state.seats === 2) {
        return seatName(state.winner) + ' wins. The other person gave up the line.';
      }
      return 'You gave up this line. Start a new board when you want another route.';
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
      ? (state.result === 'sealed' ? 'Line sealed' : 'Line still open')
      : seatName(state.turn) + ' to move';
    boardEl.dataset.seed = String(state.seed);
    boardEl.dataset.route = (state.solutions[0] || []).map(function (step) {
      return step.x + ',' + step.y;
    }).join(' ');
    if (state.result === 'sealed') {
      showCelebration();
    } else if (state.result === 'leak' && !outcomeSounded) {
      outcomeSounded = true;
      playMiss();
    }

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
    compassEl.textContent = state.over
      ? 'The hand is closed for this board.'
      : describeOpenings(selected.openings);

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

  function boot() {
    generation += 1;
    locked = false;
    lastPlaced = null;
    clearCelebration();
    game = Throughline.createGame({ seats: seats, seed: nextSeed() });
    render(startMessage());
    document.getElementById('mode-solo').setAttribute('aria-pressed', seats === 1 ? 'true' : 'false');
    document.getElementById('mode-hotseat').setAttribute('aria-pressed', seats === 2 ? 'true' : 'false');
  }

  document.getElementById('mode-solo').addEventListener('click', function () {
    seats = 1;
    boot();
  });
  document.getElementById('mode-hotseat').addEventListener('click', function () {
    seats = 2;
    boot();
  });
  document.getElementById('reset').addEventListener('click', function () {
    unlockAudio();
    boot();
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
    render(describeOpenings(piece.openings) + ' Place it on the gold mark, or rotate it again.');
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
    unlockAudio().then(function () {
      var result = game.openValve();
      render(messageFor(result.reason, game.getState()));
    });
  });
  concedeButton.addEventListener('click', function () {
    if (locked) {
      return;
    }
    var result = game.concede();
    render(messageFor(result.reason, game.getState()));
  });
  document.getElementById('watch').addEventListener('click', function () {
    unlockAudio().then(function () {
      playSealedLine();
    });
  });
  document.getElementById('sound').addEventListener('click', function () {
    var button = document.getElementById('sound');
    var bed = document.getElementById('bed');
    unlockAudio();
    if (!bed.paused) {
      bed.pause();
      button.setAttribute('aria-pressed', 'false');
      button.textContent = 'Sound';
      return;
    }
    var started = bed.play();
    var markOn = function () {
      button.setAttribute('aria-pressed', 'true');
      button.textContent = 'Sound on';
    };
    if (started && typeof started.then === 'function') {
      started.then(markOn).catch(function () {
        button.setAttribute('aria-pressed', 'false');
        button.textContent = 'Sound unavailable';
      });
      return;
    }
    markOn();
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
    clearCelebration();
    game = Throughline.createGame({ seats: 1, seed: nextSeed() });
    var local = game;
    document.getElementById('mode-solo').setAttribute('aria-pressed', 'true');
    document.getElementById('mode-hotseat').setAttribute('aria-pressed', 'false');
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var steps = local.getState().solutions[0];

    if (reduced) {
      steps.forEach(function (step) {
        local.scriptedPlace(step);
      });
      local.openValve();
      locked = false;
      render(messageFor('sealed', local.getState()));
      return;
    }

    render('Here is one way to finish this board, from the source to the gauge.');
    (async function () {
      for (var i = 0; i < steps.length; i++) {
        if (token !== generation) {
          return;
        }
        var step = steps[i];
        var preview = local.getState();
        preview.players[0].selected = 0;
        preview.players[0].hand[0] = Throughline.makePiece(step.type, step.rotation);
        render('Piece ' + (i + 1) + ' of ' + steps.length + '. ' + describeOpenings(preview.players[0].hand[0].openings));
        await delay(700);
        if (token !== generation) {
          return;
        }
        local.scriptedPlace(step);
        lastPlaced = { x: step.x, y: step.y };
        render('The line follows the open end.');
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

  var bed = document.getElementById('bed');
  if (bed) {
    bed.volume = 0.35;
  }
  boot();
})();
