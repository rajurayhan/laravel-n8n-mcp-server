<?php
/*
 * This file is part of the Laravel n8n MCP Server package.
 *
 * Copyright (c) 2026 Raju Rayhan
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Raju\N8nMcp;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Mcp\Facades\Mcp;
use Raju\N8nMcp\Server\N8nServer;

class N8nMcpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/n8n-mcp.php', 'n8n-mcp');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/n8n-mcp.php' => config_path('n8n-mcp.php'),
            ], 'n8n-mcp-config');
        }

        $this->registerMcpRoute();
    }

    private function registerMcpRoute(): void
    {
        $prefix     = config('n8n-mcp.route_prefix', 'mcp/n8n');
        $middleware = config('n8n-mcp.route_middleware', ['web']);

        Route::middleware($middleware)
            ->group(function () use ($prefix) {
                Mcp::server(N8nServer::class)->at($prefix);
            });
    }
}
