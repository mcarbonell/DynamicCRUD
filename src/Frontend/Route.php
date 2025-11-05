<?php

namespace DynamicCRUD\Frontend;

/**
 * Route
 * 
 * Represents a matched route with pattern, handler, and parameters
 */
class Route
{
    public function __construct(
        public string $pattern,
        public string $handler,
        public array $params = []
    ) {}
}
