<?php

namespace DynamicCRUD\CLI\Commands;

abstract class Command
{
    abstract public function execute(array $args): void;
    
    protected function success(string $message): void
    {
        echo "✅ $message\n";
    }
    
    protected function info(string $message): void
    {
        echo "ℹ️  $message\n";
    }
    
    protected function warning(string $message): void
    {
        echo "⚠️  $message\n";
    }
    
    protected function error(string $message): void
    {
        echo "❌ $message\n";
    }
    
    protected function getPDO(): \PDO
    {
        $config = $this->loadConfig();
        $db = $config['database'] ?? $config;
        
        $dsn = sprintf(
            '%s:host=%s;dbname=%s',
            $db['driver'] ?? 'mysql',
            $db['host'] ?? 'localhost',
            $db['database'] ?? 'test'
        );
        
        $pdo = new \PDO($dsn, $db['username'] ?? 'root', $db['password'] ?? '');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        return $pdo;
    }
    
    protected function loadConfig(): array
    {
        $configFile = getcwd() . '/dynamiccrud.json';
        
        if (!file_exists($configFile)) {
            throw new \Exception('Configuration file not found. Run: php dynamiccrud init');
        }
        
        $config = json_decode(file_get_contents($configFile), true);
        
        if (!is_array($config)) {
            throw new \Exception('Invalid configuration file');
        }
        
        return $config;
    }
}
