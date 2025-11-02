# DynamicCRUD - Technology Stack

## Programming Languages
- **PHP 8.0+**: Core language (requires modern PHP features)
  - Uses typed properties, named arguments, match expressions
  - PSR-4 autoloading standard
  - Strict types enabled in most files

## Required PHP Extensions
- **ext-pdo**: Database connectivity (PDO)
- **ext-fileinfo**: MIME type detection for file uploads (finfo)
- **ext-json**: JSON parsing for metadata and translations

## Databases Supported
- **MySQL 5.7+**: Primary database with full feature support
- **PostgreSQL 12+**: Full support via adapter pattern
  - Auto-detects database driver from PDO connection
  - Adapter pattern allows easy extension to SQL Server, SQLite, etc.

## Dependencies (Composer)

### Production Dependencies
```json
{
  "php": ">=8.0",
  "ext-pdo": "*",
  "ext-fileinfo": "*",
  "ext-json": "*"
}
```

### Development Dependencies
```json
{
  "phpunit/phpunit": "^9.5 || ^10.0"
}
```

## Build System
- **Composer**: Dependency management and autoloading
  - Package name: `dynamiccrud/dynamiccrud`
  - Type: `library`
  - License: MIT
  - PSR-4 autoloading: `DynamicCRUD\` → `src/`

## Testing Framework
- **PHPUnit 9.5+ or 10.0+**: Unit and integration testing
  - 195 tests across 16 test files
  - 76% pass rate (149 passing, 40 failing, 6 skipped)
  - Code coverage tracking (excludes Cache directory)
  - Test namespace: `DynamicCRUD\Tests\`

## Development Commands

### Installation
```bash
# Install dependencies
composer install

# Install for production (no dev dependencies)
composer install --no-dev
```

### Testing
```bash
# Run all tests
vendor/bin/phpunit

# Run with detailed output
vendor/bin/phpunit --testdox

# Run specific test file
vendor/bin/phpunit tests/FormGeneratorTest.php

# Run with coverage (requires Xdebug)
vendor/bin/phpunit --coverage-html coverage/
```

### Windows-specific
```bash
# Run tests on Windows
run-tests.bat

# Or use vendor\bin\phpunit.bat directly
vendor\bin\phpunit.bat
```

### Cache Management
```bash
# Clear template cache
php examples/clear_cache.php

# Or manually delete cache files
rm -rf cache/templates/*
rm cache/*.cache
```

## CI/CD Pipeline (GitHub Actions)

### Workflows
1. **tests.yml**: Runs PHPUnit tests on PHP 8.0, 8.1, 8.2, 8.3
2. **code-quality.yml**: Code style and static analysis checks
3. **release.yml**: Automated releases to Packagist

### Test Environment Variables
```xml
<env name="DB_HOST" value="localhost"/>
<env name="DB_NAME" value="test"/>
<env name="DB_USER" value="root"/>
<env name="DB_PASS" value="rootpassword"/>
```

## Docker Support

### Services (docker-compose.yml)
- **MySQL 8.0**: Port 3306, database `dynamiccrud_db`
- **PostgreSQL 15**: Port 5432, database `dynamiccrud_db`
- **Adminer**: Web-based database management (port 8080)

### Docker Commands
```bash
# Start all services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f

# Access MySQL
docker-compose exec mysql mysql -u root -p

# Access PostgreSQL
docker-compose exec postgres psql -U postgres -d dynamiccrud_db
```

## File Structure Standards
- **PSR-4 Autoloading**: `DynamicCRUD\` namespace maps to `src/` directory
- **Test Namespace**: `DynamicCRUD\Tests\` maps to `tests/` directory
- **Naming Convention**: PascalCase for classes, camelCase for methods
- **File Naming**: Class name matches filename (e.g., `DynamicCRUD.php`)

## Code Quality Tools
- **PHPUnit**: Unit testing framework
- **GitHub Actions**: Automated testing and quality checks
- **Composer Scripts**: Custom commands for development tasks

## Browser Compatibility
- **Client-side JavaScript**: ES6+ features (arrow functions, const/let)
- **CSS**: Modern CSS3 (flexbox, grid, custom properties)
- **Target Browsers**: Modern browsers (Chrome, Firefox, Safari, Edge)

## Performance Optimizations
- **Schema Caching**: File-based cache for database metadata (reduces queries)
- **Template Caching**: Compiled templates cached to PHP files
- **Prepared Statements**: All database queries use PDO prepared statements
- **Lazy Loading**: Components initialized only when needed

## Security Technologies
- **CSRF Tokens**: Session-based token generation and validation
- **Prepared Statements**: PDO parameterized queries (SQL injection prevention)
- **finfo**: Real MIME type detection for file uploads (not just extension)
- **htmlspecialchars**: XSS prevention for output escaping
- **Transactions**: Database transactions with automatic rollback

## Internationalization (i18n)
- **Format**: JSON files in `src/I18n/locales/`
- **Languages**: English (en), Spanish (es), French (fr)
- **Detection**: URL parameter (?lang=), session, or browser Accept-Language header
- **Client-side**: JavaScript translations embedded in generated forms

## Template System
- **Syntax**: Blade-like (Laravel-inspired)
- **Features**: @if, @foreach, @extends, @section, @yield, @include
- **Escaping**: {{ $var }} (escaped), {!! $var !!} (raw)
- **Caching**: Compiled templates stored in `cache/templates/`

## Version Control
- **Git**: Source control with GitHub hosting
- **Branching**: Feature branches, main branch for releases
- **Tagging**: Semantic versioning (v1.0.0, v1.1.0, etc.)

## Package Distribution
- **Packagist**: Official Composer package repository
- **GitHub Releases**: Tagged releases with changelogs
- **Badges**: Tests, code quality, version, downloads, license
