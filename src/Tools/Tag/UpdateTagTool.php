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

#[Title('Update n8n Tag')]
class UpdateTagTool extends Tool
{
    protected string $name = 'update-n8n-tag';

    public function description(): string
    {
        return 'Rename an existing n8n workflow tag.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'tagId' => $schema->string()
                ->description('The n8n tag ID to update.')
                ->required(),
            'name' => $schema->string()
                ->description('New tag name.')
                ->max(100)
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'tagId' => ['required', 'string'],
            'name'  => ['required', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();

        try {
            $tag = N8nClient::tags()->update($validated['tagId'], ['name' => $validated['name']]);

            return Response::structured(['success' => true, 'data' => $tag]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
