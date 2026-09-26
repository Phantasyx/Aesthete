/**
 * Copy the site sources into dist/, which is what Wrangler uploads.
 * Run from anywhere: node conduit/scripts/build.mjs
 * or, inside this project: node scripts/build.mjs
 */
import { cpSync, readdirSync, rmSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(root, 'site');
const dist = join(root, 'dist');

rmSync(dist, { recursive: true, force: true });
cpSync(site, dist, {
  recursive: true,
  filter: (source) => !source.split(/[\\/]/).some((part) => part.startsWith('.')),
});
const files = readdirSync(dist).filter((name) => !name.startsWith('.'));
console.log('dist/: ' + files.join(', '));
