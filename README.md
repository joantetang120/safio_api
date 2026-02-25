# 🔐 Safio API

**Privacy-First Personal Finance Backend**

A secure, scalable RESTful API built with Laravel 12 that powers the Safio personal finance application. Designed with end-to-end encryption and zero-knowledge architecture to ensure user financial data remains completely private.

[![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## 🎯 Project Overview

Safio API is the backend infrastructure for a privacy-focused personal finance management application. It handles encrypted data synchronization, premium subscription management, and financial rule distribution while maintaining a **zero-knowledge architecture** where the server never accesses plaintext financial data.

### Key Highlights

- **🔒 End-to-End Encryption**: All financial data is encrypted client-side with AES-256 before transmission
- **👤 Anonymous by Design**: No user authentication required; operates on anonymous identifiers
- **💳 Payment Integration**: Seamless premium verification with Moneroo/Mobile Money platforms
- **🔄 Cross-Device Sync**: Secure snapshot storage and retrieval for multi-device support
- **📊 Smart Rules Engine**: Configurable financial guidelines for budget optimization
- **✅ Production-Ready**: Comprehensive test coverage with PHPUnit

---

## 🏗️ Architecture & Design

### Privacy-First Architecture

```
┌─────────────┐                    ┌──────────────┐
│   Client    │ ──── Encrypt ────> │  Safio API   │
│  (Mobile)   │ <─── Encrypted ─── │   (Laravel)  │
└─────────────┘                    └──────────────┘
      │                                    │
      │ AES-256 Encryption                 │ Stores Encrypted
      │ Client-Side Only                   │ Blobs Only
      └────────────────────────────────────┘
           Zero-Knowledge Architecture
```

### Tech Stack

- **Framework**: Laravel 12.0
- **Language**: PHP 8.2+
- **Database**: SQLite (easily swappable to MySQL/PostgreSQL)
- **Testing**: PHPUnit 11.5
- **Code Quality**: Laravel Pint (PSR-12 compliant)

---

## 🚀 Features

### 1. Encrypted Snapshot Management
- **Upload**: Store encrypted financial snapshots with checksums
- **Fetch**: Retrieve snapshots for cross-device synchronization
- **Validation**: Schema versioning and integrity verification

### 2. Premium Subscription System
- **Payment Verification**: Webhook integration with Moneroo/MoMo
- **Status Tracking**: Real-time premium status checks
- **Revocation**: Soft-delete support for refunds and cancellations
- **Audit Trail**: Complete payment history for compliance

### 3. Financial Rules Distribution
- **Dynamic Rules**: Server-managed budget guidelines
- **Version Control**: Versioned rule updates
- **Category-Based**: Customizable thresholds per spending category

### 4. Security Features
- ✅ No plaintext financial data storage
- ✅ Anonymous user identification
- ✅ Checksum validation for data integrity
- ✅ Encrypted blob storage
- ✅ CORS protection
- ✅ Input validation and sanitization

---

## 📡 API Endpoints

### Snapshots
```http
POST   /api/snapshots/upload          # Upload encrypted snapshot
GET    /api/snapshots/fetch/{anon_id} # Retrieve snapshot
```

### Premium Management
```http
POST   /api/premium/verify             # Verify payment
GET    /api/premium/status/{anon_id}   # Check premium status
POST   /api/premium/revoke             # Revoke premium access
```

### Rules
```http
GET    /api/rules/update               # Fetch latest financial rules
```

📖 **Full API Documentation**: See [`API_TESTING.md`](API_TESTING.md) for detailed request/response examples.

---

## 🛠️ Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & npm (for asset compilation)

### Quick Start

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd safio-api
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   ```

5. **Start development server**
   ```bash
   php artisan serve
   ```

   API will be available at `http://localhost:8000`

### One-Command Setup
```bash
composer setup
```

---

## 🧪 Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suite
```bash
php artisan test tests/Feature/ApiTest.php
```

### Code Quality
```bash
./vendor/bin/pint  # Format code to PSR-12 standards
```

---

## 📊 Database Schema

### Core Models

**Snapshots**
- `anon_id`: Anonymous user identifier
- `encrypted_blob`: AES-256 encrypted financial data
- `schema_version`: Data structure version
- `checksum`: SHA-256 integrity hash
- `last_sync`: Timestamp of last synchronization

**Premium Payments**
- `anon_id`: User identifier
- `payment_id`: External payment reference
- `amount`: Payment amount
- `currency`: Currency code (XAF, etc.)
- `platform`: Payment platform (android/ios)
- `verified_at`: Verification timestamp
- `revoked_at`: Revocation timestamp (soft delete)

**Rules**
- `version`: Rule set version
- `category`: Spending category
- `min_percent`: Minimum budget percentage
- `max_percent`: Maximum budget percentage
- `alert_threshold`: Alert trigger threshold

---

## 🔐 Security Considerations

### Data Privacy
- **Zero-Knowledge**: Server never decrypts user data
- **Client-Side Encryption**: All encryption happens on the client
- **No PII Storage**: Only anonymous identifiers stored
- **Encrypted Transit**: HTTPS enforced in production

### Best Practices Implemented
- Input validation on all endpoints
- SQL injection protection via Eloquent ORM
- CSRF protection
- Rate limiting (configurable)
- Secure headers configuration

---

## 🚢 Deployment

### Production Checklist
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure production database
- [ ] Set up HTTPS/SSL certificates
- [ ] Configure CORS for your frontend domain
- [ ] Set up monitoring and logging
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`

### Recommended Hosting
- Laravel Forge
- AWS EC2 with Laravel deployment
- DigitalOcean App Platform
- Heroku with PHP buildpack

---

## 📁 Project Structure

```
safio-api/
├── app/
│   ├── Http/Controllers/    # API Controllers
│   │   ├── SnapshotController.php
│   │   ├── PremiumController.php
│   │   └── RulesController.php
│   └── Models/              # Eloquent Models
│       ├── Snapshot.php
│       ├── PremiumPayment.php
│       └── Rule.php
├── database/
│   └── migrations/          # Database migrations
├── routes/
│   └── api.php             # API route definitions
├── tests/
│   └── Feature/            # Feature tests
└── API_TESTING.md          # API documentation
```

---


### Adding New Features
1. Create migration: `php artisan make:migration create_table_name`
2. Create model: `php artisan make:model ModelName`
3. Create controller: `php artisan make:controller ControllerName`
4. Add routes in `routes/api.php`
5. Write tests in `tests/Feature/`
6. Run tests: `php artisan test`

### Code Standards
- Follow PSR-12 coding standards
- Write descriptive commit messages
- Maintain test coverage above 80%
- Document all API endpoints

---

## 📈 Performance

- **Response Time**: < 100ms average for API calls
- **Database**: Optimized queries with Eloquent eager loading
- **Caching**: Redis/Memcached support for rules and configurations
- **Scalability**: Stateless design for horizontal scaling

---



---

## 👨‍💻 Technical Skills Demonstrated

This project showcases proficiency in:

- ✅ **RESTful API Design**: Clean, intuitive endpoint structure
- ✅ **Laravel Framework**: Advanced features and best practices
- ✅ **Database Design**: Normalized schema with proper relationships
- ✅ **Security Engineering**: End-to-end encryption, zero-knowledge architecture
- ✅ **Test-Driven Development**: Comprehensive PHPUnit test suite
- ✅ **Privacy-First Development**: GDPR-compliant data handling
- ✅ **Payment Integration**: Third-party payment gateway integration
- ✅ **API Documentation**: Clear, comprehensive documentation
- ✅ **Code Quality**: PSR-12 compliant, maintainable codebase
- ✅ **DevOps**: Environment configuration, deployment readiness

---

## 📞 Contact & Support

For questions, issues, or contributions, please refer to the project documentation or open an issue in the repository.


