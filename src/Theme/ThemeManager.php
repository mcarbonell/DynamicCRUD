<?php

namespace DynamicCRUD\Theme;

use PDO;

class ThemeManager
{
    private PDO $pdo;
    private string $themesDir;
    private array $themes = [];
    private ?Theme $activeTheme = null;
    
    public function __construct(PDO $pdo, string $themesDir)
    {
        $this->pdo = $pdo;
        $this->themesDir = $themesDir;
        $this->ensureThemesTable();
    }
    
    private function ensureThemesTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS _themes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) UNIQUE NOT NULL,
                active BOOLEAN DEFAULT FALSE,
                config JSON,
                installed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) COMMENT '{\"display_name\":\"Themes\",\"icon\":\"🎨\"}'
        ");
    }
    
    private function loadActiveTheme(): void
    {
        $stmt = $this->pdo->query("SELECT name FROM _themes WHERE active = 1 LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row && isset($this->themes[$row['name']])) {
            $this->activeTheme = $this->themes[$row['name']];
        }
    }
    
    public function register(string $name, Theme $theme): void
    {
        $this->themes[$name] = $theme;
    }
    
    public function getAvailable(): array
    {
        return array_map(function($theme) {
            return [
                'name' => $theme->getName(),
                'description' => $theme->getDescription(),
                'version' => $theme->getVersion(),
                'author' => $theme->getAuthor(),
                'screenshot' => $theme->getScreenshot()
            ];
        }, $this->themes);
    }
    
    public function getActive(): ?Theme
    {
        // Lazy load active theme
        if ($this->activeTheme === null && !empty($this->themes)) {
            $this->loadActiveTheme();
        }
        return $this->activeTheme;
    }
    
    public function activate(string $name): bool
    {
        if (!isset($this->themes[$name])) {
            return false;
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Deactivate all themes
            $this->pdo->exec("UPDATE _themes SET active = 0");
            
            // Check if theme exists in database
            $stmt = $this->pdo->prepare("SELECT id FROM _themes WHERE name = :name");
            $stmt->execute(['name' => $name]);
            
            if ($stmt->fetch()) {
                // Update existing
                $stmt = $this->pdo->prepare("UPDATE _themes SET active = 1 WHERE name = :name");
                $stmt->execute(['name' => $name]);
            } else {
                // Insert new
                $stmt = $this->pdo->prepare("INSERT INTO _themes (name, active) VALUES (:name, 1)");
                $stmt->execute(['name' => $name]);
            }
            
            $this->pdo->commit();
            $this->activeTheme = $this->themes[$name];
            return true;
            
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
    
    public function deactivate(): bool
    {
        try {
            $this->pdo->exec("UPDATE _themes SET active = 0");
            $this->activeTheme = null;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function getThemeInfo(string $name): array
    {
        if (!isset($this->themes[$name])) {
            return [];
        }
        
        $theme = $this->themes[$name];
        return [
            'name' => $theme->getName(),
            'description' => $theme->getDescription(),
            'version' => $theme->getVersion(),
            'author' => $theme->getAuthor(),
            'screenshot' => $theme->getScreenshot(),
            'config' => $theme->getConfig(),
            'templates' => $theme->getTemplates(),
            'assets' => $theme->getAssets()
        ];
    }
    
    public function isInstalled(string $name): bool
    {
        $stmt = $this->pdo->prepare("SELECT id FROM _themes WHERE name = :name");
        $stmt->execute(['name' => $name]);
        return $stmt->fetch() !== false;
    }
    
    public function render(string $template, array $data): string
    {
        if (!$this->activeTheme) {
            return $this->renderFallback($template, $data);
        }
        
        return $this->activeTheme->render($template, $data);
    }
    
    private function renderFallback(string $template, array $data): string
    {
        return sprintf(
            '<div style="padding:20px;background:#fff3cd;border:1px solid #ffc107;"><strong>No Active Theme</strong><p>Please activate a theme to render templates.</p></div>'
        );
    }
    
    public function getConfig(?string $key = null): mixed
    {
        if (!$this->activeTheme) {
            return null;
        }
        
        $config = $this->activeTheme->getConfig();
        
        if ($key === null) {
            return $config;
        }
        
        // Support dot notation
        $keys = explode('.', $key);
        $value = $config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return null;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
    
    public function setConfig(string $key, mixed $value): bool
    {
        if (!$this->activeTheme) {
            return false;
        }
        
        try {
            $name = $this->activeTheme->getName();
            
            // Get current config
            $stmt = $this->pdo->prepare("SELECT config FROM _themes WHERE name = :name");
            $stmt->execute(['name' => $name]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $config = $row && $row['config'] ? json_decode($row['config'], true) ?? [] : [];
            
            // Set value with dot notation support
            $keys = explode('.', $key);
            $current = &$config;
            
            foreach ($keys as $i => $k) {
                if ($i === count($keys) - 1) {
                    $current[$k] = $value;
                } else {
                    if (!isset($current[$k]) || !is_array($current[$k])) {
                        $current[$k] = [];
                    }
                    $current = &$current[$k];
                }
            }
            
            // Save to database
            $stmt = $this->pdo->prepare("UPDATE _themes SET config = :config WHERE name = :name");
            $stmt->execute([
                'name' => $name,
                'config' => json_encode($config)
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            return false;
        }
    }
}
