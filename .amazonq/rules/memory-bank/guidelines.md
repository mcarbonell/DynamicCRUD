# Development Guidelines

## Code Quality Standards

### PHP Version and Features
- **Target PHP 8.0+** - Use modern PHP features consistently
- **Named arguments** - Used in constructors and method calls for clarity
- **Arrow functions** - Preferred for simple callbacks: `fn($col) => $col['name']`
- **Match expressions** - Used for type mapping (e.g., SQL type to input type)
- **Nullsafe operator** - Used for optional chaining: `$this->translator?->t('key')`
- **Type declarations** - All parameters and return types must be typed
- **Property types** - All class properties must have type declarations

### Naming Conventions
- **Classes**: PascalCase (e.g., `FormGenerator`, `CRUDHandler`, `AuthenticationManager`)
- **Methods**: camelCase (e.g., `renderForm`, `handleSubmission`, `getCurrentUser`)
- **Properties**: camelCase with visibility prefix (e.g., `private PDO $pdo`, `private array $schema`)
- **Constants**: UPPER_SNAKE_CASE (when used)
- **Database tables**: snake_case (e.g., `users`, `post_tags`, `password_resets`)
- **Database columns**: snake_case (e.g., `user_id`, `created_at`, `deleted_at`)

### File Organization
- **One class per file** - File name matches class name exactly
- **Namespace structure** - Mirrors directory structure: `DynamicCRUD\Security\AuthenticationManager`
- **Imports at top** - All `use` statements grouped at file start
- **No closing PHP tag** - Files end without `?>`

### Code Formatting
- **Indentation**: 4 spaces (no tabs)
- **Line length**: Aim for 120 characters max, break long lines logically
- **Braces**: Opening brace on same line for methods/classes, new line for control structures
- **Spacing**: Space after keywords (`if (`, `foreach (`, `function (`), no space for function calls
- **String concatenation**: Use `.` with spaces: `$html .= '<div>' . "\n";`
- **Array syntax**: Short array syntax `[]` instead of `array()`

### Documentation
- **PHPDoc blocks** - Not heavily used in this codebase (minimal approach)
- **Inline comments** - Spanish comments in code (e.g., `// Renderizar campos virtuales`)
- **README files** - English for public documentation, Spanish for internal notes
- **Method names** - Self-documenting, clear intent without excessive comments

## Architectural Patterns

### Dependency Injection
```php
// Constructor injection is standard
public function __construct(PDO $pdo, string $table, ?CacheStrategy $cache = null)
{
    $this->pdo = $pdo;
    $this->table = $table;
    $this->cache = $cache;
}
```

### Fluent Interface Pattern
```php
// All configuration methods return $this for chaining
public function setTranslator(Translator $translator): self
{
    $this->translator = $translator;
    return $this;
}

// Usage:
$crud->addHook('beforeSave', $callback)
     ->enableAudit($userId)
     ->setLocale('es');
```

### Facade Pattern
```php
// DynamicCRUD acts as facade, delegating to specialized classes
public function renderForm(?int $id = null): string
{
    // Delegates to FormGenerator
    $generator = new FormGenerator($this->schema, $data, $csrfToken, $this->pdo, $this->handler);
    return $generator->render();
}
```

### Hook/Event System
```php
// Hooks are stored in array and executed in sequence
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

### Adapter Pattern
```php
// DatabaseAdapter abstracts MySQL/PostgreSQL differences
interface DatabaseAdapter
{
    public function getTableSchema(string $table): array;
    public function getForeignKeys(string $table): array;
}
```

## Common Implementation Patterns

### HTML Generation Pattern
```php
// Build HTML strings with concatenation, always escape output
$html = '<div class="form-group">' . "\n";
$html .= sprintf('  <label for="%s">%s</label>', $name, htmlspecialchars($label)) . "\n";
$html .= sprintf('  <input type="%s" name="%s" value="%s">', $type, $name, htmlspecialchars($value)) . "\n";
$html .= '</div>';
return $html;
```

### Database Query Pattern
```php
// Always use prepared statements with named parameters
$sql = sprintf("SELECT * FROM %s WHERE %s = :id", $this->table, $pk);
$stmt = $this->pdo->prepare($sql);
$stmt->execute(['id' => $id]);
return $stmt->fetch(PDO::FETCH_ASSOC);
```

### Transaction Pattern
```php
// Wrap operations in try-catch with rollback
try {
    $this->pdo->beginTransaction();
    
    // Execute hooks
    $data = $this->executeHook('beforeSave', $data);
    
    // Perform operations
    $id = $this->save($data);
    
    // Execute more hooks
    $this->executeHook('afterSave', $id, $data);
    
    $this->pdo->commit();
    return ['success' => true, 'id' => $id];
    
} catch (\Exception $e) {
    $this->pdo->rollBack();
    return ['success' => false, 'error' => $e->getMessage()];
}
```

### Metadata Parsing Pattern
```php
// Parse JSON from database comments, handle errors gracefully
$metadata = [];
if (!empty($column['COLUMN_COMMENT'])) {
    $decoded = json_decode($column['COLUMN_COMMENT'], true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $metadata = $decoded;
    }
}
```

### Null Coalescing Pattern
```php
// Extensive use of null coalescing for defaults
$label = $column['metadata']['label'] ?? ucfirst($column['name']);
$type = $column['metadata']['type'] ?? 'text';
$tooltip = $column['metadata']['tooltip'] ?? null;
```

### Session Management Pattern
```php
// Always check session status before starting
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Use @ to suppress warnings if session already started
```

### Array Mapping Pattern
```php
// Use array_map with arrow functions for transformations
$columns = array_map(fn($col) => $col['name'], $this->schema['columns']);
$placeholders = array_map(fn($col) => ":{$col}", $columns);
$sets = array_map(fn($col) => "{$col} = :{$col}", array_keys($data));
```

## Security Practices

### Input Sanitization
```php
// Always sanitize user input through SecurityModule
$data = $this->security->sanitizeInput($_POST, $allowedColumns, $this->schema);
```

### Output Escaping
```php
// Always escape HTML output with htmlspecialchars()
echo sprintf('<div>%s</div>', htmlspecialchars($userInput));
```

### CSRF Protection
```php
// Generate token on form render
$csrfToken = $this->security->generateCsrfToken();

// Validate on submission
if (!$this->security->validateCsrfToken($csrfToken)) {
    return ['success' => false, 'error' => 'Token CSRF inválido'];
}
```

### Password Hashing
```php
// Always use PASSWORD_DEFAULT for bcrypt
$data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

// Verify with password_verify()
if (!password_verify($password, $user['password'])) {
    return ['success' => false, 'error' => 'Invalid credentials'];
}
```

### SQL Injection Prevention
```php
// NEVER concatenate user input into SQL
// ALWAYS use prepared statements with bound parameters
$stmt = $this->pdo->prepare($sql);
$stmt->bindValue(':key', $value, $value === null ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
$stmt->execute();
```

## Testing Standards

### Test Structure
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
        // Initialize test database connection
        // Create handler instance
        // Clean up test data
    }
    
    protected function tearDown(): void
    {
        // Clean up test data after each test
    }
}
```

### Test Naming
- **Pattern**: `test{MethodName}` or `test{Behavior}`
- **Examples**: `testCreateRecord`, `testBeforeValidateHook`, `testInvalidCsrfToken`
- **Descriptive**: Test name should clearly indicate what is being tested

### Test Data Management
```php
// Always clean up test data in setUp() and tearDown()
private function cleanupTestData(): void
{
    $this->pdo->exec("DELETE FROM users WHERE email LIKE 'test_%@example.com'");
}

// Use consistent test data patterns
private function createTestUser(string $email): int
{
    $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
    $stmt->execute(['name' => 'Test User', 'email' => $email, 'password' => 'test12345']);
    return (int) $this->pdo->lastInsertId();
}
```

### Assertions
```php
// Use specific assertions for clarity
$this->assertTrue($result['success']);
$this->assertArrayHasKey('id', $result);
$this->assertEquals('JOHN', $record['name']);
$this->assertInstanceOf(CRUDHandler::class, $result);
$this->assertStringContainsString('CSRF', $result['error']);
```

## Internationalization Patterns

### Translation Keys
```php
// Use dot notation for hierarchical keys
$this->translator->t('form.submit');
$this->translator->t('validation.required');
$this->translator->t('error.csrf_invalid');
$this->translator->t('m2n.select_all');
```

### Parameter Substitution
```php
// Use {field} format for parameters (NOT :field)
$this->translator->t('validation.required', ['field' => 'Email']);
// Translation: "El campo {field} es obligatorio"
```

### Fallback Strategy
```php
// Always provide fallback for missing translations
$submitLabel = 'Guardar';
if ($this->handler && $this->handler->getTranslator()) {
    $submitLabel = $this->handler->getTranslator()->t('form.submit');
}
```

## Database Conventions

### Table Metadata
```php
// Store configuration in table COMMENT as JSON
ALTER TABLE users COMMENT = '{
    "display_name": "Users",
    "icon": "👥",
    "permissions": {
        "create": ["guest"],
        "read": ["owner", "admin"],
        "update": ["owner", "admin"],
        "delete": ["admin"]
    }
}';
```

### Column Metadata
```php
// Store field configuration in column COMMENT as JSON
ALTER TABLE users 
MODIFY COLUMN email VARCHAR(255) 
COMMENT '{"type": "email", "label": "Email Address", "tooltip": "Required field"}';
```

### Soft Deletes
```php
// Use deleted_at column (nullable timestamp)
// NULL = active record, timestamp = soft deleted
if ($this->tableMetadata && $this->tableMetadata->hasSoftDeletes()) {
    $column = $this->tableMetadata->getSoftDeleteColumn();
    $sql .= sprintf(" AND %s IS NULL", $column);
}
```

### Timestamps
```php
// Use created_at and updated_at columns
if ($this->tableMetadata->hasTimestamps()) {
    $timestamps = $this->tableMetadata->getTimestampFields();
    if (!$isUpdate && isset($timestamps['created_at'])) {
        $data[$timestamps['created_at']] = date('Y-m-d H:i:s');
    }
    if (isset($timestamps['updated_at'])) {
        $data[$timestamps['updated_at']] = date('Y-m-d H:i:s');
    }
}
```

## Error Handling

### Return Array Pattern
```php
// Always return consistent array structure
return ['success' => true, 'id' => $id];
return ['success' => false, 'error' => 'Error message'];
return ['success' => false, 'errors' => ['field' => 'Error']];
```

### Exception Handling
```php
// Catch exceptions and convert to error arrays
try {
    // Operations
    return ['success' => true, 'id' => $id];
} catch (\Exception $e) {
    return ['success' => false, 'error' => $e->getMessage()];
}
```

### Validation Errors
```php
// Return errors array for field-specific validation failures
if (!$validator->validate($data)) {
    return ['success' => false, 'errors' => $validator->getErrors()];
}
```

## Performance Considerations

### Lazy Loading
```php
// Only instantiate when needed
private ?Translator $translator = null;

public function getTranslator(): ?Translator
{
    return $this->translator;
}
```

### Caching Strategy
```php
// Use cache for expensive operations (schema analysis)
$this->analyzer = new SchemaAnalyzer($pdo, $cache);
$this->schema = $this->analyzer->getTableSchema($table);
```

### Query Optimization
```php
// Always add LIMIT 1 for single record queries
$sql = sprintf("SELECT * FROM %s WHERE %s = :id LIMIT 1", $this->table, $pk);
```

## Code Style Preferences

### String Concatenation
```php
// Use .= for building strings, add \n for readability
$html = '<form>' . "\n";
$html .= '  <input type="text">' . "\n";
$html .= '</form>';
```

### Conditional Assignment
```php
// Use ternary for simple conditionals
$selected = $value == $option['value'] ? ' selected' : '';
$active = $index === 0 ? ' active' : '';
```

### Sprintf for HTML
```php
// Use sprintf for complex HTML with multiple variables
$html .= sprintf(
    '<input type="%s" name="%s" value="%s">',
    $type,
    $name,
    htmlspecialchars($value)
);
```

### Array Filtering
```php
// Use array_filter with arrow functions
$allowedColumns = array_map(
    fn($col) => $col['name'],
    array_filter($this->schema['columns'], fn($col) => !$col['is_primary'])
);
```

## Project-Specific Idioms

### FormGenerator Instantiation
```php
// FormGenerator is instantiated dynamically in renderForm(), not stored as property
$generator = new FormGenerator($this->schema, $data, $csrfToken, $this->pdo, $this->handler);
$generator->setTranslator($this->translator);
return $generator->render();
```

### Handler Delegation
```php
// DynamicCRUD delegates to CRUDHandler for operations
public function delete(int $id): bool
{
    return $this->handler->delete($id);
}
```

### Metadata Access
```php
// TableMetadata is instantiated in constructor and accessed via getter
$this->tableMetadata = new TableMetadata($pdo, $table);

public function getTableMetadata(): TableMetadata
{
    return $this->tableMetadata;
}
```

### Permission Checks
```php
// Permission checks integrated into CRUD operations
if ($this->permissionManager) {
    if (!$this->permissionManager->canCreate()) {
        return ['success' => false, 'error' => 'Permission denied'];
    }
}
```
