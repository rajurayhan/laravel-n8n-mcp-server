<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MCP Route Prefix
    |--------------------------------------------------------------------------
    |
    | The URL prefix under which the n8n MCP server is mounted.
    | Default: /mcp/n8n  →  full URL: https://yourapp.com/mcp/n8n
    |
    */
    'route_prefix' => env('N8N_MCP_ROUTE_PREFIX', 'mcp/n8n'),

    /*
    |--------------------------------------------------------------------------
    | MCP Route Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware applied to the n8n MCP route. Add 'auth:sanctum' or a
    | custom middleware to restrict access.
    |
    */
    'route_middleware' => ['web'],
];
