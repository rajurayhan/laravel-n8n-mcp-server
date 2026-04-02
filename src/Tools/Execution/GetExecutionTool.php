<?php

namespace Raju\N8nMcp\Tools\Execution;

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

#[Title('Get n8n Execution by ID')]
#[IsReadOnly]
#[IsIdempotent]
class GetExecutionTool extends Tool
{
    protected string $name = 'get-n8n-execution';

    public function description(): string
    {
        return 'Retrieve details for a specific n8n execution by ID, optionally including full node data.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'executionId' => $schema->integer()
                ->description('The n8n execution ID (numeric).')
                ->required(),
            'includeData' => $schema->boolean()
                ->description('Include full node execution data in the response.')
                ->nullable(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'executionId' => ['required', 'integer'],
            'includeData' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();

        try {
            $execution = N8nClient::executions()->get(
                (int) $validated['executionId'],
                (bool) ($validated['includeData'] ?? false)
            );

            return Response::structured(['success' => true, 'data' => $execution]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
