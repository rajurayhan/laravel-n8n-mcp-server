<?php

namespace Raju\N8nMcp\Tools\Variable;

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

#[Title('List n8n Variables')]
#[IsReadOnly]
#[IsIdempotent]
class ListVariablesTool extends Tool
{
    protected string $name = 'list-n8n-variables';

    public function description(): string
    {
        return 'List all environment variables defined in n8n.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        try {
            $data      = N8nClient::variables()->list();
            $variables = is_array($data['data'] ?? null) ? $data['data'] : (is_array($data) && array_is_list($data) ? $data : []);

            return Response::structured(['success' => true, 'data' => $variables, 'meta' => ['total' => count($variables)]]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
