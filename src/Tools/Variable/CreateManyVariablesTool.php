<?php

namespace Raju\N8nMcp\Tools\Variable;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use KayedSpace\N8n\Exceptions\N8nException;
use KayedSpace\N8n\Facades\N8nClient;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Title('Create Multiple n8n Variables')]
class CreateManyVariablesTool extends Tool
{
    protected string $name = 'create-many-n8n-variables';

    public function description(): string
    {
        return 'Create multiple n8n environment variables in a single call. Returns per-variable success/error results.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'variables' => $schema->array()
                ->description('Array of variable objects to create. Each must have key and value fields (e.g. [{"key": "API_URL", "value": "https://..."}]).')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'variables'         => ['required', 'array', 'min:1'],
            'variables.*.key'   => ['required', 'string'],
            'variables.*.value' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        try {
            $results = N8nClient::variables()->createMany($validator->validated()['variables']);

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
