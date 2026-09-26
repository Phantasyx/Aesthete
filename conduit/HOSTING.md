# Host Conduit on conduit.phantasyx.com

Conduit is a static site. The upload is the `dist/` directory produced by `node scripts/build.mjs`. The intended hostname is a subdomain of phantasyx.com, not a path on the marketing site.

`wrangler.jsonc` names the Worker `conduit`, serves `./dist`, and attaches the custom domain `conduit.phantasyx.com`. There is no Worker script and no bindings. Deploying that config asks Cloudflare to create the DNS record and the certificate. Do not add a CNAME for `conduit` yourself first. A custom domain cannot be attached to a hostname that already has a CNAME record.

## Before you deploy

- `phantasyx.com` is an active zone on the same Cloudflare account you use for Wrangler.
- Nothing already owns `conduit.phantasyx.com`. If a DNS record is there, delete it in the zone’s DNS page, then deploy.
- You are logged in: `npx wrangler@4 whoami`.
- `dist/` matches `site/`: `node scripts/build.mjs`.

## Deploy

From this directory:

```bash
node scripts/build.mjs
npx wrangler@4 deploy
```

Wrangler uploads `dist/` and registers the custom domain. Cloudflare creates the DNS record and an edge certificate for `conduit.phantasyx.com`. Certificate issuance can take a few minutes. Then open `https://conduit.phantasyx.com/`.

The same command also publishes a `conduit.<account>.workers.dev` hostname because `workers_dev` is true. That hostname is a preview. The public link is the subdomain.

## Dashboard, if you attach the name by hand

1. Workers & Pages → **conduit** → Settings → Domains & Routes.
2. Add → Custom domain → `conduit.phantasyx.com`.
3. Leave the marketing Worker that serves `phantasyx.com` and `www.phantasyx.com` alone. A custom domain on `conduit` does not take over the apex.

## Local preview of the build

```bash
node scripts/build.mjs
npx wrangler@4 dev
```

Or, with no Wrangler install:

```bash
node scripts/build.mjs
python3 -m http.server 8080 --directory dist
```

## If a subdomain cannot be added

Use a path only when the zone cannot take `conduit.phantasyx.com` (the name is already claimed, or you cannot deploy a second Worker on the account). Copy the four files in `dist/` to the marketing Worker’s static root at `/examples/conduit/`. Links inside the page are relative, so that folder works without a build change. Do that instead of the subdomain, not in addition to pointing the subdomain at a path.
