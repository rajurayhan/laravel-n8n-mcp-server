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

#[Title('Create n8n Variable')]
class CreateVariableTool extends Tool
{
    protected string $name = 'create-n8n-variable';

    public function description(): string
    {
        return 'Create a new n8n environment variable accessible across workflows.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'key' => $schema->string()
                ->description('Variable key name (e.g. API_BASE_URL).')
                ->required(),
            'value' => $schema->string()
                ->description('Variable value.')
                ->required(),
            'type' => $schema->string()
                ->description('Variable type (e.g. string). Defaults to string.')
                ->nullable(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'key'   => ['required', 'string'],
            'value' => ['required', 'string'],
            'type'  => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();
        $payload   = ['key' => $validated['key'], 'value' => $validated['value']];

        if (!empty($validated['type'])) {
            $payload['type'] = $validated['type'];
        }

        try {
            $variable = N8nClient::variables()->create($payload);

            return Response::structured(['success' => true, 'data' => $variable]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
