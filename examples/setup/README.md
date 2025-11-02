# Database Setup

SQL scripts to create all tables needed for DynamicCRUD examples.

## Quick Start

### MySQL
```bash
mysql -u root -p test < mysql.sql
```

### PostgreSQL
```bash
psql -U postgres -d test < postgresql.sql
```

## What's Created

### Tables
- **users** - Basic CRUD, virtual fields, validation, i18n, audit
- **categories** - Foreign key reference table
- **posts** - Foreign keys, hooks, M:N relationships
- **tags** - M:N relationship with posts
- **post_tags** - Pivot table for M:N
- **products** - File uploads example
- **contacts** - Metadata customization
- **advanced_inputs** - HTML5 input types (color, tel, time, week, month, range)
- **audit_log** - Audit logging

### Metadata Features
All tables include extensive JSON metadata in column comments:
- Custom labels and placeholders
- Input type specifications (email, url, tel, color, etc.)
- Validation rules (min, max, minlength, pattern)
- Tooltips and help text
- Autocomplete hints
- Display columns for foreign keys

### Sample Data
- 3 categories
- 5 tags
- 2 users
- 2 posts with tags
- 2 products

## Database Configuration

Default credentials used in examples:
- **Host:** localhost
- **Database:** test
- **User:** root
- **Password:** rootpassword

Change these in each example file if your setup differs.

## Docker Setup

If you prefer Docker:

```bash
# MySQL
docker run -d --name dynamiccrud-mysql \
  -e MYSQL_ROOT_PASSWORD=rootpassword \
  -e MYSQL_DATABASE=test \
  -p 3306:3306 \
  mysql:8.0

# PostgreSQL
docker run -d --name dynamiccrud-postgres \
  -e POSTGRES_PASSWORD=rootpassword \
  -e POSTGRES_DB=test \
  -p 5432:5432 \
  postgres:15

# Load data
docker exec -i dynamiccrud-mysql mysql -uroot -prootpassword test < mysql.sql
docker exec -i dynamiccrud-postgres psql -U postgres -d test < postgresql.sql
```

## Verify Setup

```sql
-- Check tables
SHOW TABLES;  -- MySQL
\dt           -- PostgreSQL

-- Check sample data
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM posts;
SELECT COUNT(*) FROM categories;
```

You should see:
- 2 users
- 2 posts
- 3 categories
- 5 tags
- 2 products
