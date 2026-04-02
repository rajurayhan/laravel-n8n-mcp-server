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

#[Title('Create Multiple n8n Tags')]
class CreateManyTagsTool extends Tool
{
    protected string $name = 'create-many-n8n-tags';

    public function description(): string
    {
        return 'Create multiple n8n workflow tags in a single call. Returns per-tag success/error results.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'tags' => $schema->array()
                ->description('Array of tag objects to create. Each object must have a name field (e.g. [{"name": "Production"}, {"name": "Testing"}]).')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'tags'        => ['required', 'array', 'min:1'],
            'tags.*.name' => ['required', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        try {
            $results = N8nClient::tags()->createMany($validator->validated()['tags']);

            return Response::structured([
                'success' => true,
                'data'    => $results,
                'meta'    => ['attempted' => count($results)],
            ]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
