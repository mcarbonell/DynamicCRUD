# 04. Advanced Features

Extend DynamicCRUD with hooks, virtual fields, and custom validation.

## Examples

### Hooks (`hooks.php`)
10 lifecycle hooks for custom logic.

**Available hooks:**
- `beforeValidate`, `afterValidate`
- `beforeSave`, `afterSave`
- `beforeCreate`, `afterCreate`
- `beforeUpdate`, `afterUpdate`
- `beforeDelete`, `afterDelete`

```php
// Auto-generate slug from title
$crud->beforeSave(function($data) {
    if (empty($data['slug'])) {
        $data['slug'] = slugify($data['title']);
    }
    return $data;
});

// Log after creation
$crud->afterCreate(function($id, $data) {
    error_log("Created: $id");
});
```

### Virtual Fields (`virtual-fields.php`)
Fields that appear in forms but aren't stored in database.

**Use cases:**
- Password confirmation
- Terms acceptance
- Captcha validation
- Calculated fields

```php
$crud->addVirtualField(new VirtualField(
    name: 'password_confirmation',
    type: 'password',
    required: true,
    validator: fn($value, $data) => $value === $data['password']
));
```

### Custom Validation (`validation.php`)
Advanced validation rules using hooks.

```php
// Email domain whitelist
$crud->afterValidate(function($data) {
    $domain = substr(strrchr($data['email'], "@"), 1);
    if (!in_array($domain, ['example.com', 'test.com'])) {
        throw new \Exception("Invalid email domain");
    }
    return $data;
});

// Password strength
$crud->afterValidate(function($data) {
    if (!preg_match('/[A-Z]/', $data['password'])) {
        throw new \Exception("Password needs uppercase");
    }
    return $data;
});
```

## Hook Execution Order

1. `beforeValidate` - Modify data before validation
2. Built-in validation runs
3. `afterValidate` - Custom validation
4. `beforeSave` - Modify data before save
5. `beforeCreate` OR `beforeUpdate` - Specific to operation
6. Database operation (INSERT/UPDATE)
7. `afterCreate` OR `afterUpdate` - After operation
8. `afterSave` - After any save

## Next Steps

- [Production Features](../05-features/) - i18n, templates, audit
- [Database Examples](../06-databases/) - MySQL and PostgreSQL specific
