# Refactoring Patterns

This document describes the refactoring patterns applied in v3.4.0 and recommended for future development.

## Overview

DynamicCRUD v3.4.0 introduced significant code refactoring in FormGenerator and ListGenerator classes, focusing on:
- **Components Integration** - Using UI Components library for consistent design
- **Method Extraction** - Breaking down large methods into smaller, focused ones
- **Code Reduction** - Eliminating duplication and simplifying logic
- **Better Organization** - Clear separation of concerns

## Pattern 1: Components Integration

### Before (v3.3)
```php
// Inline HTML generation
$html = '<button type="submit">Guardar</button>';
$html .= '<table class="list-table">';
// ... more inline HTML
```

### After (v3.4)
```php
use DynamicCRUD\UI\Components;

// Use Components library
$html = Components::button('Guardar', 'primary', ['type' => 'submit']);
$html = Components::table($headers, $rows, ['striped' => true]);
```

**Benefits:**
- Consistent styling across the application
- Easier to maintain and update
- Automatic XSS protection
- Responsive design built-in

## Pattern 2: Method Extraction

### Before (v3.3)
```php
public function render(): string
{
    $html = '';
    
    // Theme rendering
    if ($this->themeManager) {
        $html .= $this->themeManager->renderCSSVariables();
        $html .= $this->themeManager->renderBranding();
    }
    
    // Styles
    $html .= $this->renderStyles();
    
    // Form opening
    $enctype = $this->hasFileFields() ? ' enctype="multipart/form-data"' : '';
    $html .= '<form method="POST"' . $enctype . '>';
    $html .= $this->renderCsrfField();
    
    // ... 50+ more lines
    
    return $html;
}
```

### After (v3.4)
```php
public function render(): string
{
    $html = $this->renderTheme();
    $html .= $this->renderStyles();
    $html .= $this->renderAssets();
    $html .= $this->renderFormOpen();
    $html .= $this->renderFormFields();
    $html .= $this->renderSubmitButton();
    $html .= '</form>';
    $html .= $this->renderWorkflowButtons();
    
    return $html;
}

private function renderTheme(): string { /* ... */ }
private function renderFormOpen(): string { /* ... */ }
private function renderFormFields(): string { /* ... */ }
private function renderSubmitButton(): string { /* ... */ }
```

**Benefits:**
- Main method is now 15 lines instead of 70
- Each method has a single responsibility
- Easier to test individual components
- Better code readability

## Pattern 3: CSS Variables for Theming

### Before (v3.3)
```php
$css = 'button { background: #667eea; }';
```

### After (v3.4)
```php
$css = 'button { background: var(--primary-color, #667eea); }';
```

**Benefits:**
- Dynamic theming support
- Easy color customization
- Consistent with Components library
- Fallback values for compatibility

## Pattern 4: Eliminating Duplication

### Before (v3.3)
```php
public function render(): string
{
    // ... 70 lines of code
}

private function renderTabbedForm(): string
{
    // ... 60 lines of duplicated code
}
```

### After (v3.4)
```php
public function render(): string
{
    if ($this->tableMetadata && $this->tableMetadata->getFormLayout() === 'tabs') {
        return $html . $this->renderTabbedForm();
    }
    
    $html .= $this->renderFormOpen();
    $html .= $this->renderFormFields();
    // ...
}

private function renderTabbedForm(): string
{
    // Reuses renderFormOpen(), renderFormFields(), etc.
    $html = $this->renderFormOpen();
    $html .= Components::tabs($tabsData, $tabsContent);
    // ...
}
```

**Benefits:**
- No code duplication
- Single source of truth
- Easier to maintain
- Consistent behavior

## Pattern 5: Simplified Conditionals

### Before (v3.3)
```php
if ($this->tableMetadata?->hasCardView()) {
    $html .= $this->renderCards($data['records']);
} else {
    $html .= $this->renderTable($data['records']);
}
```

### After (v3.4)
```php
private function renderContent(array $records): string
{
    if ($this->tableMetadata?->hasCardView()) {
        return $this->renderCards($records);
    }
    return $this->renderTableWithComponents($records);
}
```

**Benefits:**
- Cleaner code
- Early returns reduce nesting
- Easier to understand logic

## Pattern 6: Array Transformations

### Before (v3.3)
```php
$headers = [];
foreach ($columns as $col) {
    $headers[] = ucfirst(str_replace('_', ' ', $col));
}
```

### After (v3.4)
```php
$headers = array_map(fn($col) => ucfirst(str_replace('_', ' ', $col)), $columns);
```

**Benefits:**
- More concise
- Functional programming style
- Easier to read

## Pattern 7: Inline Styles for Components

### Before (v3.3)
```php
$html = '<a href="?id=' . $id . '" class="action-edit">Editar</a>';
// Requires CSS class definition elsewhere
```

### After (v3.4)
```php
$html = sprintf(
    '<a href="?id=%s" style="color: #667eea; text-decoration: none;">Editar</a>',
    $id
);
```

**Benefits:**
- Self-contained styling
- No external CSS dependencies
- Easier to customize per instance

## Applying These Patterns

### When to Extract Methods

Extract a method when:
1. A block of code has a clear, single purpose
2. The code is repeated in multiple places
3. The method is getting too long (>50 lines)
4. You need to test a specific piece of logic

### When to Use Components

Use Components library when:
1. Rendering UI elements (buttons, tables, alerts)
2. You need consistent styling
3. You want responsive design
4. You need accessibility features

### When to Use CSS Variables

Use CSS variables when:
1. Colors might change based on theme
2. You want to support customization
3. You need fallback values
4. You're integrating with ThemeManager

## Code Review Checklist

Before committing refactored code:

- [ ] All tests pass
- [ ] No breaking changes
- [ ] Methods have single responsibility
- [ ] Code duplication eliminated
- [ ] Components used where appropriate
- [ ] CSS variables for themeable colors
- [ ] Inline documentation updated
- [ ] Performance not degraded

## Examples

See these files for refactoring examples:
- `src/FormGenerator.php` - Complete refactoring with Components
- `src/ListGenerator.php` - Table and pagination with Components
- `src/Admin/AdminPanel.php` - Components integration

## Future Refactoring Candidates

Classes that could benefit from similar refactoring:
1. **CRUDHandler.php** - Extract transaction logic, validation
2. **SchemaAnalyzer.php** - Simplify metadata parsing
3. **ValidationEngine.php** - Extract validation rules
4. **FileUploadHandler.php** - Simplify upload logic

## Resources

- [Components Documentation](UI_COMPONENTS.md)
- [Best Practices](BEST_PRACTICES.md)
- [Contributing Guidelines](../CONTRIBUTING.md)

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
