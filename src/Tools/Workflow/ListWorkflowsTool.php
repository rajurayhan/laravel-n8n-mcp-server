<?php

namespace Raju\N8nMcp\Tools\Workflow;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use KayedSpace\N8n\Exceptions\N8nException;
use KayedSpace\N8n\Facades\N8nClient;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Title('List n8n Workflows')]
#[IsReadOnly]
#[IsIdempotent]
#[IsOpenWorld]
class ListWorkflowsTool extends Tool
{
    protected string $name = 'list-n8n-workflows';

    public function description(): string
    {
        return 'List n8n workflows with optional filters. Returns compact workflow info including id, name, active status, and tags.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'active' => $schema->boolean()
                ->description('Filter by active status.')
                ->nullable(),
            'tags' => $schema->string()
                ->description('Comma-separated tag names to filter by.')
                ->nullable(),
            'name' => $schema->string()
                ->description('Case-insensitive workflow name contains filter.')
                ->max(255)
                ->nullable(),
            'projectId' => $schema->string()
                ->description('Filter workflows by project ID.')
                ->nullable(),
            'limit' => $schema->integer()
                ->description('Maximum workflows to return. Default 100, max 500.')
                ->min(1)
                ->max(500)
                ->nullable(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'active'    => ['nullable', 'boolean'],
            'tags'      => ['nullable', 'string'],
            'name'      => ['nullable', 'string', 'max:255'],
            'projectId' => ['nullable', 'string'],
            'limit'     => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();

        $filters = array_filter([
            'active'    => $validated['active'] ?? null,
            'tags'      => $validated['tags'] ?? null,
            'name'      => $validated['name'] ?? null,
            'projectId' => $validated['projectId'] ?? null,
        ], fn ($v) => $v !== null);

        try {
            $data      = N8nClient::workflows()->list($filters);
            $workflows = is_array($data['data'] ?? null) ? $data['data'] : (is_array($data) && array_is_list($data) ? $data : []);
            $limit     = (int) ($validated['limit'] ?? 100);
            $workflows = array_slice($workflows, 0, $limit);

            $compact = array_map(fn (array $w) => [
                'id'        => $w['id'] ?? null,
                'name'      => $w['name'] ?? null,
                'active'    => (bool) ($w['active'] ?? false),
                'updatedAt' => $w['updatedAt'] ?? null,
                'createdAt' => $w['createdAt'] ?? null,
                'tags'      => $w['tags'] ?? [],
            ], $workflows);

            return Response::structured([
                'success' => true,
                'data'    => $compact,
                'meta'    => ['total' => count($compact), 'limit' => $limit],
            ]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
