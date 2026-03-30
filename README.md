# CloudVault

CloudVault is a monorepo for a modern personal cloud / NAS platform built with Laravel, Vue 3 and Flutter.

## Workspaces

- `backend`: Laravel 13 API-first backend with Sanctum, chunked uploads, share links, audit logs and notifications.
- `web`: Vue 3 + TypeScript + Tailwind web application for authentication, file browsing, uploads and sharing.
- `mobile`: Flutter app scaffold with clean layers, i18n and live API integration for login and drive browsing.
- `docs`: architecture, roadmap and product decisions.
- `ops`: Docker, Nginx and deployment assets.

## Quick start

### Unified dev command

```bash
./scripts/dev.sh
```

This command auto-configures:

- `backend/.env` for local SQLite development
- `web/.env.local` with the API base URL
- Docker services for Laravel, Vue and Nginx

After startup:

- App: `http://localhost:8080`
- Web dev server: `http://localhost:5173`
- Backend API: `http://localhost:8000/api/v1`

### Backend

```bash
cd backend
cp .env.example .env
php artisan migrate:fresh --seed
php artisan serve
```

### Web

```bash
cd web
npm install
npm run dev
```

### Mobile

```bash
cd mobile
flutter pub get
flutter run
```

## Verified locally

- `cd backend && php artisan test`
- `cd web && npm run build`
- `cd mobile && flutter analyze`
- `cd mobile && flutter test`

## Primary docs

- `docs/architecture/v1.md`
