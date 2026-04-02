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

#[Title('Get n8n Tag')]
#[IsReadOnly]
#[IsIdempotent]
class GetTagTool extends Tool
{
    protected string $name = 'get-n8n-tag';

    public function description(): string
    {
        return 'Get a specific n8n tag by ID.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'tagId' => $schema->string()
                ->description('The n8n tag ID.')
                ->required(),
            'withUsageCount' => $schema->boolean()
                ->description('Include the number of workflows using this tag.')
                ->nullable(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'tagId'          => ['required', 'string'],
            'withUsageCount' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();

        try {
            $tag = N8nClient::tags()->get($validated['tagId']);

            return Response::structured(['success' => true, 'data' => $tag]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
