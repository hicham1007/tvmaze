# TV Show Finder

A small web app to search TV shows and open the details of each one. Data comes from the public [TVMaze API](https://www.tvmaze.com/api). The browser never calls TVMaze directly: a Laravel backend does, and the React frontend only talks to that backend.

This project was built as a technical test.

## Running it

Docker is the only requirement.

```bash
docker compose up --build
```

The app is served at http://localhost:8080. Use `APP_PORT` to pick another port.

The API can be called directly:

```bash
curl 'http://localhost:8080/api/shows?q=dark'
```

## Development

A second compose file mounts the source code into the containers and reloads on save. The frontend runs at http://localhost:5173 and the API at http://localhost:8000.

```bash
make dev      # same as: docker compose -f docker-compose.dev.yml up --build
make check    # linters, type checks and tests for both projects, inside the containers
make format   # fix code style
make down
```

`make sh-backend` and `make sh-frontend` open a shell in each container, for Artisan commands or installing dependencies.

## Architecture

```
browser ──► proxy ──┬── /api ──► backend (Laravel) ──► TVMaze
                    │                 └──► Redis
                    └── other ──► frontend (React, static files)
```

| Path | Contents |
| --- | --- |
| `backend/` | JSON API. Laravel 13, PHP 8.4, no database |
| `frontend/` | Single-page app. React 19, TypeScript, Vite, Tailwind |
| `proxy/` | Reverse proxy (nginx) and single entry point |
| `docker-compose.yml` | Production-like images |
| `docker-compose.dev.yml` | Development environment with live reload |

## API

| Endpoint | Description |
| --- | --- |
| `GET /api/shows?q={text}` | Search shows by name. `q` is required, up to 100 characters |
| `GET /api/shows/{id}` | Details of one show, including its main cast |

Successful responses are wrapped in `data`. Errors always carry a `message` and a `code`:

| Status | `code` | Meaning |
| --- | --- | --- |
| 404 | `show_not_found` | The show does not exist |
| 422 | | `q` is missing or too long (standard Laravel validation body) |
| 502 | `upstream_error` | TVMaze answered with an error |
| 503 | `upstream_rate_limited` | TVMaze is rate limiting us |
| 504 | `upstream_unreachable` | TVMaze did not answer |

## Configuration

Set these on the command line or in a `.env` file next to `docker-compose.yml` (see `.env.example`).

| Variable | Default | Description |
| --- | --- | --- |
| `APP_PORT` | `8080` | Port the app is published on |
| `CORS_ALLOWED_ORIGINS` | empty | Other origins allowed to call the API from a browser, comma separated |
| `API_SAME_ORIGIN_ONLY` | `off` | With `on`, the API only answers requests made by the app itself; anything else gets a 403 |

## Credits

Show data and images are provided by [TVMaze](https://www.tvmaze.com), licensed under CC BY-SA.
