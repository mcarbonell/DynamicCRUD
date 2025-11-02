# 03. Customization

Control field behavior and handle file uploads.

## Examples

### Metadata (`metadata.php`)
Customize fields using JSON in column comments.

**16+ options available:**
- Input types (email, url, tel, color, date, etc.)
- Labels and placeholders
- Validation (min, max, pattern, minlength)
- Tooltips and help text
- Read-only and hidden fields

```sql
ALTER TABLE contacts 
MODIFY COLUMN email VARCHAR(255) 
COMMENT '{"type": "email", "label": "Email Address", "tooltip": "We never share"}';
```

### Advanced Inputs (`advanced-inputs.php`)
HTML5 input types demonstration.

**Input types:**
- color, tel, password, search
- time, week, month, range
- All with metadata customization

### File Uploads (`file-uploads.php`)
Secure file upload handling.

**Security features:**
- Real MIME type detection (finfo)
- File size validation
- Unique filename generation
- Allowed types whitelist

```sql
ALTER TABLE products 
MODIFY COLUMN image VARCHAR(255) 
COMMENT '{"type": "file", "accept": "image/*", "max_size": 2097152}';
```

## Metadata Options Reference

| Option | Type | Description |
|--------|------|-------------|
| `type` | string | Input type (email, url, tel, color, date, file, etc.) |
| `label` | string | Field label text |
| `placeholder` | string | Placeholder text |
| `tooltip` | string | Help text shown as tooltip |
| `min` | int | Minimum value (numbers) |
| `max` | int | Maximum value (numbers) |
| `minlength` | int | Minimum length (text) |
| `pattern` | string | Regex validation pattern |
| `step` | string | Step increment (numbers) |
| `readonly` | bool | Make field read-only |
| `hidden` | bool | Hide field from form |
| `autocomplete` | string | Autocomplete hint |
| `accept` | string | Allowed file types (file inputs) |
| `max_size` | int | Max file size in bytes (file inputs) |
| `display_column` | string | Column to display for foreign keys |

## Next Steps

- [Advanced Features](../04-advanced/) - Hooks and virtual fields
- [Production Features](../05-features/) - i18n, templates, audit
