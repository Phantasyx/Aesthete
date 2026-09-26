/**
 * Rule checks for the Throughline demo.
 * Run: node test/rules.test.js
 */
var assert = require('assert');
var Throughline = require('../site/rules.js');

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
  var elbow = Throughline.makePiece('elbow', 0);
  assert.deepStrictEqual(elbow.openings, { N: false, E: false, S: true, W: true });
  var turned = Throughline.makePiece('elbow', 2);
  assert.deepStrictEqual(turned.openings, { N: true, E: true, S: false, W: false });
  var straight = Throughline.makePiece('straight', 1);
  assert.deepStrictEqual(straight.openings, { N: true, E: false, S: true, W: false });
});

test('a pipe can land only on your own leak, opening toward it', function () {
  var game = Throughline.createGame({ seats: 1, seed: 1, layout: 'classic' });
  var state = game.getState();
  state.players[0].hand[0] = Throughline.makePiece('straight', 0);
  state.players[0].selected = 0;
  assert.strictEqual(game.place(3, 3).reason, 'not-leak');
  state.players[0].hand[0] = Throughline.makePiece('cap', 0);
  assert.strictEqual(game.place(1, 0).reason, 'facing');
  state.players[0].hand[0] = Throughline.makePiece('straight', 0);
  assert.strictEqual(game.place(1, 0).reason, 'placed');
  assert.strictEqual(state.players[0].pipes.length, 1);
  assert.strictEqual(state.players[0].leaks.length, 1);
  assert.strictEqual(state.players[0].leaks[0].dir, 'W');
  assert.strictEqual(state.players[0].leaks[0].x, 2);
});

test('opening the valve on an open line loses the seat', function () {
  var game = Throughline.createGame({ seats: 1, seed: 2, layout: 'classic' });
  var state = game.getState();
  state.players[0].hand[0] = Throughline.makePiece('cap', 1);
  state.players[0].selected = 0;
  assert.strictEqual(game.place(1, 0).reason, 'placed');
  assert.strictEqual(game.openValve().reason, 'leak');
  assert.strictEqual(state.winner, null);
  assert.strictEqual(game.place(1, 0).reason, 'closed');
});

test('the scripted upper line seals the gauge', function () {
  var game = Throughline.createGame({ seats: 1, seed: 3, layout: 'classic' });
  Throughline.UPPER_LINE.forEach(function (step) {
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
  var game = Throughline.createGame({ seats: 1, seed: 4, layout: 'classic' });
  var state = game.getState();
  state.players[0].hand[0] = Throughline.makePiece('elbow', 1);
  state.players[0].selected = 0;
  assert.deepStrictEqual(state.players[0].hand[0].openings, {
    N: true, E: false, S: false, W: true
  });
  assert.strictEqual(game.place(1, 0).reason, 'placed');
  assert.strictEqual(state.players[0].leaks.length, 0);
  assert.strictEqual(game.openValve().reason, 'leak');
});

test('two seats alternate, and a bad valve gives the other seat the match', function () {
  var game = Throughline.createGame({ seats: 2, seed: 5, layout: 'classic' });
  var state = game.getState();
  state.players[0].hand[0] = Throughline.makePiece('straight', 0);
  state.players[0].selected = 0;
  assert.strictEqual(game.place(1, 0).reason, 'placed');
  assert.strictEqual(state.turn, 1);
  assert.strictEqual(game.place(2, 0).reason, 'not-leak');
  state.players[1].hand[state.players[1].selected] = Throughline.makePiece('straight', 0);
  assert.strictEqual(game.place(1, 5).reason, 'placed');
  assert.strictEqual(state.turn, 0);
  assert.strictEqual(game.openValve().reason, 'leak');
  assert.strictEqual(state.winner, 1);
});

test('fit prefers a piece that can continue past the leak', function () {
  var game = Throughline.createGame({ seats: 1, seed: 7, layout: 'classic' });
  var state = game.getState();
  state.players[0].hand = [];
  for (var i = 0; i < 5; i++) {
    state.players[0].hand.push(Throughline.makePiece('cap', 0));
  }
  state.players[0].hand[3] = Throughline.makePiece('straight', 1);
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
  var game = Throughline.createGame({ seats: 1, seed: 8, layout: 'classic' });
  var state = game.getState();
  state.players[0].hand = [];
  for (var i = 0; i < 5; i++) {
    state.players[0].hand.push(Throughline.makePiece('cap', 0));
  }
  var fitted = game.prepareFit();
  assert.strictEqual(fitted.index, 0);
  assert.strictEqual(state.players[0].hand[0].openings.W, true);
  assert.strictEqual(state.players[0].hand[0].openings.S, false);
});

test('scrap removes the pipe and passes a two-seat turn', function () {
  var game = Throughline.createGame({ seats: 2, seed: 6 });
  var state = game.getState();
  var before = state.players[0].hand.length;
  var supply = state.players[0].supply;
  game.discard();
  assert.strictEqual(state.players[0].pipes.length, 0);
  assert.strictEqual(state.players[0].hand.length, before - 1);
  assert.strictEqual(before, supply);
  assert.strictEqual(state.turn, 1);
});

test('a generated board can be sealed, and two seeds are not the same route', function () {
  var seen = {};
  for (var seed = 1; seed <= 20; seed++) {
    var game = Throughline.createGame({ seats: 1, seed: seed });
    var state = game.getState();
    var signature = state.solutions[0].map(function (step) {
      return step.x + ',' + step.y;
    }).join('|');
    seen[signature] = true;
    assert.strictEqual(state.solutions[0].length, 18);
    assert.strictEqual(state.players[0].hand.length, 18);
    var covered = {};
    state.solutions[0].forEach(function (step) {
      covered[step.x + ',' + step.y] = true;
    });
    assert.strictEqual(Object.keys(covered).length, 18);
    var results = game.replay(0);
    results.forEach(function (result, index) {
      assert.strictEqual(result.reason, 'placed', signature + ' step ' + index);
    });
    assert.strictEqual(game.isSealed(0), true, signature);
    assert.strictEqual(game.openValve().reason, 'sealed');
  }
  assert.ok(Object.keys(seen).length >= 2, JSON.stringify(Object.keys(seen)));
});

test('two generated lines do not share a cell, and each line can be sealed', function () {
  var game = Throughline.createGame({ seats: 2, seed: 42 });
  var state = game.getState();
  var upper = {};
  state.solutions[0].forEach(function (step) {
    upper[step.x + ',' + step.y] = true;
  });
  state.solutions[1].forEach(function (step) {
    assert.strictEqual(upper[step.x + ',' + step.y], undefined);
  });
  assert.notStrictEqual(state.players[0].source.y, state.players[1].source.y);
  game.replay(0).forEach(function (result) {
    assert.strictEqual(result.reason, 'placed');
  });
  assert.strictEqual(game.isSealed(0), true);
  game.replay(1).forEach(function (result) {
    assert.strictEqual(result.reason, 'placed');
  });
  assert.strictEqual(game.isSealed(1), true);
});

test('a short run seals for less than a perfect fill', function () {
  var game = Throughline.createGame({ seats: 1, seed: 9 });
  var state = game.getState();
  var perfect = state.solutions[0].length;
  var steps = [
    { x: 1, y: 0, type: 'straight', rotation: 0 },
    { x: 2, y: 0, type: 'straight', rotation: 0 },
    { x: 3, y: 0, type: 'straight', rotation: 0 },
    { x: 4, y: 0, type: 'straight', rotation: 0 },
    { x: 5, y: 0, type: 'straight', rotation: 0 },
    { x: 6, y: 0, type: 'elbow', rotation: 0 },
    { x: 6, y: 1, type: 'straight', rotation: 1 },
    { x: 6, y: 2, type: 'elbow', rotation: 2 }
  ];
  steps.forEach(function (step) {
    assert.strictEqual(game.scriptedPlace(step).reason, 'placed', JSON.stringify(step));
  });
  assert.strictEqual(game.isSealed(0), true);
  assert.strictEqual(state.players[0].pipes.length, steps.length);
  assert.ok(steps.length < perfect);
  assert.strictEqual(game.openValve().reason, 'sealed');
});

test('scrapping lowers the best score you can still reach', function () {
  var game = Throughline.createGame({ seats: 1, seed: 4 });
  var state = game.getState();
  var perfect = state.solutions[0].length;
  game.discard();
  var reachable = state.players[0].pipes.length + state.players[0].hand.length;
  assert.strictEqual(reachable, perfect - 1);
});

if (failures > 0) {
  console.error(failures + ' failed');
  process.exit(1);
}
console.log('all rules tests passed');
