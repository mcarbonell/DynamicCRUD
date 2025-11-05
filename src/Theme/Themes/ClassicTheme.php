<?php

namespace DynamicCRUD\Theme\Themes;

use DynamicCRUD\Theme\AbstractTheme;

class ClassicTheme extends AbstractTheme
{
    public function getName(): string
    {
        return 'Classic';
    }
    
    public function getDescription(): string
    {
        return 'Traditional blog design with sidebar layout and serif fonts.';
    }
}
