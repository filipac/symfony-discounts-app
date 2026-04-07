# Legacy Promo App

Small Symfony demo app for product catalog and promo code management.

Please review the codebase as if you joined the team and were asked to give feedback before extending it.

## What the candidate should do

Review this codebase and identify anything risky, unusual, hard to maintain, or incorrect.
Prioritize the issues you think matter most in production.
You do not need to fix everything; we care most about your reasoning.

## Stack

-   Symfony 7
-   Twig
-   Doctrine ORM
-   SQLite

## Run locally

```bash
composer install
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction
symfony server:start
```

## Demo credentials

-   Admin: `admin@example.com` / `adminpass`
-   User: `buyer@example.com` / `userpass`

## Main areas

-   `/` product catalog
-   `/products/new` and `/products/{id}/edit`
-   `/checkout/{id}`
-   `/admin/promos`
-   `/api/products/search?q=mug`

## Quick code browsing

GitHub repo: `https://github.com/filipac/symfony-discounts-app`

Web VS Code: `https://github.dev/filipac/symfony-discounts-app`
