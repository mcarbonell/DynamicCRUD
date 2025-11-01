<?php

namespace DynamicCRUD;

use PDO;
use DynamicCRUD\Cache\CacheStrategy;

class CRUDHandler
{
    private PDO $pdo;
    private string $table;
    private SchemaAnalyzer $analyzer;
    private SecurityModule $security;
    private FileUploadHandler $fileHandler;
    private array $schema;
    private array $hooks = [];

    public function __construct(PDO $pdo, string $table, ?CacheStrategy $cache = null, ?string $uploadDir = null)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->analyzer = new SchemaAnalyzer($pdo, $cache);
        $this->security = new SecurityModule();
        
        if ($uploadDir === null) {
            $uploadDir = __DIR__ . '/../examples/uploads';
        }
        
        $this->fileHandler = new FileUploadHandler($uploadDir);
        $this->schema = $this->analyzer->getTableSchema($table);
    }

    public function renderForm(?int $id = null): string
    {
        $data = [];
        
        if ($id !== null) {
            $data = $this->findById($id);
        }
        
        $csrfToken = $this->security->generateCsrfToken();
        $generator = new FormGenerator($this->schema, $data, $csrfToken, $this->pdo);
        
        return $generator->render();
    }

    public function handleSubmission(): array
    {
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->security->validateCsrfToken($csrfToken)) {
            return ['success' => false, 'error' => 'Token CSRF inválido'];
        }
        
        try {
            $this->pdo->beginTransaction();
        
        $allowedColumns = array_map(
            fn($col) => $col['name'],
            array_filter($this->schema['columns'], fn($col) => !$col['is_primary'])
        );
        
        $data = $this->security->sanitizeInput($_POST, $allowedColumns, $this->schema);
        
        // Hook: beforeValidate
        $data = $this->executeHook('beforeValidate', $data);
        
        // Manejar archivos subidos
        foreach ($this->schema['columns'] as $column) {
            if (($column['metadata']['type'] ?? null) === 'file') {
                try {
                    $filePath = $this->fileHandler->handleUpload($column['name'], $column['metadata']);
                    if ($filePath) {
                        $data[$column['name']] = $filePath;
                    } elseif (!$column['is_nullable'] && empty($data[$column['name']])) {
                        // Si es requerido y no hay archivo, quitar del array para que falle validación
                        unset($data[$column['name']]);
                    } else {
                        // Si es opcional y no se subió archivo, quitar del array para no actualizar
                        unset($data[$column['name']]);
                    }
                } catch (\Exception $e) {
                    return ['success' => false, 'error' => $e->getMessage()];
                }
            }
        }
        
        $validator = new ValidationEngine($this->schema);
        
        if (!$validator->validate($data)) {
            $this->pdo->rollBack();
            return ['success' => false, 'errors' => $validator->getErrors()];
        }
        
        // Hook: afterValidate
        $data = $this->executeHook('afterValidate', $data);
        
        // Hook: beforeSave
        $data = $this->executeHook('beforeSave', $data);
        
        $isUpdate = isset($_POST['id']) && $_POST['id'];
        $id = $isUpdate ? (int)$_POST['id'] : null;
        
        if ($isUpdate) {
            // Hook: beforeUpdate
            $data = $this->executeHook('beforeUpdate', $data, $id);
            $id = $this->update($id, $data);
            // Hook: afterUpdate
            $this->executeHook('afterUpdate', $id, $data);
        } else {
            // Hook: beforeCreate
            $data = $this->executeHook('beforeCreate', $data);
            $id = $this->save($data);
            // Hook: afterCreate
            $this->executeHook('afterCreate', $id, $data);
        }
        
        // Hook: afterSave
        $this->executeHook('afterSave', $id, $data);
        
        $this->pdo->commit();
        return ['success' => true, 'id' => $id];
        
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function save(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);
        
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
        
        return (int) $this->pdo->lastInsertId();
    }

    private function update(int $id, array $data): int
    {
        $pk = $this->schema['primary_key'];
        $sets = array_map(fn($col) => "{$col} = :{$col}", array_keys($data));
        
        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = :id",
            $this->table,
            implode(', ', $sets),
            $pk
        );
        
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value, $value === null ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
        }
        
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $id;
    }

    private function findById(int $id): array
    {
        $pk = $this->schema['primary_key'];
        
        $sql = sprintf("SELECT * FROM %s WHERE %s = :id LIMIT 1", $this->table, $pk);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function list(array $options = []): array
    {
        $listGenerator = new ListGenerator($this->pdo, $this->schema);
        return $listGenerator->list($options);
    }

    public function delete(int $id): bool
    {
        try {
            $this->pdo->beginTransaction();
            
            // Hook: beforeDelete
            $this->executeHook('beforeDelete', $id);
            
            $pk = $this->schema['primary_key'];
            $sql = sprintf("DELETE FROM %s WHERE %s = :id", $this->table, $pk);
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute(['id' => $id]);
            
            // Hook: afterDelete
            $this->executeHook('afterDelete', $id);
            
            $this->pdo->commit();
            return $result;
            
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    // Métodos para Hooks/Eventos
    
    public function on(string $event, callable $callback): self
    {
        if (!isset($this->hooks[$event])) {
            $this->hooks[$event] = [];
        }
        
        $this->hooks[$event][] = $callback;
        return $this;
    }
    
    public function beforeValidate(callable $callback): self
    {
        return $this->on('beforeValidate', $callback);
    }
    
    public function afterValidate(callable $callback): self
    {
        return $this->on('afterValidate', $callback);
    }
    
    public function beforeSave(callable $callback): self
    {
        return $this->on('beforeSave', $callback);
    }
    
    public function afterSave(callable $callback): self
    {
        return $this->on('afterSave', $callback);
    }
    
    public function beforeCreate(callable $callback): self
    {
        return $this->on('beforeCreate', $callback);
    }
    
    public function afterCreate(callable $callback): self
    {
        return $this->on('afterCreate', $callback);
    }
    
    public function beforeUpdate(callable $callback): self
    {
        return $this->on('beforeUpdate', $callback);
    }
    
    public function afterUpdate(callable $callback): self
    {
        return $this->on('afterUpdate', $callback);
    }
    
    public function beforeDelete(callable $callback): self
    {
        return $this->on('beforeDelete', $callback);
    }
    
    public function afterDelete(callable $callback): self
    {
        return $this->on('afterDelete', $callback);
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
}
