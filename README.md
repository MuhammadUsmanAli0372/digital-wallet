<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<h2 align="center">🚀 Laravel + Inertia.js + Vue 3 + Pusher + MySQL Starter</h2>

<p align="center">
  <a href="https://laravel.com">Laravel</a> •
  <a href="https://inertiajs.com">Inertia.js</a> •
  <a href="https://vuejs.org">Vue 3</a> •
  <a href="https://pusher.com">Pusher</a> •
  <a href="https://www.mysql.com">MySQL</a>
</p>

---

## 🧩 About the Project

This is a modern Laravel 12 application powered by **Inertia.js** and **Vue 3**, designed for a smooth single-page app experience.
It includes **real-time features** via **Pusher** and uses **MySQL** as the database.

The setup ensures clean structure, responsive UI, and real-time updates (e.g., transactions, notifications, etc.).

---

## ⚙️ Prerequisites

Before setting up the project, make sure you have the following installed on your system:

| Requirement | Version | Description |
|--------------|----------|-------------|
| [PHP](https://www.php.net/downloads.php) | 8.2 or higher | Required by Laravel 12 |
| [Composer](https://getcomposer.org/) | Latest | Dependency manager for PHP |
| [Node.js](https://nodejs.org/) | 18+ or 20+ | For frontend assets & Vite |
| [NPM](https://www.npmjs.com/) or [Bun](https://bun.sh) | Latest | Package manager |
| [MySQL](https://www.mysql.com/) | 8.0+ | Database server |
| [Pusher Account](https://pusher.com) | — | For real-time broadcasting |
| [Git](https://git-scm.com/) | — | For version control |
| [WSL 2 (optional)](https://learn.microsoft.com/en-us/windows/wsl/) | — | Recommended for Windows users |

---

## 🛠️ Installation Steps

Follow these steps to set up and run the project locally:

### 1️⃣ Clone the Repository
```bash
git clone https://github.com/MuhammadUsmanAli0372/digital-wallet
cd digital-wallet
```

### 2️⃣ Install PHP Dependencies
```bash
composer install
```

### 3️⃣ Install Node/Bun Dependencies
If using **NPM**:
```bash
npm install
```
If using **Bun**:
```bash
bun install
```

### 4️⃣ Create Environment File
Copy the example environment file and configure it:
```bash
cp .env.example .env
```

Then update the following values in `.env`:

```dotenv
APP_NAME="Your App Name"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=root
DB_PASSWORD=

# Broadcasting (Pusher)
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1
```

### 5️⃣ Generate Application Key
```bash
php artisan key:generate
```

### 6️⃣ Run Database Migrations & Seeders
```bash
php artisan migrate --seed
```

### 7️⃣ Start the Laravel Development Server
```bash
npm install
```

### 8️⃣ Start the Laravel Development Server
```bash
composer run dev
```

Your application will be available at:
```
http://127.0.0.1:8000
```

---

## ⚡ Real-Time Features

This project uses **Laravel Echo** and **Pusher** for real-time broadcasting.

### Setup in JavaScript (`resources/js/bootstrap.js` or `app.js`):
```js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
  forceTLS: true,
});
```

Make sure the `.env` and `vite.config.js` are correctly configured for your Pusher credentials.

---

## 🧱 Tech Stack

| Layer | Technology |
|-------|-------------|
| Backend | Laravel 12 |
| Frontend | Inertia.js + Vue 3 |
| Database | MySQL |
| Real-Time | Pusher |
| Package Manager | Composer + NPM/Bun |
| Build Tool | Vite |
| Authentication | Laravel Breeze / Fortify (optional) |

---

## 🧪 Running Tests

```bash
php artisan test
```

---

## 🧰 Useful Artisan Commands

| Command | Description |
|----------|-------------|
| `php artisan serve` | Run local development server |
| `php artisan migrate` | Run database migrations |
| `php artisan db:seed` | Seed database with sample data |
| `php artisan queue:work` | Process queued jobs |
| `php artisan route:list` | List all application routes |
| `php artisan event:generate` | Generate missing event classes |

---

## 🧑‍💻 Contributing

Contributions are welcome!
Please fork the repository and submit a pull request with clear commits.

Before pushing code, make sure:
- Code follows **PSR-12** standards.
- All tests pass.
- No sensitive credentials are committed.

---

## 🛡️ License

This project is open-sourced software licensed under the [MIT License](https://opensource.org/licenses/MIT).
