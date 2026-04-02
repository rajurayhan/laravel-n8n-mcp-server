<?php

namespace Raju\N8nMcp\Tools\Project;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use KayedSpace\N8n\Exceptions\N8nException;
use KayedSpace\N8n\Facades\N8nClient;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Title('List n8n Projects')]
#[IsReadOnly]
#[IsIdempotent]
class ListProjectsTool extends Tool
{
    protected string $name = 'list-n8n-projects';

    public function description(): string
    {
        return 'List all projects in the n8n instance.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        try {
            $data     = N8nClient::projects()->list();
            $projects = is_array($data['data'] ?? null) ? $data['data'] : (is_array($data) && array_is_list($data) ? $data : []);

            return Response::structured(['success' => true, 'data' => $projects, 'meta' => ['total' => count($projects)]]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
