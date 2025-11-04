<?php

namespace DynamicCRUD\Tests;

use PHPUnit\Framework\TestCase;
use DynamicCRUD\ThemeManager;
use DynamicCRUD\GlobalMetadata;

class ThemeManagerTest extends TestCase
{
    private \PDO $pdo;
    private GlobalMetadata $config;
    private ThemeManager $themeManager;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        $this->config = new GlobalMetadata($this->pdo);
        $this->config->clear();
        
        $this->themeManager = new ThemeManager($this->config);
    }

    protected function tearDown(): void
    {
        $this->config->clear();
    }

    public function testGetThemeReturnsDefaults(): void
    {
        $theme = $this->themeManager->getTheme();
        
        $this->assertIsArray($theme);
        $this->assertEquals('#667eea', $theme['primary_color']);
        $this->assertEquals('#764ba2', $theme['secondary_color']);
    }

    public function testGetThemeMergesWithConfig(): void
    {
        $this->config->set('theme', ['primary_color' => '#ff0000']);
        
        $theme = $this->themeManager->getTheme();
        
        $this->assertEquals('#ff0000', $theme['primary_color']);
        $this->assertEquals('#764ba2', $theme['secondary_color']); // Default
    }

    public function testRenderCSSVariables(): void
    {
        $this->config->set('theme', [
            'primary_color' => '#123456',
            'font_family' => 'Arial, sans-serif'
        ]);
        
        $css = $this->themeManager->renderCSSVariables();
        
        $this->assertStringContainsString(':root {', $css);
        $this->assertStringContainsString('--primary-color: #123456;', $css);
        $this->assertStringContainsString('--font-family: Arial, sans-serif;', $css);
    }

    public function testRenderBrandingWithLogo(): void
    {
        $this->config->set('application', [
            'name' => 'Test App',
            'logo' => '/logo.png'
        ]);
        
        $html = $this->themeManager->renderBranding();
        
        $this->assertStringContainsString('app-branding', $html);
        $this->assertStringContainsString('Test App', $html);
        $this->assertStringContainsString('/logo.png', $html);
    }

    public function testRenderBrandingWithoutConfig(): void
    {
        $html = $this->themeManager->renderBranding();
        
        $this->assertEmpty($html);
    }

    public function testApplyThemeToStyles(): void
    {
        $css = 'background: #667eea; color: #764ba2;';
        
        $themed = $this->themeManager->applyThemeToStyles($css);
        
        $this->assertStringContainsString('var(--primary-color)', $themed);
        $this->assertStringContainsString('var(--secondary-color)', $themed);
        $this->assertStringNotContainsString('#667eea', $themed);
    }
}
