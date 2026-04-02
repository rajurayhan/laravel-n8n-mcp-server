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

#[Title('Update n8n Variable')]
class UpdateVariableTool extends Tool
{
    protected string $name = 'update-n8n-variable';

    public function description(): string
    {
        return 'Update the value of an existing n8n environment variable by its ID.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'variableId' => $schema->string()
                ->description('The n8n variable ID to update.')
                ->required(),
            'key' => $schema->string()
                ->description('Updated variable key name.')
                ->nullable(),
            'value' => $schema->string()
                ->description('Updated variable value.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'variableId' => ['required', 'string'],
            'key'        => ['nullable', 'string'],
            'value'      => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();
        $payload   = ['value' => $validated['value']];

        if (!empty($validated['key'])) {
            $payload['key'] = $validated['key'];
        }

        try {
            N8nClient::variables()->update($validated['variableId'], $payload);

            return Response::structured([
                'success' => true,
                'message' => 'Variable updated successfully.',
            ]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
