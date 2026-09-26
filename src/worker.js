/**
 * Static page plus one Durable Object per shared table.
 * The browser uses the same /api/room routes as scripts/play.mjs.
 */
import Throughline from '../site/rules.js';

function token() {
  var alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  var out = '';
  for (var i = 0; i < 16; i++) out += alphabet[Math.floor(Math.random() * alphabet.length)];
  return out;
}

export class GameSession extends DurableObject {
  constructor(ctx, env) {
    super(ctx, env);
    this.loaded = ctx.blockConcurrencyWhile(async () => {
      this.saved = await ctx.storage.get('room');
      this.game = null;
      if (this.saved && this.saved.state) {
        this.game = Throughline.createGame({ seats: 2, seed: this.saved.state.seed });
        this.game.restore(this.saved.state);
      }
    });
  }

  async ready() {
    await this.loaded;
  }

  async persist() {
    await this.ctx.storage.put('room', this.saved);
  }

  async open() {
    await this.ready();
    if (!this.saved) {
      var seed = Date.now() >>> 0;
      this.game = Throughline.createGame({ seats: 2, seed: seed });
      this.saved = {
        host: token(),
        guest: null,
        version: 1,
        state: this.game.snapshot()
      };
      await this.persist();
    }
    return {
      token: this.saved.host,
      seat: 0,
      version: this.saved.version,
      state: this.saved.state
    };
  }

  async join() {
    await this.ready();
    if (!this.saved) return { ok: false, reason: 'missing' };
    if (this.saved.guest) return { ok: false, reason: 'full' };
    this.saved.guest = token();
    this.saved.version += 1;
    await this.persist();
    return {
      ok: true,
      token: this.saved.guest,
      seat: 1,
      version: this.saved.version,
      state: this.saved.state
    };
  }

  async view() {
    await this.ready();
    if (!this.saved) return { ok: false, reason: 'missing' };
    return {
      ok: true,
      version: this.saved.version,
      seatsFilled: this.saved.guest ? 2 : 1,
      state: this.saved.state
    };
  }

  async act(playerToken, body) {
    await this.ready();
    if (!this.saved || !this.game) return { ok: false, reason: 'missing' };
    var seat = playerToken === this.saved.host ? 0 : (playerToken === this.saved.guest ? 1 : -1);
    if (seat < 0) return { ok: false, reason: 'token' };
    body = body || {};
    var state = this.game.getState();
    if (state.over && body.type !== 'new') {
      return { ok: false, reason: 'closed', version: this.saved.version, state: this.saved.state, seatsFilled: this.saved.guest ? 2 : 1 };
    }
    if (body.type !== 'new' && state.turn !== seat) {
      return { ok: false, reason: 'wait', version: this.saved.version, state: this.saved.state, seatsFilled: this.saved.guest ? 2 : 1 };
    }
    var result = { ok: true, reason: 'ok' };
    if (body.index != null && body.type !== 'new') this.game.select(body.index);
    if (body.type === 'select') this.game.select(body.index);
    else if (body.type === 'rotate') this.game.rotate();
    else if (body.type === 'discard') result = this.game.discard();
    else if (body.type === 'place') result = this.game.place(body.x, body.y);
    else if (body.type === 'valve') result = this.game.openValve();
    else if (body.type === 'concede') result = this.game.concede();
    else if (body.type === 'new') {
      if (seat !== 0) return { ok: false, reason: 'host', version: this.saved.version, state: this.saved.state };
      this.game = Throughline.createGame({ seats: 2, seed: Date.now() >>> 0 });
      result = { ok: true, reason: 'new' };
    } else {
      return { ok: false, reason: 'unknown' };
    }
    this.saved.version += 1;
    this.saved.state = this.game.snapshot();
    await this.persist();
    return {
      ok: result.ok !== false,
      reason: result.reason || 'ok',
      version: this.saved.version,
      seatsFilled: this.saved.guest ? 2 : 1,
      state: this.saved.state
    };
  }
}

function roomCode() {
  var alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  var out = '';
  for (var i = 0; i < 4; i++) out += alphabet[Math.floor(Math.random() * alphabet.length)];
  return out;
}

function json(status, body) {
  return new Response(JSON.stringify(body), {
    status: status,
    headers: { 'content-type': 'application/json; charset=utf-8', 'cache-control': 'no-store' }
  });
}

export default {
  async fetch(request, env) {
    var url = new URL(request.url);
    if (url.pathname === '/api/room' && request.method === 'POST') {
      var code = roomCode();
      var opened = await env.SESSIONS.getByName(code).open();
      opened.code = code;
      return json(200, opened);
    }
    var join = url.pathname.match(/^\/api\/room\/([A-Za-z0-9]{4})\/join$/);
    if (join && request.method === 'POST') {
      var joined = await env.SESSIONS.getByName(join[1].toUpperCase()).join();
      if (joined.ok) joined.code = join[1].toUpperCase();
      return json(joined.ok ? 200 : 409, joined);
    }
    var act = url.pathname.match(/^\/api\/room\/([A-Za-z0-9]{4})\/act$/);
    if (act && request.method === 'POST') {
      var body = await request.json().catch(function () { return {}; });
      var result = await env.SESSIONS.getByName(act[1].toUpperCase()).act(body.token, body);
      return json(result.ok ? 200 : 409, result);
    }
    var view = url.pathname.match(/^\/api\/room\/([A-Za-z0-9]{4})$/);
    if (view && request.method === 'GET') {
      var room = await env.SESSIONS.getByName(view[1].toUpperCase()).view();
      if (room.ok) room.code = view[1].toUpperCase();
      return json(room.ok ? 200 : 404, room);
    }
    return env.ASSETS.fetch(request);
  }
};
