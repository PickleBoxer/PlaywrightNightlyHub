# PlaywrightNightlyHub

A modern web application for storing, analyzing, and visualizing Playwright test reports generated during nightly test runs. Built with Laravel 12 and React.

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)
![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat-square&logo=laravel)
![React](https://img.shields.io/badge/React-19.0-61DAFB?style=flat-square&logo=react)
![Playwright](https://img.shields.io/badge/Playwright-Reports-2EAD33?style=flat-square&logo=playwright)
![License](https://img.shields.io/badge/License-MIT-blue?style=flat-square)

## 📋 Features

- **Dashboard**: Overview of test reports with key metrics and statistics
- **Report Visualization**: Detailed view of test results with filtering options
- **Data Analysis**: Track test performance over time with metrics and trends
- **Multi-platform Support**: Compare test results across different platforms and configurations
- **Dark/Light Mode**: Customizable UI theme
- **Responsive Design**: Works on desktop and mobile devices

## 📊 Screenshots

(Screenshots would go here)

## 🧩 Tech Stack

### Backend

- **Laravel 12**: PHP framework providing the application backend
- **MySQL/SQLite**: Database for storing report data
- **Inertia.js**: Server-side rendering bridge between Laravel and React

### Frontend

- **React 19**: JavaScript library for building the user interface
- **TypeScript**: Type-safe JavaScript
- **Tailwind CSS**: Utility-first CSS framework
- **Lucide Icons**: Beautiful, consistent icon set
- **Radix UI**: Accessible component primitives

## 🚀 Installation

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+ and npm/yarn
- MySQL (optional, SQLite works out of the box)

### Setup Steps

1. **Clone the repository**

```bash
git clone https://github.com/PickleBoxer/PlaywrightNightlyHub.git
cd PlaywrightNightlyHub
```

2. **Install PHP dependencies**

```bash
composer install
```

3. **Install JavaScript dependencies**

```bash
npm install
```

4. **Environment configuration**

```bash
cp .env.example .env
php artisan key:generate --ansi
```

5. **Configure the database**

For SQLite (default):
```bash
touch database/database.sqlite
```

For MySQL, update your .env file with database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=playwrightnightlyhub
DB_USERNAME=root
DB_PASSWORD=
```

6. **Run database migrations**

```bash
php artisan migrate
```

7. **Build frontend assets**

```bash
npm run build
```

## 🏃‍♂️ Running the Application

### Development Mode

```bash
# Start the Laravel server, queue worker, and Vite development server
composer run dev
```

### Production Mode

```bash
# Build for production
npm run build

# Start the server
php artisan serve
```

## 📤 Uploading Reports

Reports can be uploaded through:

1. **Web Interface**: Navigate to the Upload page in the UI
2. **API Endpoint**: Send POST requests to `/api/reports` with the JSON report file

> [!WARNING]
> TODO: Api Endpoint is not yet implemented true api route.

Example API upload using curl:
```bash
curl -X POST \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -F "report=@/path/to/playwright-report.json" \
  -F "platform=chrome" \
  -F "version=1.0.0" \
  http://your-app-url/api/reports
```

## 🧪 Testing

```bash
# Run all tests
composer test

# Run specific test suites
composer test:unit
composer test:feature
```

## 🛠️ Development Tools

```bash
# Lint PHP code
composer lint

# Fix PHP code style
composer fix

# Type checking
composer test:types

# Code refactoring
composer refactor
```

## 🔄 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 💡 Acknowledgments

- Playwright team for their awesome testing framework
- Laravel for the robust backend framework
- React community for the frontend libraries and components
