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
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Title('List n8n Executions')]
#[IsReadOnly]
#[IsIdempotent]
#[IsOpenWorld]
class ListExecutionsTool extends Tool
{
    protected string $name = 'list-n8n-executions';

    public function description(): string
    {
        return 'List n8n workflow executions with optional filters by status, workflow, or project.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()
                ->description('Filter by status: success, error, waiting, running, or crashed.')
                ->nullable(),
            'workflowId' => $schema->string()
                ->description('Filter executions for a specific workflow ID.')
                ->nullable(),
            'projectId' => $schema->string()
                ->description('Filter executions for a specific project ID.')
                ->nullable(),
            'includeData' => $schema->boolean()
                ->description('Include full execution data in the response (increases response size).')
                ->nullable(),
            'limit' => $schema->integer()
                ->description('Maximum number of executions to return. Default 50, max 250.')
                ->min(1)
                ->max(250)
                ->nullable(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'status'      => ['nullable', 'string', 'in:success,error,waiting,running,crashed'],
            'workflowId'  => ['nullable', 'string'],
            'projectId'   => ['nullable', 'string'],
            'includeData' => ['nullable', 'boolean'],
            'limit'       => ['nullable', 'integer', 'min:1', 'max:250'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();

        $filters = array_filter([
            'status'      => $validated['status'] ?? null,
            'workflowId'  => $validated['workflowId'] ?? null,
            'projectId'   => $validated['projectId'] ?? null,
            'includeData' => $validated['includeData'] ?? null,
            'limit'       => $validated['limit'] ?? 50,
        ], fn ($v) => $v !== null);

        try {
            $data       = N8nClient::executions()->list($filters);
            $executions = is_array($data['data'] ?? null) ? $data['data'] : (is_array($data) && array_is_list($data) ? $data : []);

            return Response::structured([
                'success' => true,
                'data'    => $executions,
                'meta'    => ['total' => count($executions)],
            ]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
