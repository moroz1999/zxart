# Local HTTP Access

The site runs in the `app` container. A browser on the host opens it as
`http://zxart.loc/` (the domain points at `127.0.0.1` in the OS hosts file).

**Always send requests from inside the container.** A host shell may be
sandboxed and reach nothing but `/`, which looks exactly like a broken site: a
plain Apache 404 on every real route.

```bash
docker compose exec -T app curl -s http://zxart.loc/prod/589898              # page
docker compose exec -T app curl -s "http://zxart.loc/prod-details/?id=589898" # JSON API
docker compose exec -T app curl -s -o /dev/null -w "%{http_code}\n" http://zxart.loc/authors/
docker compose exec -T app curl -s -X POST --data-binary @/tmp/screen.scr \
  "http://zxart.loc/screenshot-upload/?id=589898&format=standard"            # binary body
```

Use the `zxart.loc` host name, not `localhost` — `docker-compose.yml` pins it to
`127.0.0.1` inside the container via `extra_hosts`, so it resolves without the
host's DNS.

Requests are anonymous: endpoints behind a privilege answer `403`, which is a
valid result to assert.

Logs: `docker compose logs --tail=50 app` for Apache access lines,
`temporary/logs/` for application errors.
