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

#[Title('Delete n8n Variable')]
class DeleteVariableTool extends Tool
{
    protected string $name = 'delete-n8n-variable';

    public function description(): string
    {
        return 'Delete an n8n environment variable by its ID.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'variableId' => $schema->string()
                ->description('The n8n variable ID to delete.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'variableId' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        try {
            $result = N8nClient::variables()->delete($validator->validated()['variableId']);

            return Response::structured(['success' => true, 'data' => $result]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
