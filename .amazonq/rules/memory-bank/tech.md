# Technology Stack

## Programming Languages

### PHP 8.0+
- **Minimum Version**: PHP 8.0
- **Tested Versions**: 8.0, 8.1, 8.2, 8.3, 8.4
- **Key Features Used**:
  - Named arguments
  - Union types
  - Nullsafe operator (`?->`)
  - Match expressions
  - Constructor property promotion
  - Attributes (for future use)
  - Arrow functions

### SQL
- **MySQL 5.7+** - Primary database support
- **PostgreSQL 12+** - Full support via adapter pattern

### JavaScript (ES6+)
- Client-side validation
- Dynamic UI interactions (tabs, multi-select)
- AJAX form submissions (optional)

### CSS3
- Embedded styles in FormGenerator
- Responsive design
- Accessibility features

## Required PHP Extensions

### Core Extensions
- `ext-pdo` - Database abstraction layer (required)
- `ext-fileinfo` - MIME type detection for file uploads (required)
- `ext-json` - JSON parsing for metadata (required)

### Optional Extensions
- `ext-mbstring` - Multi-byte string support (recommended for i18n)
- `ext-intl` - Internationalization support (recommended)

## Dependencies

### Production Dependencies
**None** - Zero external dependencies for production use

### Development Dependencies
- `phpunit/phpunit` - ^9.5 || ^10.0
  - Unit testing framework
  - Code coverage analysis
  - 242 tests with 90% coverage

## Build System

### Composer
- **Package Manager**: Composer 2.x
- **Autoloading**: PSR-4
  - `DynamicCRUD\` → `src/`
  - `DynamicCRUD\Tests\` → `tests/`
- **Binary**: `bin/dynamiccrud` (CLI tool)

### Package Configuration
```json
{
  "name": "dynamiccrud/dynamiccrud",
  "type": "library",
  "require": {
    "php": ">=8.0",
    "ext-pdo": "*",
    "ext-fileinfo": "*",
    "ext-json": "*"
  }
}
```

## Database Systems

### MySQL
- **Version**: 5.7+
- **Features Used**:
  - Foreign key constraints
  - JSON column comments
  - ENUM types
  - Transactions
  - Prepared statements

### PostgreSQL
- **Version**: 12+
- **Features Used**:
  - Foreign key constraints
  - JSON column comments
  - ENUM types (via CHECK constraints)
  - Transactions
  - Prepared statements
- **Adapter**: `PostgreSQLAdapter` handles dialect differences

## Development Tools

### Testing
```bash
# Run all tests
php vendor/phpunit/phpunit/phpunit

# Run specific test
php vendor/phpunit/phpunit/phpunit tests/DynamicCRUDTest.php

# Run with coverage
php vendor/phpunit/phpunit/phpunit --coverage-html coverage/
```

### CLI Tool
```bash
# Initialize project
php bin/dynamiccrud init

# List tables
php bin/dynamiccrud list:tables

# Generate metadata
php bin/dynamiccrud generate:metadata users

# Validate metadata
php bin/dynamiccrud validate:metadata users

# Clear cache
php bin/dynamiccrud clear:cache
```

### Docker Development
```bash
# Start MySQL + PostgreSQL
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f
```

**Services**:
- MySQL 8.0 on port 3306
- PostgreSQL 15 on port 5432
- phpMyAdmin on port 8080

## CI/CD Pipeline

### GitHub Actions
- **Workflow Files**: `.github/workflows/`
  - `tests.yml` - Run test suite on PHP 8.0-8.3
  - `code-quality.yml` - Code style and static analysis
  - `release.yml` - Automated releases

### Test Matrix
- PHP versions: 8.0, 8.1, 8.2, 8.3
- Databases: MySQL 8.0, PostgreSQL 15
- OS: Ubuntu latest

## Development Commands

### Installation
```bash
# Install dependencies
composer install

# Install for development
composer install --dev
```

### Testing
```bash
# Run all tests
composer test

# Run specific test suite
php vendor/phpunit/phpunit/phpunit tests/AuthenticationManagerTest.php
```

### Code Quality
```bash
# PHP syntax check
find src/ -name "*.php" -exec php -l {} \;

# Run static analysis (if configured)
composer analyze
```

### Cache Management
```bash
# Clear schema cache
php examples/clear_cache.php

# Clear template cache
rm -rf cache/templates/*
```

## Project Configuration Files

### `composer.json`
- Package metadata
- Dependencies
- Autoloading rules
- CLI binary registration

### `phpunit.xml`
- Test suite configuration
- Code coverage settings
- Bootstrap file
- Test directories

### `docker-compose.yml`
- MySQL service configuration
- PostgreSQL service configuration
- phpMyAdmin service
- Volume mappings

### `.gitignore`
- Vendor directory
- Cache files
- Upload files
- IDE configurations

## Performance Considerations

### Caching
- **Schema Cache**: File-based caching of database schema
- **Template Cache**: Compiled template caching
- **Cache Location**: `cache/` directory
- **Cache Strategy**: Pluggable via `CacheStrategy` interface

### Database Optimization
- Prepared statements (prevents SQL injection + performance)
- Connection reuse (single PDO instance)
- Lazy loading of schema metadata
- Efficient JOIN queries for relationships

### Memory Management
- Streaming file uploads (no memory buffering)
- Pagination for large datasets
- Lazy loading of related data

## Security Technologies

### Built-in Security
- **CSRF Protection**: Token-based validation
- **SQL Injection Prevention**: Prepared statements only
- **XSS Protection**: Automatic HTML escaping
- **Password Hashing**: bcrypt (PASSWORD_DEFAULT)
- **Session Management**: Secure session handling
- **Rate Limiting**: Login attempt throttling

### File Upload Security
- Real MIME type validation with `finfo`
- File size limits
- Allowed extensions whitelist
- Secure file naming

## Internationalization

### Translation System
- **Format**: JSON files in `src/I18n/locales/`
- **Languages**: English (en), Spanish (es), French (fr)
- **Detection**: URL parameter, session, browser Accept-Language
- **Fallback**: English as default

### Supported Locales
- `en` - English (default)
- `es` - Spanish (Español)
- `fr` - French (Français)
