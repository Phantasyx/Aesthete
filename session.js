/**
 * In-memory tables for two browsers sharing one board.
 * The Worker keeps one table in a Durable Object. The local play server
 * keeps them in this map.
 */
var Throughline = require('./site/rules.js');

var ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

function token() {
  var out = '';
  for (var i = 0; i < 16; i++) {
    out += ALPHABET[Math.floor(Math.random() * ALPHABET.length)];
  }
  return out;
}

function code() {
  var out = '';
  for (var i = 0; i < 4; i++) {
    out += ALPHABET[Math.floor(Math.random() * ALPHABET.length)];
  }
  return out;
}

function publicState(room) {
  return {
    version: room.version,
    seatsFilled: room.guest ? 2 : 1,
    state: room.game.snapshot()
  };
}

function createLobby() {
  var rooms = new Map();

  function open(seed) {
    var roomCode = code();
    while (rooms.has(roomCode)) roomCode = code();
    var room = {
      code: roomCode,
      host: token(),
      guest: null,
      version: 1,
      game: Throughline.createGame({
        seats: 2,
        seed: seed == null ? (Date.now() >>> 0) : (seed >>> 0)
      })
    };
    rooms.set(roomCode, room);
    return {
      code: room.code,
      token: room.host,
      seat: 0,
      version: room.version,
      state: room.game.snapshot()
    };
  }

  function join(roomCode) {
    var room = rooms.get(String(roomCode || '').toUpperCase());
    if (!room) return { ok: false, reason: 'missing' };
    if (room.guest) return { ok: false, reason: 'full' };
    room.guest = token();
    room.version += 1;
    return {
      ok: true,
      code: room.code,
      token: room.guest,
      seat: 1,
      version: room.version,
      state: room.game.snapshot()
    };
  }

  function view(roomCode) {
    var room = rooms.get(String(roomCode || '').toUpperCase());
    if (!room) return { ok: false, reason: 'missing' };
    return Object.assign({ ok: true, code: room.code }, publicState(room));
  }

  function seatFor(room, playerToken) {
    if (playerToken === room.host) return 0;
    if (playerToken && playerToken === room.guest) return 1;
    return -1;
  }

  function act(roomCode, playerToken, body) {
    var room = rooms.get(String(roomCode || '').toUpperCase());
    if (!room) return { ok: false, reason: 'missing' };
    var seat = seatFor(room, playerToken);
    if (seat < 0) return { ok: false, reason: 'token' };
    body = body || {};
    var game = room.game;
    var state = game.getState();
    if (state.over && body.type !== 'new') {
      return Object.assign({ ok: false, reason: 'closed' }, publicState(room));
    }
    if (body.type !== 'new' && state.turn !== seat) {
      return Object.assign({ ok: false, reason: 'wait' }, publicState(room));
    }
    var result = { ok: true, reason: 'ok' };
    if (body.index != null && body.type !== 'new') {
      game.select(body.index);
    }
    if (body.type === 'select') {
      game.select(body.index);
    } else if (body.type === 'rotate') {
      game.rotate();
    } else if (body.type === 'discard') {
      result = game.discard();
    } else if (body.type === 'place') {
      result = game.place(body.x, body.y);
    } else if (body.type === 'valve') {
      result = game.openValve();
    } else if (body.type === 'concede') {
      result = game.concede();
    } else if (body.type === 'new') {
      if (seat !== 0) return Object.assign({ ok: false, reason: 'host' }, publicState(room));
      room.game = Throughline.createGame({ seats: 2, seed: (Date.now() >>> 0) });
      result = { ok: true, reason: 'new' };
    } else {
      return Object.assign({ ok: false, reason: 'unknown' }, publicState(room));
    }
    room.version += 1;
    return Object.assign({ ok: result.ok !== false, reason: result.reason || 'ok' }, publicState(room));
  }

  return { open: open, join: join, view: view, act: act, rooms: rooms };
}

module.exports = { createLobby: createLobby };
