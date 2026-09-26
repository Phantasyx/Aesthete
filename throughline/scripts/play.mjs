/**
 * Serve the built page and the shared tables on one origin.
 * node scripts/play.mjs
 */
import { createServer } from 'node:http';
import { createReadStream, existsSync, statSync } from 'node:fs';
import { createRequire } from 'node:module';
import { extname, join, normalize } from 'node:path';
import { fileURLToPath } from 'node:url';

const require = createRequire(import.meta.url);
const { createLobby } = require('../session.js');
const lobby = createLobby();

const root = fileURLToPath(new URL('../dist/', import.meta.url));
const port = Number(process.env.PORT || 8094);
const types = {
  '.html': 'text/html; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8',
  '.mp3': 'audio/mpeg',
  '.txt': 'text/plain; charset=utf-8'
};

function send(res, status, body) {
  var data = JSON.stringify(body);
  res.writeHead(status, {
    'content-type': 'application/json; charset=utf-8',
    'cache-control': 'no-store'
  });
  res.end(data);
}

function readBody(req) {
  return new Promise(function (resolve) {
    var chunks = [];
    req.on('data', function (chunk) { chunks.push(chunk); });
    req.on('end', function () {
      if (!chunks.length) {
        resolve({});
        return;
      }
      try {
        resolve(JSON.parse(Buffer.concat(chunks).toString('utf8')));
      } catch (error) {
        resolve({});
      }
    });
  });
}

var server = createServer(async function (req, res) {
  var url = new URL(req.url, 'http://127.0.0.1');
  if (url.pathname === '/api/room' && req.method === 'POST') {
    send(res, 200, lobby.open());
    return;
  }
  var join = url.pathname.match(/^\/api\/room\/([A-Za-z0-9]{4})\/join$/);
  if (join && req.method === 'POST') {
    var joined = lobby.join(join[1]);
    send(res, joined.ok ? 200 : 409, joined);
    return;
  }
  var act = url.pathname.match(/^\/api\/room\/([A-Za-z0-9]{4})\/act$/);
  if (act && req.method === 'POST') {
    var body = await readBody(req);
    var result = lobby.act(act[1], body.token, body);
    send(res, result.ok ? 200 : 409, result);
    return;
  }
  var view = url.pathname.match(/^\/api\/room\/([A-Za-z0-9]{4})$/);
  if (view && req.method === 'GET') {
    var room = lobby.view(view[1]);
    send(res, room.ok ? 200 : 404, room);
    return;
  }

  var rel = decodeURIComponent(url.pathname);
  if (rel === '/') rel = '/index.html';
  var file = normalize(join(root, rel));
  if (!file.startsWith(root) || !existsSync(file) || !statSync(file).isFile()) {
    res.writeHead(404);
    res.end('Not found');
    return;
  }
  res.writeHead(200, { 'content-type': types[extname(file)] || 'application/octet-stream' });
  createReadStream(file).pipe(res);
});

server.listen(port, '127.0.0.1', function () {
  console.log('Throughline table at http://127.0.0.1:' + port + '/');
});
