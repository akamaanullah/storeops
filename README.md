# StoreOps — Store Operations Management Platform

A lightweight **StoreOps** web application built with **PHP 8+ (Custom MVC)**, **MySQL 8+**, and **Vanilla JS**. Features include role-based dashboards, work orders, realtime polling, browser notifications, comments, and payment tracking.

---

## Tech Stack

- **Frontend**: HTML5, Tailwind CSS (compiled), Vanilla JS
- **Backend**: Custom PHP MVC (no Composer required)
- **Database**: MySQL 8+ with PDO prepared statements
- **Auth**: Secure sessions + bcrypt password hashing

---

## Production Deployment

1. **Upload files** to your hosting account.

2. **Document root** must point to the `/public` folder.

3. **Environment** — copy `.env.example` to `.env` and set:
   ```env
   DB_HOST=localhost
   DB_USER=your_db_user
   DB_PASS=your_db_password
   DB_NAME=your_db_name
   APP_ENV=production
   BASE_URL=https://yourdomain.com
   ```

4. **Database** — in phpMyAdmin, select your database and import `schema.sql` (fresh install only).

5. **Create admin user** — after import, insert an admin account or use Manage Users once logged in.

6. **CSS build** (on your machine before upload, or on server if Node is available):
   ```bash
   npm install
   npm run build:css
   ```

7. **Permissions** — ensure these are writable by the web server:
   - `storage/logs/`
   - `public/uploads/`

8. **PHP extensions required**: `pdo_mysql`, `fileinfo`, `zip`

9. **Security checklist**:
   - `APP_ENV=production` in `.env`
   - Strong database password
   - `.env` never committed to git
   - HTTPS enabled on domain

---

## Local Development

1. Create a MySQL database and import `schema.sql`.
2. Copy `.env.example` to `.env` with local credentials.
3. Set `APP_ENV=development` for local debugging.
4. Run `npm install && npm run build:css`.
5. Point Apache to `/public` or use:
   ```bash
   php -S localhost:8000 -t public
   ```

---

## Directory Layout

```
/app          Controllers, Models, Core services
/config       Application configuration
/public       Web root (index.php, css, uploads)
/views        Role-based templates (admin, team_lead, user)
/schema.sql   Complete database schema
/storage/logs Error logs (production)
```

---

## Work Order Workflow

`New → Assigned → Scheduled → Work In Progress → Done`

Roles: **Administrator**, **Team Lead**, **User**
