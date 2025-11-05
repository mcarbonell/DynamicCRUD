<?php

return [
    'name' => 'Classic',
    'description' => 'Traditional blog design with sidebar and serif fonts',
    'version' => '1.0.0',
    'author' => 'DynamicCRUD',
    'screenshot' => 'screenshot.png',
    
    'colors' => [
        'primary' => '#8b4513',
        'secondary' => '#d2691e',
        'background' => '#f5f5dc',
        'text' => '#333333',
        'link' => '#8b4513'
    ],
    
    'fonts' => [
        'heading' => 'Georgia, "Times New Roman", serif',
        'body' => 'Georgia, "Times New Roman", serif'
    ],
    
    'layout' => [
        'container_width' => '1000px',
        'sidebar' => true,
        'header_style' => 'static'
    ],
    
    'features' => [
        'dark_mode' => false,
        'animations' => false,
        'breadcrumbs' => true,
        'social_share' => false
    ]
];
