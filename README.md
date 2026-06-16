# StoreOps — Store Operations Management Platform

StoreOps is a lightweight, high-performance web application designed for retail store operations, maintenance workflow tracking, and financial ledgers. Built with a custom, modern **PHP 8+ MVC Architecture** (without external package dependencies), **MySQL 8+**, **Vanilla JS**, and **Tailwind CSS**.

---

## Key Features

### 📋 Work Order Lifecycle & Configuration
* **Systematic Workflow**: Track work orders from inception to completion: `New` ➔ `Assigned` ➔ `Scheduled` ➔ `Work In Progress` ➔ `Pending` ➔ `Cancelled` ➔ `Done`.
* **SLA Deadlines**: Set and monitor Service Level Agreement (SLA) deadlines per work order.
* **Auto-generated Reference Codes**: Every job is automatically assigned a unique reference code (e.g., `WO-2026-00045`) for quick search and tracking.

### 💰 Financial Ledger & Balance Tracking
* **Contract Valuation**: Set contract value for each work order with real-time outstanding balance calculation.
* **Payment Ledger**: Record full, partial, or pending payments directly on the work order dashboard.
* **W-9 Clearance**: Mark W-9 form requirements and securely upload W-9 documents (PDF or image). Administrators can review and manage W-9 clearance directly.

### 💬 Collaborative Discussion & Attachments
* **Interactive Comments**: Add updates, technical notes, or status reports directly within work orders.
* **Reactions**: Upvote/Downvote discussion points.
* **Inline Edits**: Edit your comments directly from the timeline interface.
* **Media Galleries**: Attach multiple images/documents to comments or jobs, preview them in a modal lightbox, or download all attachments compiled as a `.zip` archive.
* **Unread Indicators**: Displays unread comment counts per job on your dashboard list.

### 📊 Real-Time Analytics & Reporting
* **Financial Metrics**: Track total job values, collected revenue, outstanding balances, and completion rates.
* **Performance Charts**: Dynamic Chart.js visualizations (Doughnut charts for job status distribution and line charts for monthly collection trends).
* **Staff Leaderboard**: Monitor assignee performance, including assigned vs. completed job ratios and revenue cleared.

### ⚙️ System Settings & Polling Controls
* **Real-time Updates**: Adjustable client polling intervals for global notifications, active dashboards, and hidden tab refreshes to optimize database performance.
* **Role-Based Controls**: Secure, distinct dashboards for **Administrators**, **Team Leads**, and **Technicians/Coordinators**.

---

## Tech Stack

* **Frontend**: HTML5, Tailwind CSS (compiled), Vanilla JS, Chart.js
* **Backend**: Custom PHP 8 MVC (no Composer required)
* **Database**: MySQL 8+ (PDO prepared statements)
* **Authentication**: PHP Sessions + secure bcrypt password hashing

---

## Directory Layout

```
/app          Controllers, Models, and Core services
/config       Unified application configurations
/public       Web root (entry point index.php, assets, uploaded files)
/views        Role-based templates (admin, team_lead, user)
/migrations   Database migration scripts
/schema.sql   Full MySQL database schema
/storage/logs Application error logs
```

---

## Production Deployment

1. **Upload files** to your hosting server.
2. **Configure Web Server**: Point the domain document root to the `/public` folder.
3. **Environment Setup**: Copy `.env.example` to `.env` and configure your credentials:
   ```env
   DB_HOST=localhost
   DB_USER=your_db_user
   DB_PASS=your_db_password
   DB_NAME=your_db_name
   APP_ENV=production
   BASE_URL=https://yourdomain.com
   ```
4. **Database Import**: Create a MySQL database and import `schema.sql`. Run any additional migrations or use `public/update_db.php` to update an existing schema (remove/rename this file after execution).
5. **Compile Tailwind CSS** (if styling changes are made):
   ```bash
   npm install
   npm run build:css
   ```
6. **Set File Permissions**: Ensure these directories are writable by the web server (e.g., `chmod -R 775`):
   - `storage/logs/`
   - `public/uploads/`
7. **Required PHP Extensions**: `pdo_mysql`, `fileinfo`, `zip`

---

## Local Development

1. Set up a local Apache/MySQL server (e.g., XAMPP).
2. Create a database, configure `.env` with `APP_ENV=development`.
3. Import `schema.sql` to your local database.
4. Run locally with Apache pointing to `/public` or start the built-in server:
   ```bash
   php -S localhost:8000 -t public
   ```

---

## 👨‍💻 Developer & Support

This platform is developed and maintained by **Amaanullah**. For custom setups, feature integrations, or professional deployment support, feel free to get in touch:

| Resource | Link / Information |
| :--- | :--- |
| **Lead Developer** | **Amaanullah** |
| **Primary Email** | [info@amaanullah.com](mailto:info@amaanullah.com) |
| **Secondary Email** | [akamaanullah@gmail.com](mailto:akamaanullah@gmail.com) |
| **Official Website** | [amaanullah.com](https://amaanullah.com) |

> [!TIP]
> **Custom Workflows & Deployment**
> If you need help integrating third-party APIs, creating custom operational workflows, or setting up production-grade hosting servers, reach out via the business email listed above.

