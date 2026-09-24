# VentureKids

VentureKids is a multi-tenant edtech platform for K-12 entrepreneurship education. Schools sign up as tenants; trainers deliver curriculum content and log observations; students work through levels, assignments, projects, a marketplace, and challenges; partners onboard new schools; and admins manage the whole platform, including RealQ and Standard assessments.

Built on Laravel 8, using [stancl/tenancy](https://tenancyforlaravel.com/) for multi-tenancy (each school is a tenant, sharing one physical database with logical scoping via `tenant_id`/`school_id`) and [spatie/laravel-permission](https://spatie.be/docs/laravel-permission) for roles.

## Portals

| Portal | Group | Notes |
|---|---|---|
| Admin | 1 | Central domain, manages schools, trainers, catalogs, assessments |
| School | 2 | Tenant subdomain, manages the school's own trainers/students |
| Trainer | 3 | Delivers content, reviews assignments, logs observations |
| Student | 4 | Takes content/assignments/assessments, marketplace, challenges |
| Partner | 5 | Onboards new schools (subject to admin approval) |

## Requirements

- PHP ^7.3 or ^8.0
- MySQL
- Composer
- Node.js + npm (for asset compilation via Laravel Mix)

## Installation

1. Clone the repository and run `composer install`
2. Copy the environment file: `cp .env.example .env`
3. Set your database credentials in `.env`, then generate an app key: `php artisan key:generate`
4. Run migrations and seed demo data: `php artisan migrate --seed`
5. Link the storage directory: `php artisan storage:link`
6. Point a local domain (e.g. `kidsnew.test`) at the project, or run `php artisan serve`
7. For school/tenant portals, each seeded school gets its own subdomain (e.g. `demoschool1.kidsnew.test`)

After adding new permissions, clear the permission cache:
```
php artisan cache:forget spatie.permission.cache
```

## Demo Data

`php artisan migrate --seed` runs `database/seeders/DatabaseSeeder.php`, which seeds a full demo dataset end to end — grade/trainer-level catalogs, content streams and sessions, 10 schools (10 students + 5 trainers each), assignments, observations, marketplace listings, daily/industry challenges, and both the RealQ and Standard assessment pipelines (categories/questions/answers/generated reports). Each seeder is independently re-runnable via `php artisan db:seed --class="Database\Seeders\<Name>"`.

### Demo logins
All passwords are `secret` unless noted.

| Role | Email |
|---|---|
| Super Admin | `super@admin.com` |
| School (Demo School 1) | `school1@venturekids.test` (domain `demoschool1.kidsnew.test`) |
| Trainer | `school1.trainer1@venturekids.test` |
| Student | `school1.student1@venturekids.test` |
| Partner | `partner1@venturekids.test` |

## Notes for developers

- `php artisan db:seed` runs inside Laravel's `Model::unguarded()`, which bypasses `$fillable` mass-assignment restrictions — code that only works when called from a seeder (and would silently drop fields elsewhere) is a real risk; several existing services rely on this.
- Content grade levels (`grades`/`Grade`), trainer levels (`trainerlavels`), and student academic grades (`student_grade`) are three distinct catalogs — don't conflate them.
- Several controllers resolve a "default" grade dynamically via `is_primary` + lowest `display_order_id` rather than a hardcoded name/code, since hardcoded lookups break whenever the `grades` catalog is reseeded.
