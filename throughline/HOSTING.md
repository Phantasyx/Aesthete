# Host Throughline on throughline.phantasyx.com

Throughline is a static page plus one Durable Object per shared table. The page upload is the `dist/` directory produced by `node scripts/build.mjs`. The Worker in `src/worker.js` serves that page and the `/api/room` session routes. The intended hostname is a subdomain of phantasyx.com, not a path on the marketing site.

`wrangler.jsonc` names the Worker `throughline`, binds `./dist` as `ASSETS`, binds a SQLite Durable Object class `GameSession` as `SESSIONS`, and attaches the custom domain `throughline.phantasyx.com`. Deploying that config asks Cloudflare to create the DNS record and the certificate. Do not add a CNAME for `throughline` yourself first. A custom domain cannot be attached to a hostname that already has a CNAME record. The migration tag `v1` creates the `GameSession` class. Do not deploy from automation. A person with the zone should run the command below when they want it live.

## Before you deploy

- `phantasyx.com` is an active zone on the same Cloudflare account you use for Wrangler.
- Nothing already owns `throughline.phantasyx.com`. If a DNS record is there, delete it in the zone’s DNS page, then deploy.
- You are logged in: `npx wrangler@4 whoami`.
- `dist/` matches `site/`: `node scripts/build.mjs`.

## Deploy

From this directory:

```bash
node scripts/build.mjs
npx wrangler@4 deploy
```

Wrangler uploads `dist/`, the Worker, and the Durable Object migration, then registers the custom domain. Cloudflare creates the DNS record and an edge certificate for `throughline.phantasyx.com`. Certificate issuance can take a few minutes. Then open `https://throughline.phantasyx.com/`. Solo play works as soon as the page loads. Two browsers share a table only after that Worker is deployed, because the session lives in the Durable Object.

The same command also publishes a `throughline.<account>.workers.dev` hostname because `workers_dev` is true. That hostname is a preview. The public link is the subdomain.

## Dashboard, if you attach the name by hand

1. Workers & Pages → **throughline** → Settings → Domains & Routes.
2. Add → Custom domain → `throughline.phantasyx.com`.
3. Leave the marketing Worker that serves `phantasyx.com` and `www.phantasyx.com` alone. A custom domain on `throughline` does not take over the apex.

## Local preview of the build

```bash
node scripts/build.mjs
npx wrangler@4 dev
```

Or, with no Wrangler install, the same page and the same session API:

```bash
node scripts/build.mjs
node scripts/play.mjs
```

Open `http://127.0.0.1:8094/`. A plain static server can preview the page, and solo play works, but it has no table API.

## If a subdomain cannot be added

Use a path only when the zone cannot take `throughline.phantasyx.com` (the name is already claimed, or you cannot deploy a second Worker on the account). Copy `dist/` to the marketing Worker’s static root at `/examples/throughline/`, including `audio/factory.mp3`. Links inside the page are relative, so that folder works without a build change. Do that instead of the subdomain, not in addition to pointing the subdomain at a path.
