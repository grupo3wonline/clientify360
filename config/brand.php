<?php

declare(strict_types=1);

/*
 * Brand design tokens. The single source is resources/brand/tokens.json,
 * shared with tailwind.config.js; the result is cached by config:cache.
 */
return json_decode(
    (string) file_get_contents(resource_path('brand/tokens.json')),
    true,
    512,
    JSON_THROW_ON_ERROR,
);
