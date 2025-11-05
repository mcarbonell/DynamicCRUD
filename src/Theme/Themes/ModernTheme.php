<?php

namespace DynamicCRUD\Theme\Themes;

use DynamicCRUD\Theme\AbstractTheme;

class ModernTheme extends AbstractTheme
{
    public function getName(): string
    {
        return 'Modern';
    }
    
    public function getDescription(): string
    {
        return 'Modern theme with gradients, animations, and dark mode support.';
    }
}
