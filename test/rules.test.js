/**
 * Rule checks for the static Aesthete demo.
 * Run: node test/rules.test.js
 */
var assert = require('assert');
var Aesthete = require('../public/rules.js');

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

test('rotation matches the pipe pieces', function () {
  var elbow = Aesthete.makePiece('elbow', 0);
  assert.deepStrictEqual(elbow.openings, { N: false, E: false, S: true, W: true });
  var turned = Aesthete.makePiece('elbow', 2);
  assert.deepStrictEqual(turned.openings, { N: true, E: true, S: false, W: false });
  var straight = Aesthete.makePiece('straight', 1);
  assert.deepStrictEqual(straight.openings, { N: true, E: false, S: true, W: false });
});

test('a pipe can land only on your own leak, opening toward it', function () {
  var game = Aesthete.createGame({ seats: 1, seed: 1 });
  var state = game.getState();
  state.players[0].hand[0] = Aesthete.makePiece('straight', 0);
  state.players[0].selected = 0;
  assert.strictEqual(game.place(3, 3).reason, 'not-leak');
  state.players[0].hand[0] = Aesthete.makePiece('cap', 0);
  assert.strictEqual(game.place(1, 0).reason, 'facing');
  state.players[0].hand[0] = Aesthete.makePiece('straight', 0);
  assert.strictEqual(game.place(1, 0).reason, 'placed');
  assert.strictEqual(state.players[0].pipes.length, 1);
  assert.strictEqual(state.players[0].leaks.length, 1);
  assert.strictEqual(state.players[0].leaks[0].dir, 'W');
  assert.strictEqual(state.players[0].leaks[0].x, 2);
});

test('opening the valve on an open line loses the seat', function () {
  var game = Aesthete.createGame({ seats: 1, seed: 2 });
  var state = game.getState();
  state.players[0].hand[0] = Aesthete.makePiece('cap', 1);
  state.players[0].selected = 0;
  assert.strictEqual(game.place(1, 0).reason, 'placed');
  assert.strictEqual(game.openValve().reason, 'leak');
  assert.strictEqual(state.winner, null);
  assert.strictEqual(game.place(1, 0).reason, 'closed');
});

test('the scripted upper line seals the gauge', function () {
  var game = Aesthete.createGame({ seats: 1, seed: 3 });
  Aesthete.UPPER_LINE.forEach(function (step) {
    var result = game.scriptedPlace(step);
    assert.strictEqual(result.reason, 'placed', JSON.stringify(step));
  });
  var state = game.getState();
  assert.strictEqual(state.players[0].leaks.length, 0);
  assert.strictEqual(game.isSealed(0), true);
  assert.strictEqual(game.openValve().reason, 'sealed');
  assert.strictEqual(state.winner, 0);
});

test('an opening off the board cannot be sealed', function () {
  var game = Aesthete.createGame({ seats: 1, seed: 4 });
  var state = game.getState();
  state.players[0].hand[0] = Aesthete.makePiece('elbow', 1);
  state.players[0].selected = 0;
  assert.deepStrictEqual(state.players[0].hand[0].openings, {
    N: true, E: false, S: false, W: true
  });
  assert.strictEqual(game.place(1, 0).reason, 'placed');
  assert.strictEqual(state.players[0].leaks.length, 0);
  assert.strictEqual(game.openValve().reason, 'leak');
});

test('two seats alternate, and a bad valve gives the other seat the match', function () {
  var game = Aesthete.createGame({ seats: 2, seed: 5 });
  var state = game.getState();
  state.players[0].hand[0] = Aesthete.makePiece('straight', 0);
  state.players[0].selected = 0;
  assert.strictEqual(game.place(1, 0).reason, 'placed');
  assert.strictEqual(state.turn, 1);
  assert.strictEqual(game.place(2, 0).reason, 'not-leak');
  state.players[1].hand[state.players[1].selected] = Aesthete.makePiece('straight', 0);
  assert.strictEqual(game.place(1, 5).reason, 'placed');
  assert.strictEqual(state.turn, 0);
  assert.strictEqual(game.openValve().reason, 'leak');
  assert.strictEqual(state.winner, 1);
});

test('fit prefers a piece that can continue past the leak', function () {
  var game = Aesthete.createGame({ seats: 1, seed: 7 });
  var state = game.getState();
  for (var i = 0; i < 5; i++) {
    state.players[0].hand[i] = Aesthete.makePiece('cap', 0);
  }
  state.players[0].hand[3] = Aesthete.makePiece('straight', 1);
  state.players[0].selected = 0;
  var fitted = game.prepareFit();
  assert.strictEqual(fitted.ok, true);
  assert.strictEqual(fitted.index, 3);
  assert.strictEqual(state.players[0].hand[3].openings.W, true);
  assert.strictEqual(state.players[0].hand[3].openings.E, true);
  assert.strictEqual(state.players[0].hand[0].openings.S, true);
  assert.strictEqual(game.place(1, 0).reason, 'placed');
  assert.strictEqual(state.players[0].leaks.length, 1);
});

test('fit still uses a cap when nothing else can meet the leak', function () {
  var game = Aesthete.createGame({ seats: 1, seed: 8 });
  var state = game.getState();
  for (var i = 0; i < 5; i++) {
    state.players[0].hand[i] = Aesthete.makePiece('cap', 0);
  }
  var fitted = game.prepareFit();
  assert.strictEqual(fitted.index, 0);
  assert.strictEqual(state.players[0].hand[0].openings.W, true);
  assert.strictEqual(state.players[0].hand[0].openings.S, false);
});

test('discard replaces the selected piece and passes a two-seat turn', function () {
  var game = Aesthete.createGame({ seats: 2, seed: 6 });
  var state = game.getState();
  game.discard();
  assert.strictEqual(state.players[0].pipes.length, 0);
  assert.strictEqual(state.players[0].hand.length, 5);
  assert.strictEqual(state.turn, 1);
});

if (failures > 0) {
  console.error(failures + ' failed');
  process.exit(1);
}
console.log('all rules tests passed');
