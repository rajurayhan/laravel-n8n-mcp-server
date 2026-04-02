<?php

namespace Raju\N8nMcp\Tools\Tag;

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
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Title('List n8n Tags')]
#[IsReadOnly]
#[IsIdempotent]
class ListTagsTool extends Tool
{
    protected string $name = 'list-n8n-tags';

    public function description(): string
    {
        return 'List all workflow tags in n8n, optionally including usage count per tag.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'withUsageCount' => $schema->boolean()
                ->description('Include the number of workflows using each tag.')
                ->nullable(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'withUsageCount' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();
        $filters   = array_filter(['withUsageCount' => $validated['withUsageCount'] ?? null], fn ($v) => $v !== null);

        try {
            $data = N8nClient::tags()->list($filters);
            $tags = is_array($data['data'] ?? null) ? $data['data'] : (is_array($data) && array_is_list($data) ? $data : []);

            return Response::structured(['success' => true, 'data' => $tags, 'meta' => ['total' => count($tags)]]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
