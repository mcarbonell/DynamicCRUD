# Validation Rules Examples

Advanced validation and business rules configured via table metadata.

## 🎯 Features Demonstrated

### 1. Unique Together
**File:** `unique-together.php`

Validates that a combination of fields is unique across the table.

```json
"validation_rules": {
    "unique_together": [
        ["sku", "category"]
    ]
}
```

**Use Case:** Prevent duplicate SKUs within the same category.

---

### 2. Required If
**File:** `required-if.php`

Makes a field required based on another field's value.

```json
"validation_rules": {
    "required_if": {
        "min_stock": {"status": "active"}
    }
}
```

**Use Case:** Active products must have a minimum stock defined.

---

### 3. Conditional Validation
**File:** `conditional.php`

Applies min/max constraints conditionally.

```json
"validation_rules": {
    "conditional": {
        "discount": {
            "condition": "price > 100",
            "max": 50
        }
    }
}
```

**Use Case:** Expensive products have a maximum discount limit.

---

### 4. Business Rules
**File:** `business-rules.php`

Enforces business logic constraints.

```json
"business_rules": {
    "max_records_per_user": 5,
    "require_approval": true,
    "approval_field": "approved_at",
    "owner_field": "user_id"
}
```

**Use Cases:**
- Limit records per user
- Require approval workflow
- Track approval status

---

## 📋 Setup

1. **Create database:**
```bash
mysql -u root -p < setup.sql
```

2. **Run examples:**
```
http://localhost/dynamicCRUD/examples/10-validation-rules/unique-together.php
http://localhost/dynamicCRUD/examples/10-validation-rules/required-if.php
http://localhost/dynamicCRUD/examples/10-validation-rules/conditional.php
http://localhost/dynamicCRUD/examples/10-validation-rules/business-rules.php
```

---

## 🔧 Configuration Reference

### Validation Rules

#### unique_together
```json
"unique_together": [
    ["field1", "field2"],
    ["field3", "field4", "field5"]
]
```

#### required_if
```json
"required_if": {
    "field_name": {
        "condition_field": "condition_value"
    }
}
```

#### conditional
```json
"conditional": {
    "field_name": {
        "condition": "other_field > 100",
        "min": 0,
        "max": 50
    }
}
```

### Business Rules

```json
"business_rules": {
    "max_records_per_user": 100,
    "owner_field": "user_id",
    "require_approval": true,
    "approval_field": "approved_at",
    "approval_roles": ["admin", "supervisor"]
}
```

---

## 🧪 Testing Scenarios

### Unique Together
1. Create product: SKU="TEST-001", Category="electronics" ✅
2. Try to create another with same SKU+Category ❌ Error
3. Create with same SKU but different category ✅

### Required If
1. Create product with status="draft", no min_stock ✅
2. Create product with status="active", no min_stock ❌ Error
3. Create product with status="active", min_stock=10 ✅

### Conditional
1. Create product: price=50, discount=60 ✅ (condition not met)
2. Create product: price=150, discount=60 ❌ Error (exceeds max)
3. Create product: price=150, discount=40 ✅

### Business Rules
1. Create 5 subscriptions for user_id=1 ✅
2. Try to create 6th subscription ❌ Error (limit reached)
3. Check approved_at is NULL (pending approval) ✅

---

## 💡 Tips

- **Combine rules:** Use multiple validation rules together
- **Error messages:** Customize messages via i18n
- **Performance:** Rules are validated after basic validation
- **Transactions:** All validations run inside a transaction

---

## 🔗 Related Documentation

- [Table Metadata Guide](../../docs/TABLE_METADATA.md)
- [Validation Engine](../../docs/CUSTOMIZATION.md)
- [RBAC Guide](../../docs/RBAC.md)
