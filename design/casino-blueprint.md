# Roulette Demo Casino – Laravel 11 Blueprint

This document captures the architecture, folder structure, and implementation plan for a production-ready, demo-only roulette casino built with **Laravel 11**, **Blade + Tailwind + Alpine**, **Sanctum**, and **Laravel WebSockets**. Network restrictions in this environment currently block fetching Composer packages (packagist and GitHub returned HTTP 403), so the scaffold and code must be generated once connectivity is available using the steps below.

## Architecture Overview

- **Frontend**: Blade for server-rendered SEO-friendly pages, Tailwind for styling, Alpine for lightweight interactivity and websocket event handling. RTL/LTR aware for Arabic/English, dark premium theme.
- **Backend**: Laravel 11 REST API + Sanctum for auth, domain services for roulette rounds, bet settlement, wallet operations, and fair RNG. Policies enforce USER/ADMIN separation.
- **Realtime**: Laravel WebSockets (or Pusher-compatible) broadcasting round lifecycle events and wallet updates. Channels: `public.roulette.rounds`, `private.user.{id}.wallet`, `private.admin.rounds`.
- **Data model**: Users, Wallets, Rounds, Bets, Transactions, AdminLogs, FAQ/Content. Seeders create admin + demo players and sample rounds/bets.
- **Security**: CSRF, request validation, route throttling, no hardcoded secrets, `.env.example` provided. Clean API error responses.
- **Operations**: Round cadence configurable via `config/casino.php`. Artisan command/queue job drives round lifecycle, emitting events. Redis queue recommended.

## Target Folder Structure (Laravel app root)

```
app/
  Console/Commands/DriveRouletteRounds.php
  Events/{RoundStarted,BetWindowClosed,RoundResulted,WalletUpdated}.php
  Http/
    Controllers/Web/{HomeController,RouletteController,StaticPageController}.php
    Controllers/Api/{AuthController,ProfileController,WalletController,BetController}.php
    Controllers/Api/Admin/{AdminUserController,AdminRoundController,AdminWalletController}.php
    Middleware/EnsureAdmin.php
    Requests/Auth/{LoginRequest,RegisterRequest}.php
    Requests/Bets/PlaceBetRequest.php
    Requests/Admin/AdjustBalanceRequest.php
  Models/{User,Wallet,Round,Bet,Transaction,AdminLog}.php
  Policies/{BetPolicy,AdminPolicy}.php
  Services/{RouletteRoundService,BetSettlementService,WalletService,FairRngService}.php
  Broadcasting/{RoundChannel,UserWalletChannel}.php
  Listeners/{UpdateWalletOnBet,BroadcastRoundEvents}.php
config/
  casino.php   # round cadence, bet limits, payout table, RNG settings
  broadcasting.php # websockets config
routes/
  web.php      # public pages + dashboard
  api.php      # REST API
  channels.php # broadcast channels
resources/
  views/layouts/app.blade.php
  views/components/{navbar,footer,hero,roulette/table,roulette/bet-chip,cards/feature-card}.blade.php
  views/pages/{home,roulette,about,faq,terms,auth/login,auth/register,dashboard/*,admin/*}.blade.php
  lang/{en,ar}/*.php
  css/app.css (Tailwind entry)
database/
  migrations/{create_wallets,create_rounds,create_bets,create_transactions,create_admin_logs,...}.php
  seeders/{DatabaseSeeder,UserSeeder,WalletSeeder,RoundSeeder,BetSeeder,FaqSeeder}.php
public/
  # Vite-built assets
```

## Implementation Steps (to run once network access is available)

1. **Bootstrap Laravel 11 + packages**

```bash
# 1. create project
composer create-project laravel/laravel casino-app
cd casino-app

# 2. auth + realtime deps
composer require laravel/sanctum
composer require beyondcode/laravel-websockets
npm install

# 3. publish configs
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="config"
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="migrations"

# 4. env
cp .env.example .env
php artisan key:generate
```

2. **Add config and domain code**

- `config/casino.php` for round timings, bet limits, payout odds, RNG seed strategy.
- Services: `FairRngService` (seed hash stored on `rounds`), `RouletteRoundService` state machine, `BetSettlementService`, `WalletService` (transactional debits/credits).
- Events & listeners broadcasting to websocket channels.

3. **Migrations & Models**

- `wallets` (user_id, balance), `rounds` (state, timers, result_number/color, seed_hash), `bets` (user_id, round_id, type, selection, amount, payout, status), `transactions` (wallet_id, type, delta, reference), `admin_logs`.
- Extend `users` with `role` (USER/ADMIN), `locale` default `en`, `demo_balance` flag.

4. **API & Validation**

- Auth (register/login via Sanctum), profile update, wallet retrieval, bet placement, bet history.
- Admin endpoints: user list, balance adjust, start/stop rounds, view bets/payouts/logs.
- Form Requests enforce limits and required fields; route throttling on auth and betting groups.

5. **Frontend (Blade + Tailwind + Alpine)**

- Dark premium layout with RTL/LTR switch based on locale.
- Public pages: Home (hero, games CTA), Roulette, About, FAQ, Terms/Responsible Gaming.
- Dashboard: profile, wallet, bet history, game history.
- Admin dashboard: users, rounds, balances, logs.
- Alpine stores to handle countdowns, live bet placement UX, and Echo websocket events.

6. **Realtime wiring**

- Broadcast channels: `public.roulette.rounds`, `private.user.{id}.wallet`, `private.admin.rounds`.
- Events: `RoundStarted`, `BetWindowClosed`, `RoundResulted`, `WalletUpdated`.
- Client: Laravel Echo + websockets driver; Alpine listens and updates UI state.

7. **Testing**

- Unit tests: payout math (`BetSettlementService`), RNG seed hashing, wallet debit/credit.
- Feature tests: auth + Sanctum tokens, bet placement flow, round resolution.

## Notes on RNG & Fairness

- Generate `seed = random_bytes(32)`, persist `hash = hash('sha256', seed)` on `rounds.seed_hash` before result.
- On resolution, store `seed` and computed `result_number`; clients can verify hash matches.
- Use `random_int(0,36)` for European roulette single-zero wheel; derive color (red/black/green) server-side.

## Next Actions When Connectivity is Restored

- Run the bootstrap commands above to scaffold Laravel and install dependencies.
- Add the domain files, migrations, and Blade views per the structure listed.
- Configure `.env` (DB, websockets, queue) and run `php artisan migrate --seed`.
- Start websockets server (`php artisan websockets:serve`) and queue worker (`php artisan queue:work`) for real-time rounds.

```

```
