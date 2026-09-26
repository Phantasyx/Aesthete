/**
 * Two browsers share one table.
 * Run: node test/session.test.js
 */
var assert = require('assert');
var createLobby = require('../session.js').createLobby;

var failures = 0;

function test(name, fn) {
  try {
    fn();
    console.log('ok  ' + name);
  } catch (error) {
    failures += 1;
    console.error('fail  ' + name);
    console.error(error.stack || error.message);
  }
}

test('a guest sees the host scrap a pipe', function () {
  var lobby = createLobby();
  var host = lobby.open(11);
  var guest = lobby.join(host.code);
  assert.strictEqual(guest.ok, true);
  assert.strictEqual(guest.seat, 1);
  assert.strictEqual(guest.state.seed, host.state.seed);
  var before = host.state.players[0].hand.length;
  var acted = lobby.act(host.code, host.token, { type: 'discard' });
  assert.strictEqual(acted.ok, true);
  assert.strictEqual(acted.state.players[0].hand.length, before - 1);
  assert.strictEqual(acted.state.turn, 1);
  var seen = lobby.view(host.code);
  assert.strictEqual(seen.state.players[0].hand.length, before - 1);
  assert.strictEqual(seen.version, acted.version);
});

test('the waiting seat cannot move', function () {
  var lobby = createLobby();
  var host = lobby.open(3);
  var guest = lobby.join(host.code);
  var denied = lobby.act(host.code, guest.token, { type: 'discard' });
  assert.strictEqual(denied.reason, 'wait');
  assert.strictEqual(denied.state.players[0].hand.length, host.state.players[0].hand.length);
});

test('a second guest is turned away', function () {
  var lobby = createLobby();
  var host = lobby.open(4);
  lobby.join(host.code);
  var extra = lobby.join(host.code);
  assert.strictEqual(extra.ok, false);
  assert.strictEqual(extra.reason, 'full');
});

if (failures > 0) {
  console.error(failures + ' failed');
  process.exit(1);
}
console.log('all session tests passed');
