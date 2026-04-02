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

#[Title('Create n8n Tag')]
class CreateTagTool extends Tool
{
    protected string $name = 'create-n8n-tag';

    public function description(): string
    {
        return 'Create a new workflow tag in n8n.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()
                ->description('Tag name.')
                ->max(100)
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'name' => ['required', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        try {
            $tag = N8nClient::tags()->create(['name' => $validator->validated()['name']]);

            return Response::structured(['success' => true, 'data' => $tag]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
