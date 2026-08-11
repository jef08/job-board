# Job Board
 
A full-stack freelancer job board built with **Symfony 7.4** as a portfolio project. Companies can post job listings and review applicants. Freelancers can browse listings, apply, and manage their applications. The project is primarily a demonstration of **CRUD functionality, authentication/authorization, and Symfony architecture**. Creating, reading, updating, and deleting real, related data (listings, applications, profiles) through a properly secured, role-based interface.
 
## What this project demonstrates
 
- **Full CRUD** across multiple related entities (Listings, Applications, Companies, Freelancers)
- **Role-based authentication** with two distinct user types (`ROLE_COMPANY`, `ROLE_FREELANCER`) sharing one `User` entity, split into dedicated `Company`/`Freelancer` profile entities
- **Authorization via Symfony Voters** — ownership checks (e.g. only a listing's owning company can edit/close/delete it; only the applying freelancer can withdraw an application) are centralized rather than duplicated across controllers
- **CSRF protection** on every state-changing action
- **Search, filtering, and pagination** on all browse pages (listings, companies, freelancers)
- **Custom Doctrine queries** for things like "companies currently hiring" and "a freelancer's recent applications"
- **PHP 8.1+ backed enums** for status fields (`ListingStatus`, `ApplicationStatus`) instead of raw strings
- **A responsive Bootstrap 5 frontend** built on Symfony's AssetMapper (no Node/npm build step)
- **Doctrine fixtures with Faker** for realistic seed data
## Tech Stack
 
- PHP 8.2+, Symfony 7.4
- Doctrine ORM 3 / Doctrine Migrations
- PostgreSQL (see below)
- Bootstrap 5, Bootstrap Icons, Symfony AssetMapper, Stimulus
- KnpPaginatorBundle
- Faker + DoctrineFixturesBundle (dev)
## Getting Started
 
### Prerequisites
 
- PHP 8.2 or higher
- Composer
- A running PostgreSQL server
- Symfony CLI
### 1. Clone and install dependencies
 
```bash
git clone https://github.com/jef08/job-board.git
cd job-board
composer install
```
 
### 2. Configure your database
 
Copy `.env` to `.env.local` and set your own `DATABASE_URL`, or edit the line directly if you're just running locally:
 
```
DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"
```
 
Also set a real `APP_SECRET` in `.env.local` (or `.env.dev.local` for the dev environment). Don't leave this blank or commit a real one:
 
```bash
php -r "echo bin2hex(random_bytes(16));"
```
 
### 3. Create the database and run migrations
 
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```
 
### 4. Install frontend assets
 
Bootstrap/Bootstrap Icons are managed via AssetMapper's importmap and aren't committed to the repo. Download them locally:
 
```bash
php bin/console importmap:install
```
 
### 5. Load sample data (recommended)
 
This seeds the database with realistic companies, freelancers, listings, categories, and applications via Faker:
 
```bash
php bin/console doctrine:fixtures:load
```
 
This **purges your database** before seeding — only run it in a local/dev environment.
 
Every generated user shares the password:
 
```
password123
```
 
To find a sample login email:
 
```bash
php bin/console dbal:run-sql "SELECT email, roles FROM \"user\" LIMIT 20"
```
 
### 6. Run the app
 
With Symfony CLI:
```bash
symfony server:start
```
 
Or with PHP's built-in server:
```bash
php -S 127.0.0.1:8000 -t public/
```
 
Visit `http://127.0.0.1:8000`.
 
## Project Structure Highlights
 
- `src/Entity/` — `User`, `Company`, `Freelancer`, `Listing`, `Category`, `Application`
- `src/Security/Voter/` — `ListingVoter`, `ApplicationVoter` for ownership-based authorization
- `src/Enum/` — `ListingStatus`, `ApplicationStatus`
- `src/DataFixtures/AppFixtures.php` — Faker-powered seed data
- `src/Twig/AppExtension.php` — custom Twig filters (e.g. `truncate_words`)
## Notes
 
This is a portfolio/demo project, not a production application. Things like email verification, password reset, and payment processing are intentionally out of scope. Any names or websites invented by Faker are not associated with any real-world companies or people. The SVG background was created through the following website -> https://www.svgbackgrounds.com/set/free-svg-backgrounds-and-patterns/