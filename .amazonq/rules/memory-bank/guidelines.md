# DynamicCRUD - Development Guidelines

## Code Quality Standards

### Namespace and Autoloading
- **PSR-4 autoloading**: All classes use `DynamicCRUD\` namespace mapping to `src/` directory
- **Test namespace**: Test classes use `DynamicCRUD\Tests\` namespace mapping to `tests/` directory
- **Subnamespaces**: Organize by feature (e.g., `DynamicCRUD\Cache\`, `DynamicCRUD\Database\`, `DynamicCRUD\I18n\`)

### File Structure
- **One class per file**: Each PHP file contains exactly one class
- **File naming**: Class name matches filename exactly (e.g., `FormGenerator` class in `FormGenerator.php`)
- **Opening tag**: All PHP files start with `<?php` with no closing tag
- **Namespace declaration**: Immediately after opening tag, followed by blank line
- **Use statements**: Grouped after namespace, sorted alphabetically, followed by blank line

### Code Formatting
- **Indentation**: 4 spaces (no tabs)
- **Line length**: No strict limit, but keep readable (most lines under 120 characters)
- **Braces**: Opening brace on same line for methods/functions, closing brace on new line
- **Spacing**: Space after control structures (`if (`, `foreach (`, `while (`), no space for function calls
- **String concatenation**: Space around `.` operator when building multi-line strings

Example:
```php
<?php

namespace DynamicCRUD;

use PDO;
use DynamicCRUD\I18n\Translator;

class FormGenerator
{
    private array $schema;
    private ?Translator $translator = null;
    
    public function render(): string
    {
        $html = '<form method="POST">' . "\n";
        $html .= $this->renderFields() . "\n";
        $html .= '</form>';
        return $html;
    }
}
```

### Naming Conventions
- **Classes**: PascalCase (e.g., `FormGenerator`, `CRUDHandler`, `PostgreSQLAdapter`)
- **Methods**: camelCase (e.g., `renderForm()`, `handleSubmission()`, `getForeignKeys()`)
- **Properties**: camelCase (e.g., `$schema`, `$csrfToken`, `$uploadDir`)
- **Constants**: UPPER_SNAKE_CASE (e.g., `UPLOAD_ERR_OK`)
- **Private methods**: camelCase with descriptive names (e.g., `renderVirtualField()`, `executeHook()`)

### Type Declarations
- **Property types**: Always declare types for class properties (PHP 8.0+ typed properties)
- **Method parameters**: Always type-hint parameters
- **Return types**: Always declare return types (use `void` for no return)
- **Nullable types**: Use `?Type` syntax for nullable parameters/returns

Example:
```php
private PDO $pdo;
private string $table;
private ?Translator $translator = null;

public function __construct(PDO $pdo, string $table, ?CacheStrategy $cache = null)
{
    $this->pdo = $pdo;
    $this->table = $table;
}

public function handleSubmission(): array
{
    // Implementation
}

private function executeHook(string $event, ...$args)
{
    // Variadic parameters for flexibility
}
```

### Documentation
- **Class docblocks**: Not required unless special PHPUnit annotations needed
- **Method docblocks**: Not required for self-explanatory methods with type hints
- **Complex logic**: Add inline comments for non-obvious code
- **PHPUnit annotations**: Use docblocks for test configuration

Example:
```php
/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CRUDHandlerTest extends TestCase
{
    // Tests that modify global state need isolation
}
```

## Architectural Patterns

### Dependency Injection
- **Constructor injection**: Pass dependencies via constructor, not via setters (except optional features)
- **Optional dependencies**: Use nullable types with default `null` for optional features
- **Fluent setters**: Provide setter methods that return `$this` for optional dependencies

Example:
```php
public function __construct(PDO $pdo, string $table, ?CacheStrategy $cache = null)
{
    $this->pdo = $pdo;
    $this->table = $table;
    $this->cache = $cache;
}

public function setTranslator(Translator $translator): self
{
    $this->translator = $translator;
    return $this; // Fluent interface
}
```

### Adapter Pattern (Database Abstraction)
- **Interface first**: Define `DatabaseAdapter` interface with all required methods
- **Concrete implementations**: Create adapter classes for each database (MySQL, PostgreSQL)
- **Type normalization**: Adapters normalize database-specific types to common types
- **Quote identifiers**: Provide database-specific identifier quoting

Example:
```php
interface DatabaseAdapter
{
    public function getTableSchema(string $table): array;
    public function getForeignKeys(string $table): array;
    public function getEnumValues(string $table, string $column): array;
    public function quote(string $identifier): string;
}

class PostgreSQLAdapter implements DatabaseAdapter
{
    private function normalizeSqlType(string $pgType): string
    {
        return match($pgType) {
            'character varying', 'varchar' => 'varchar',
            'integer', 'int4' => 'int',
            'bigint', 'int8' => 'bigint',
            default => $pgType
        };
    }
}
```

### Hooks/Events System
- **Hook registration**: Provide both generic `on()` method and specific convenience methods
- **Fluent interface**: All hook registration methods return `$this`
- **Hook execution**: Execute hooks in order, passing results through chain
- **Variadic parameters**: Use `...$args` for flexible hook signatures

Example:
```php
private array $hooks = [];

public function on(string $event, callable $callback): self
{
    if (!isset($this->hooks[$event])) {
        $this->hooks[$event] = [];
    }
    $this->hooks[$event][] = $callback;
    return $this;
}

public function beforeSave(callable $callback): self
{
    return $this->on('beforeSave', $callback);
}

private function executeHook(string $event, ...$args)
{
    if (!isset($this->hooks[$event])) {
        return $args[0] ?? null;
    }
    
    $result = $args[0] ?? null;
    foreach ($this->hooks[$event] as $callback) {
        $result = $callback(...$args) ?? $result;
    }
    return $result;
}
```

### Fluent Interface Pattern
- **Method chaining**: Methods that configure objects return `$this`
- **Consistent return**: All configuration methods follow same pattern
- **Applies to**: Hook registration, virtual fields, M:N relations, audit setup

Example:
```php
$crud->addManyToMany('tags', 'post_tags', 'post_id', 'tag_id', 'tags')
     ->beforeSave(function($data) { return $data; })
     ->afterCreate(function($id, $data) { /* log */ })
     ->enableAudit(1);
```

## Security Practices

### CSRF Protection
- **Token generation**: Generate cryptographically secure tokens with `bin2hex(random_bytes(32))`
- **Session storage**: Store tokens in `$_SESSION['csrf_token']`
- **Hidden field**: Include token as hidden input in all forms
- **Validation**: Check token before processing any POST request

Example:
```php
public function generateCsrfToken(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    return $token;
}

public function validateCsrfToken(string $token): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
```

### SQL Injection Prevention
- **Prepared statements**: Always use PDO prepared statements, never string concatenation
- **Named parameters**: Use `:parameter` syntax for clarity
- **Bind values**: Explicitly bind values with correct PDO types
- **NULL handling**: Use `PDO::PARAM_NULL` for null values

Example:
```php
$sql = sprintf(
    "INSERT INTO %s (%s) VALUES (%s)",
    $this->table,
    implode(', ', $columns),
    implode(', ', $placeholders)
);

$stmt = $this->pdo->prepare($sql);

foreach ($data as $key => $value) {
    $stmt->bindValue(":{$key}", $value, $value === null ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
}

$stmt->execute();
```

### XSS Prevention
- **Output escaping**: Always use `htmlspecialchars()` for user data in HTML
- **Consistent escaping**: Escape in rendering layer (FormGenerator), not storage layer
- **Attribute escaping**: Escape data in HTML attributes too

Example:
```php
$html .= sprintf(
    '<input type="%s" name="%s" value="%s">',
    $type,
    $column['name'],
    htmlspecialchars($value)
);
```

### File Upload Security
- **Real MIME detection**: Use `finfo_file()` with `FILEINFO_MIME_TYPE`, not file extension
- **Size validation**: Check file size before processing
- **Unique filenames**: Generate unique names with `uniqid() . '_' . time()`
- **Directory permissions**: Verify upload directory is writable (0755)

Example:
```php
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedMimes)) {
    throw new \Exception("Tipo de archivo no permitido");
}

$filename = uniqid() . '_' . time() . '.' . $extension;
```

## Database Patterns

### Transaction Management
- **Begin early**: Start transaction at beginning of `handleSubmission()`
- **Commit on success**: Commit after all operations succeed
- **Rollback on error**: Catch exceptions and rollback in catch block
- **Nested operations**: Ensure M:N sync happens within same transaction

Example:
```php
public function handleSubmission(): array
{
    try {
        $this->pdo->beginTransaction();
        
        // Validation, save, hooks, M:N sync
        
        $this->pdo->commit();
        return ['success' => true, 'id' => $id];
        
    } catch (\Exception $e) {
        $this->pdo->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
```

### Schema Metadata
- **JSON in comments**: Store field metadata as JSON in column comments
- **Graceful parsing**: Use `json_decode()` with null check, default to empty array
- **Metadata keys**: Use lowercase with underscores (e.g., `display_column`, `max_size`)

Example:
```php
$metadata = [];
if (!empty($column['comment'])) {
    $decoded = json_decode($column['comment'], true);
    $metadata = is_array($decoded) ? $decoded : [];
}
```

### Foreign Key Handling
- **Auto-detection**: Query information_schema for foreign key relationships
- **Display column**: Support custom display column via metadata (default: 'name')
- **Dropdown generation**: Automatically create `<select>` for foreign key fields
- **Nullable handling**: Add empty option for nullable foreign keys

Example:
```php
private function renderForeignKeySelect(array $column, $value): string
{
    $fk = $this->schema['foreign_keys'][$column['name']];
    $displayColumn = $column['metadata']['display_column'] ?? 'name';
    
    $options = $this->getForeignKeyOptions($fk['table'], $fk['column'], $displayColumn);
    
    // Generate <select> with options
}
```

## Testing Practices

### Test Structure
- **PHPUnit annotations**: Use docblock annotations for test isolation
- **setUp/tearDown**: Initialize PDO and cleanup test data in lifecycle methods
- **Separate processes**: Use `@runTestsInSeparateProcesses` for tests modifying global state
- **Test naming**: Use descriptive method names starting with `test`

Example:
```php
/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CRUDHandlerTest extends TestCase
{
    private PDO $pdo;
    private CRUDHandler $handler;

    protected function setUp(): void
    {
        $this->pdo = new PDO(/* connection */);
        $this->handler = new CRUDHandler($this->pdo, 'users');
        $this->cleanupTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
    }
}
```

### Test Data Management
- **Unique identifiers**: Use unique email/name patterns for test data (e.g., `test_hook1@example.com`)
- **Cleanup methods**: Create helper methods to delete test data
- **Helper methods**: Create private methods for common operations (createTestUser, findByEmail)

Example:
```php
private function cleanupTestData(): void
{
    $this->pdo->exec("DELETE FROM users WHERE email LIKE 'test_%@example.com'");
}

private function createTestUser(string $email): int
{
    $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
    $stmt->execute(['name' => 'Test User', 'email' => $email, 'password' => 'test123']);
    return (int) $this->pdo->lastInsertId();
}
```

### Assertion Patterns
- **Type assertions**: Use `assertInstanceOf()` for object type checks
- **Array assertions**: Use `assertArrayHasKey()` for array structure validation
- **Boolean assertions**: Use `assertTrue()`, `assertFalse()` for boolean checks
- **Comparison assertions**: Use `assertEquals()`, `assertGreaterThan()` for value checks

## HTML Generation Patterns

### String Building
- **Concatenation**: Build HTML strings with `.=` operator
- **Line breaks**: Add `"\n"` for readable output (helps debugging)
- **sprintf**: Use `sprintf()` for complex attribute formatting
- **Indentation**: Add spaces for nested elements (2 spaces per level)

Example:
```php
$html = '<div class="form-group">' . "\n";
$html .= '  <label for="' . $column['name'] . '">' . htmlspecialchars($label) . '</label>' . "\n";
$html .= '  ' . $this->renderInput($column, $value) . "\n";
$html .= '</div>';
```

### Accessibility
- **ARIA attributes**: Add `aria-required="true"` for required fields
- **Labels**: Always associate labels with inputs using `for` attribute
- **Tooltips**: Use `role="tooltip"` for tooltip content
- **Keyboard navigation**: Add `tabindex="0"` for focusable non-interactive elements

Example:
```php
if (!$column['is_nullable']) {
    $attrs[] = 'required';
    $attrs[] = 'aria-required="true"';
}
```

## Error Handling

### Exception Usage
- **Throw exceptions**: Use exceptions for error conditions in library code
- **Descriptive messages**: Provide clear, actionable error messages in Spanish (or i18n)
- **Catch at boundaries**: Catch exceptions at API boundaries (handleSubmission)
- **Return error arrays**: Return structured error arrays from public methods

Example:
```php
if ($file['size'] > $maxSize) {
    throw new \Exception("El archivo excede el tamaño máximo permitido de " . $this->formatBytes($maxSize));
}

// At boundary:
try {
    // Operations
    return ['success' => true, 'id' => $id];
} catch (\Exception $e) {
    return ['success' => false, 'error' => $e->getMessage()];
}
```

### Validation Errors
- **Structured errors**: Return associative array with field names as keys
- **Multiple errors**: Support multiple validation errors per submission
- **Translated messages**: Use Translator for error messages when available

Example:
```php
if (!$validator->validate($data)) {
    $this->pdo->rollBack();
    return ['success' => false, 'errors' => $validator->getErrors()];
}
```

## Internationalization (i18n)

### Translation Keys
- **Dot notation**: Use dot notation for namespacing (e.g., `form.submit`, `validation.required`)
- **Placeholders**: Use `:placeholder` syntax for dynamic values
- **Fallback**: Provide fallback text when translator not available

Example:
```php
$submitLabel = 'Guardar'; // Fallback
if ($this->handler && $this->handler->getTranslator()) {
    $submitLabel = $this->handler->getTranslator()->t('form.submit');
}
```

### Client-side Translations
- **Embed in HTML**: Add translations as JavaScript object in `<script>` tag
- **Window global**: Use `window.DynamicCRUDTranslations` for client access
- **JSON encoding**: Use `json_encode()` to safely embed translations

Example:
```php
$translations = [
    'required' => $t->t('validation.required', ['field' => '']),
    'email' => $t->t('validation.email', ['field' => '']),
];
$html .= '<script>window.DynamicCRUDTranslations = ' . json_encode($translations) . ';</script>';
```

## Performance Optimization

### Caching Strategy
- **Schema caching**: Cache database schema metadata to reduce queries
- **Template caching**: Compile templates to PHP files for faster rendering
- **Cache keys**: Use table name or template hash as cache key
- **TTL support**: Support time-to-live for cache invalidation

### Lazy Initialization
- **Optional dependencies**: Initialize optional features only when used
- **Null checks**: Check for null before using optional dependencies
- **Late binding**: Set dependencies via setters after construction

Example:
```php
private ?Translator $translator = null;

public function setTranslator(Translator $translator): self
{
    $this->translator = $translator;
    return $this;
}

// Use with null check:
if ($this->translator) {
    $label = $this->translator->t('form.submit');
}
```

## Match Expressions (PHP 8.0+)

### Use match over switch
- **Return values**: Use `match` when returning values based on condition
- **Type mapping**: Perfect for mapping database types to input types
- **Error messages**: Good for mapping error codes to messages

Example:
```php
return match($column['sql_type']) {
    'int', 'bigint', 'smallint', 'tinyint' => 'number',
    'date' => 'date',
    'datetime', 'timestamp' => 'datetime-local',
    'text', 'longtext', 'mediumtext' => 'textarea',
    default => 'text'
};
```

## Common Code Idioms

### Array Filtering and Mapping
```php
// Filter columns (exclude primary key)
$allowedColumns = array_map(
    fn($col) => $col['name'],
    array_filter($this->schema['columns'], fn($col) => !$col['is_primary'])
);

// Build placeholders for SQL
$placeholders = array_map(fn($col) => ":{$col}", $columns);
```

### Conditional HTML Attributes
```php
// Add attribute only if condition is true
$requiredAttr = (!$column['is_nullable'] && !$value) ? ' required' : '';
$selected = $value == $option['value'] ? ' selected' : '';
```

### Null Coalescing
```php
// Provide defaults for optional values
$label = $column['metadata']['label'] ?? ucfirst($column['name']);
$value = $this->data[$column['name']] ?? $column['default_value'] ?? '';
```
