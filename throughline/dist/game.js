(function () {
  if (typeof document === 'undefined') {
    return;
  }

  var DIRS = ['N', 'E', 'S', 'W'];
  var DIR_NAME = { N: 'north', E: 'east', S: 'south', W: 'west' };
  var TYPE_NAME = { cap: 'cap', elbow: 'elbow', straight: 'straight', tee: 'tee' };
  var PALETTE = ['#e4b08a', '#8fd0cb'];
  var NAMES = ['Copper', 'Teal'];

  var boardEl = document.getElementById('board');
  var handEl = document.getElementById('hand');
  var statusEl = document.getElementById('status');
  var compassEl = document.getElementById('compass');
  var turnEl = document.getElementById('turn-label');
  var scoreEl = document.getElementById('score');
  var scoreNoteEl = document.getElementById('score-note');
  var scoreKickerEl = document.getElementById('score-kicker');
  var roomEl = document.getElementById('room');
  var roomStatusEl = document.getElementById('room-status');
  var roomCodeEl = document.getElementById('room-code');
  var rotateButton = document.getElementById('rotate');
  var discardButton = document.getElementById('discard');
  var valveButton = document.getElementById('valve');
  var concedeButton = document.getElementById('concede');
  var watchButton = document.getElementById('watch');

  var game = null;
  var net = null;
  var pollTimer = null;
  var sending = false;
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

  function mySeatIndex(state) {
    return net ? net.seat : 0;
  }

  function myTurn(state) {
    if (state.over) {
      return false;
    }
    if (!net) {
      return true;
    }
    return state.turn === net.seat;
  }

  function seatName(state, index) {
    return state.seats === 1 ? 'You' : NAMES[index];
  }

  function shownScore(state, index) {
    var seat = state.players[index];
    if (state.result === 'leak' || state.result === 'concede') {
      if (state.seats === 1 || state.winner !== index) {
        return 0;
      }
    }
    return seat.pipes.length;
  }

  function bestReachable(seat) {
    return seat.pipes.length + seat.hand.length;
  }

  function actorIndex(state, reason) {
    if (state.seats === 2 && (reason === 'placed' || reason === 'discarded')) {
      return 1 - state.turn;
    }
    return mySeatIndex(state);
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
    var seat = state.players[mySeatIndex(state)];
    var leak = seat.leaks[0];
    var side = leak ? DIR_NAME[leak.dir] : 'the line';
    if (net && net.seatsFilled < 2) {
      return 'The table is open. Send the code. Copper moves first, and the board matches when the other browser sits down.';
    }
    if (net && !myTurn(state)) {
      return 'The other browser has the turn. Your pipes stay in the tray until it comes back.';
    }
    if (state.seats === 2) {
      return seatName(state, state.turn) + ' moves. The gold mark needs a pipe that opens ' + side + '. Every pipe you lay raises the score.';
    }
    return 'The tray holds every pipe for a perfect fill. The gold mark needs a pipe that opens ' + side + '. Lay more pipes to raise the score, or scrap one and take a shorter run.';
  }

  function messageFor(reason, state) {
    var actor = state.players[actorIndex(state, reason)] || state.players[0];
    if (reason === 'facing') {
      return 'That pipe does not open toward the gold mark. Rotate it, or choose another pipe.';
    }
    if (reason === 'not-leak') {
      return 'You can only lay a pipe on the gold mark.';
    }
    if (reason === 'other-leak') {
      return 'That opening belongs to the other line. On this turn you can only extend the line that is moving.';
    }
    if (reason === 'empty') {
      return 'The tray is empty. Seal the line with what you have laid, or forfeit.';
    }
    if (reason === 'placed') {
      var placedScore = actor.pipes.length;
      var placedBest = bestReachable(actor);
      var placed = 'Score is ' + placedScore + '. The best you can still reach is ' + placedBest + '.';
      if (state.seats === 2) {
        return seatName(state, state.turn) + ' has the turn. ' + placed;
      }
      var leaks = state.players[0].leaks.length;
      if (leaks === 0) {
        return 'Nothing is left open on the board. ' + placed + ' If the gauge is connected, seal the line.';
      }
      return 'The open end moved. ' + placed;
    }
    if (reason === 'discarded') {
      var left = actor.hand.length;
      var best = bestReachable(actor);
      var scrap = 'That pipe is scrapped. You have ' + left + ' left, so the best score you can still reach is ' + best + '.';
      if (state.seats === 2) {
        return scrap + ' ' + seatName(state, state.turn) + ' has the turn.';
      }
      return scrap;
    }
    if (reason === 'rotated' || reason === 'selected') {
      var piece = actor.hand[actor.selected];
      if (!piece) {
        return 'The tray is empty.';
      }
      return describeOpenings(piece.openings) + ' Lay it on the gold mark, or rotate it again.';
    }
    if (reason === 'sealed') {
      var winnerIndex = state.winner == null ? 0 : state.winner;
      var winner = state.players[winnerIndex];
      var sealed;
      if (winner.pipes.length === winner.supply) {
        sealed = 'Perfect fill. You used every pipe, and the gauge is connected.';
      } else {
        sealed = 'You sealed the line with ' + winner.pipes.length + ' pipes. A perfect fill on this board is ' + winner.supply + '.';
      }
      if (state.seats === 2) {
        return seatName(state, winnerIndex) + ' banks ' + winner.pipes.length + '. ' + sealed;
      }
      return sealed;
    }
    if (reason === 'leak') {
      if (state.seats === 2) {
        return seatName(state, state.winner) + ' takes the round. The valve opened before the line was sealed, so that score does not bank.';
      }
      return 'The valve opened while the line was still unfinished. The score does not bank.';
    }
    if (reason === 'concede') {
      if (state.seats === 2) {
        return seatName(state, state.winner) + ' takes the round. The other player forfeited.';
      }
      return 'You forfeited this puzzle. The score does not bank. Deal a new puzzle when you want another route.';
    }
    if (reason === 'wait') {
      return 'The other browser has the turn.';
    }
    if (reason === 'host') {
      return 'Only the host can deal the next puzzle.';
    }
    if (reason === 'waiting') {
      return startMessage();
    }
    if (reason === 'sync') {
      return startMessage();
    }
    if (reason === 'new') {
      return startMessage();
    }
    return startMessage();
  }

  function paintScore(state) {
    var mine = state.players[mySeatIndex(state)];
    if (state.seats === 1) {
      scoreKickerEl.textContent = state.result === 'leak' || state.result === 'concede' ? 'Not banked' : 'Score';
      scoreEl.textContent = String(shownScore(state, 0));
      if (state.result === 'sealed' && mine.pipes.length === mine.supply) {
        scoreNoteEl.textContent = 'Perfect fill. You used every pipe, and the gauge is connected.';
      } else if (state.result === 'sealed') {
        scoreNoteEl.textContent = 'You sealed the line with ' + mine.pipes.length + ' pipes. A perfect fill on this board is ' + mine.supply + '.';
      } else if (state.result === 'leak' || state.result === 'concede') {
        scoreNoteEl.textContent = 'You laid ' + mine.pipes.length + ' pipes. The score does not bank.';
      } else if (bestReachable(mine) < mine.supply) {
        scoreNoteEl.textContent = 'Perfect fill is ' + mine.supply + ' pipes. ' + mine.hand.length + ' still in the tray. The best you can still reach is ' + bestReachable(mine) + '.';
      } else {
        scoreNoteEl.textContent = 'Perfect fill is ' + mine.supply + ' pipes. ' + mine.hand.length + ' still in the tray.';
      }
      return;
    }
    scoreKickerEl.textContent = 'Copper · Teal';
    scoreEl.textContent = shownScore(state, 0) + ' · ' + shownScore(state, 1);
    if (state.result === 'sealed') {
      var winnerIndex = state.winner == null ? 0 : state.winner;
      var winner = state.players[winnerIndex];
      scoreNoteEl.textContent = seatName(state, winnerIndex) + ' sealed with ' + winner.pipes.length + ' pipes. A perfect fill is ' + winner.supply + '.';
    } else if (state.result === 'leak' || state.result === 'concede') {
      scoreNoteEl.textContent = 'The round is over. A score banks only when that line is sealed.';
    } else {
      scoreNoteEl.textContent = 'Copper has ' + state.players[0].pipes.length + ' laid and ' + state.players[0].hand.length + ' left. Teal has ' + state.players[1].pipes.length + ' laid and ' + state.players[1].hand.length + ' left. A perfect fill is ' + state.players[0].supply + ' pipes on each side.';
    }
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
    var tray = state.players[mySeatIndex(state)];
    var winnerColor = state.winner == null ? null : PALETTE[state.winner];
    statusEl.textContent = text;
    if (state.over) {
      turnEl.textContent = state.result === 'sealed' ? 'Line sealed' : 'Round over';
    } else if (net && !myTurn(state)) {
      turnEl.textContent = 'Waiting on ' + seatName(state, state.turn);
    } else {
      turnEl.textContent = seatName(state, state.turn) + ' to move';
    }
    paintScore(state);
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
    tray.hand.forEach(function (piece, index) {
      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'piece' + (index === tray.selected ? ' is-selected' : '');
      button.setAttribute('aria-pressed', index === tray.selected ? 'true' : 'false');
      button.setAttribute('aria-label', TYPE_NAME[piece.type] + ', ' + describeOpenings(piece.openings));
      button.innerHTML = svgPipe(piece.openings, PALETTE[mySeatIndex(state)], false);
      button.addEventListener('click', function () {
        if (!myTurn(game.getState()) || locked || sending) {
          return;
        }
        game.select(index);
        render(messageFor('selected', game.getState()));
      });
      handEl.appendChild(button);
    });

    var selected = tray.hand[tray.selected];
    compassEl.textContent = state.over
      ? 'The tray is closed for this puzzle.'
      : (selected ? describeOpenings(selected.openings) : 'The tray is empty.');

    var frozen = locked || sending || state.over || !myTurn(state);
    rotateButton.disabled = frozen || tray.hand.length === 0;
    discardButton.disabled = frozen || tray.hand.length === 0;
    valveButton.disabled = frozen;
    concedeButton.disabled = frozen;
    if (watchButton) {
      watchButton.disabled = !!net || locked;
    }
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
      cell.setAttribute('aria-label', 'Open end, pipe must open ' + side);
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

  function runLocal(body) {
    if (body.type === 'select') {
      game.select(body.index);
      return { ok: true, reason: 'selected' };
    }
    if (body.type === 'rotate') {
      game.rotate();
      return { ok: true, reason: 'rotated' };
    }
    if (body.type === 'discard') return game.discard();
    if (body.type === 'place') return game.place(body.x, body.y);
    if (body.type === 'valve') return game.openValve();
    if (body.type === 'concede') return game.concede();
    return { ok: false, reason: 'unknown' };
  }

  function applyRemote(payload) {
    if (!payload || !payload.state || !net) {
      return payload || { ok: false, reason: 'missing' };
    }
    if (payload.version != null && payload.version < net.version) {
      return payload;
    }
    var wasOver = game.getState().over;
    if (payload.version != null) net.version = payload.version;
    if (payload.seatsFilled != null) net.seatsFilled = payload.seatsFilled;
    game.restore(payload.state);
    if (!payload.state.over && wasOver) {
      lastPlaced = null;
      clearCelebration();
    }
    return payload;
  }

  function act(body) {
    var state = game.getState();
    if (locked || sending) {
      return;
    }
    if (body.type !== 'new' && (state.over || !myTurn(state))) {
      render(messageFor(state.over ? state.result : 'wait', state));
      return;
    }
    if (body.type === 'rotate' || body.type === 'discard' || body.type === 'place') {
      body.index = state.players[state.turn].selected;
    }
    if (!net) {
      var local = runLocal(body);
      if (body.type === 'place' && local.reason === 'placed') {
        lastPlaced = { x: body.x, y: body.y };
      }
      if (body.type === 'discard') {
        lastPlaced = null;
      }
      render(messageFor(local.reason, game.getState()));
      return;
    }
    sending = true;
    render(statusEl.textContent);
    fetch('/api/room/' + net.code + '/act', {
      method: 'POST',
      headers: { 'content-type': 'application/json' },
      body: JSON.stringify(Object.assign({ token: net.token }, body))
    }).then(function (res) {
      return res.json();
    }).then(function (payload) {
      sending = false;
      if (body.type === 'place' && payload.reason === 'placed') {
        lastPlaced = { x: body.x, y: body.y };
      }
      if (body.type === 'discard' || body.type === 'new') {
        lastPlaced = null;
      }
      if (body.type === 'new') {
        clearCelebration();
      }
      applyRemote(payload);
      render(messageFor(payload.reason, game.getState()));
    }).catch(function () {
      sending = false;
      render('The session service is not answering. Solo play still works on this page.');
    });
  }

  function onCell(x, y) {
    var state = game.getState();
    if (locked || sending || state.over || !myTurn(state)) {
      return;
    }
    if (otherLeak(state, x, y)) {
      render(messageFor('other-leak', state));
      return;
    }
    act({ type: 'place', x: x, y: y });
  }

  function stopPoll() {
    if (pollTimer) {
      window.clearInterval(pollTimer);
      pollTimer = null;
    }
  }

  function pollOnce() {
    if (!net || sending) {
      return;
    }
    fetch('/api/room/' + net.code, { cache: 'no-store' }).then(function (res) {
      return res.json();
    }).then(function (payload) {
      if (!net || sending) {
        return;
      }
      if (!payload.ok) {
        render('That table is gone. Solo play still works on this page.');
        return;
      }
      var version = payload.version;
      var filled = payload.seatsFilled;
      if (version === net.version && filled === net.seatsFilled) {
        return;
      }
      var arrived = filled > net.seatsFilled;
      applyRemote(payload);
      render(messageFor(arrived ? 'waiting' : 'sync', game.getState()));
    }).catch(function () {
      if (!net) {
        return;
      }
      roomStatusEl.textContent = 'The session service is not answering. Solo play still works on this page.';
    });
  }

  function startPoll() {
    stopPoll();
    pollTimer = window.setInterval(pollOnce, 800);
  }

  function showRoom(code, status) {
    roomEl.hidden = false;
    roomStatusEl.textContent = status;
    if (code) {
      roomCodeEl.hidden = false;
      roomCodeEl.textContent = code;
    }
    document.getElementById('mode-solo').setAttribute('aria-pressed', 'false');
    document.getElementById('mode-live').setAttribute('aria-pressed', 'true');
  }

  function adopt(payload, status) {
    net = {
      code: payload.code,
      token: payload.token,
      seat: payload.seat,
      version: payload.version,
      seatsFilled: payload.seat === 1 ? 2 : 1
    };
    generation += 1;
    locked = false;
    lastPlaced = null;
    clearCelebration();
    game = Throughline.createGame({ seats: 2, seed: payload.state.seed });
    game.restore(payload.state);
    showRoom(payload.code, status);
    render(startMessage());
    startPoll();
  }

  function bootSolo() {
    generation += 1;
    locked = false;
    sending = false;
    lastPlaced = null;
    net = null;
    stopPoll();
    roomEl.hidden = true;
    roomCodeEl.hidden = true;
    clearCelebration();
    game = Throughline.createGame({ seats: 1, seed: nextSeed() });
    document.getElementById('mode-solo').setAttribute('aria-pressed', 'true');
    document.getElementById('mode-live').setAttribute('aria-pressed', 'false');
    render(startMessage());
  }

  document.getElementById('mode-solo').addEventListener('click', function () {
    unlockAudio();
    bootSolo();
  });
  document.getElementById('mode-live').addEventListener('click', function () {
    unlockAudio();
    roomEl.hidden = false;
    document.getElementById('mode-solo').setAttribute('aria-pressed', 'false');
    document.getElementById('mode-live').setAttribute('aria-pressed', 'true');
    if (!net) {
      roomStatusEl.textContent = 'Open a table, then send the code to the other player. Solo stays on this page until someone sits down.';
      roomCodeEl.hidden = true;
    }
  });
  document.getElementById('host').addEventListener('click', function () {
    unlockAudio();
    roomStatusEl.textContent = 'Opening a table.';
    fetch('/api/room', { method: 'POST' }).then(function (res) {
      return res.json();
    }).then(function (payload) {
      if (!payload || !payload.code) {
        roomStatusEl.textContent = 'The session service is not answering. Solo play still works on this page.';
        return;
      }
      adopt(payload, 'You are Copper. Send this code to the other browser.');
    }).catch(function () {
      roomStatusEl.textContent = 'The session service is not answering. Solo play still works on this page.';
    });
  });
  document.getElementById('join-form').addEventListener('submit', function (event) {
    event.preventDefault();
    unlockAudio();
    var code = document.getElementById('join-code').value.replace(/[^a-z0-9]/gi, '').toUpperCase();
    if (code.length !== 4) {
      roomStatusEl.textContent = 'Enter the four character code from the other browser.';
      return;
    }
    fetch('/api/room/' + code + '/join', { method: 'POST' }).then(function (res) {
      return res.json();
    }).then(function (payload) {
      if (!payload || !payload.ok) {
        var reason = payload && payload.reason;
        if (reason === 'full') {
          roomStatusEl.textContent = 'That table already has two players.';
        } else if (reason === 'missing') {
          roomStatusEl.textContent = 'That code does not match an open table.';
        } else {
          roomStatusEl.textContent = 'The session service is not answering. Solo play still works on this page.';
        }
        return;
      }
      adopt(payload, 'You are Teal. Copper has the first turn.');
    }).catch(function () {
      roomStatusEl.textContent = 'The session service is not answering. Solo play still works on this page.';
    });
  });
  document.getElementById('reset').addEventListener('click', function () {
    unlockAudio();
    if (net) {
      if (net.seat !== 0) {
        render('Only the host can deal the next puzzle.');
        return;
      }
      act({ type: 'new' });
      return;
    }
    bootSolo();
  });
  rotateButton.addEventListener('click', function () {
    act({ type: 'rotate' });
  });
  discardButton.addEventListener('click', function () {
    act({ type: 'discard' });
  });
  valveButton.addEventListener('click', function () {
    unlockAudio().then(function () {
      act({ type: 'valve' });
    });
  });
  concedeButton.addEventListener('click', function () {
    act({ type: 'concede' });
  });
  watchButton.addEventListener('click', function () {
    if (net) {
      render('This table is live. Scrap, place, and seal here. Watch a perfect fill is for solo practice.');
      return;
    }
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
    locked = true;
    lastPlaced = null;
    clearCelebration();
    game = Throughline.createGame({ seats: 1, seed: nextSeed() });
    var local = game;
    document.getElementById('mode-solo').setAttribute('aria-pressed', 'true');
    document.getElementById('mode-live').setAttribute('aria-pressed', 'false');
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

    render('Here is one perfect fill, from the source to the gauge.');
    (async function () {
      for (var i = 0; i < steps.length; i++) {
        if (token !== generation) {
          return;
        }
        var step = steps[i];
        var preview = local.getState();
        preview.players[0].selected = 0;
        preview.players[0].hand[0] = Throughline.makePiece(step.type, step.rotation);
        render('Pipe ' + (i + 1) + ' of ' + steps.length + '. ' + describeOpenings(preview.players[0].hand[0].openings));
        await delay(380);
        if (token !== generation) {
          return;
        }
        local.scriptedPlace(step);
        lastPlaced = { x: step.x, y: step.y };
        render('Score is ' + local.getState().players[0].pipes.length + '. The line follows the open end.');
        await delay(220);
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
  bootSolo();
})();
