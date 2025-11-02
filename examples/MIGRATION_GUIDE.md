# Migration Guide - Old Examples to New Structure

This guide helps you migrate from the old examples to the new organized structure.

## Old vs New Structure

### Old Examples (Root Level)
```
examples/
├── index.php              → 01-basic/index.php
├── products.php           → 03-customization/file-uploads.php
├── posts.php              → 02-relationships/foreign-keys.php
├── hooks_demo.php         → 04-advanced/hooks.php
├── virtual_fields_demo.php → 04-advanced/virtual-fields.php
├── many_to_many_demo.php  → 02-relationships/many-to-many.php
├── i18n_demo.php          → 05-features/i18n.php
├── validation_demo.php    → 04-advanced/validation.php
├── contacts.php           → 03-customization/metadata.php
└── categories.php         → (merged into other examples)
```

### New Structure (Organized)
```
examples/
├── index.html             # Visual navigation page
├── README.md              # Complete guide
├── 01-basic/              # Start here
├── 02-relationships/      # FK and M:N
├── 03-customization/      # Metadata and files
├── 04-advanced/           # Hooks, virtual fields, validation
├── 05-features/           # i18n, templates, audit
├── 06-databases/          # MySQL and PostgreSQL
├── setup/                 # Consolidated SQL scripts
└── assets/                # Shared CSS/JS
```

## What Changed

### ✅ Improvements
1. **Progressive Learning** - Examples ordered by complexity
2. **Better Documentation** - Each folder has README.md
3. **Consolidated Setup** - Single SQL script per database
4. **Visual Navigation** - index.html for easy browsing
5. **Consistent Naming** - Clear, descriptive filenames
6. **Removed Duplicates** - Merged similar examples

### 🗑️ Removed Files
- `debug_*.php` - Debug files removed
- `test_token.php` - Test file removed
- `clear_cache.php` - Moved to root if needed
- `setup_phase*.sql` - Consolidated into setup/mysql.sql

### 📦 Kept Files
- `assets/` - CSS and JS files (unchanged)
- `uploads/` - Upload directory (unchanged)

## Migration Steps

### For Users
1. **Backup old examples** (optional)
   ```bash
   cp -r examples examples_old
   ```

2. **Use new structure**
   - Start with `examples/index.html` for navigation
   - Follow progressive learning path
   - Use consolidated SQL scripts in `setup/`

3. **Update bookmarks**
   - Old: `examples/index.php`
   - New: `examples/01-basic/index.php`

### For Developers
1. **Update documentation links**
   - README.md references
   - Tutorial links
   - Blog posts

2. **Update CI/CD**
   - Test paths if automated
   - Update deployment scripts

3. **Database setup**
   - Use `setup/mysql.sql` or `setup/postgresql.sql`
   - Remove old `setup_phase*.sql` files

## Quick Reference

| Feature | Old File | New File |
|---------|----------|----------|
| Basic CRUD | `index.php` | `01-basic/index.php` |
| Foreign Keys | `posts.php` | `02-relationships/foreign-keys.php` |
| Many-to-Many | `many_to_many_demo.php` | `02-relationships/many-to-many.php` |
| Metadata | `contacts.php` | `03-customization/metadata.php` |
| File Upload | `products.php` | `03-customization/file-uploads.php` |
| Hooks | `hooks_demo.php` | `04-advanced/hooks.php` |
| Virtual Fields | `virtual_fields_demo.php` | `04-advanced/virtual-fields.php` |
| Validation | `validation_demo.php` | `04-advanced/validation.php` |
| i18n | `i18n_demo.php` | `05-features/i18n.php` |

## Benefits of New Structure

1. **Easier Learning** - Clear progression from basic to advanced
2. **Better Organization** - Related examples grouped together
3. **Improved Documentation** - README in each folder
4. **Faster Setup** - Single SQL script per database
5. **Professional** - Industry-standard project structure

## Need Help?

- Check `examples/README.md` for complete guide
- Open `examples/index.html` for visual navigation
- Each folder has its own README with details

## Rollback (if needed)

If you need the old structure:
```bash
git checkout HEAD~1 examples/
```

Or restore from backup:
```bash
rm -rf examples
mv examples_old examples
```
