<?php

namespace Raju\N8nMcp\Tools\Workflow;

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

#[Title('Get n8n Workflow by ID')]
#[IsReadOnly]
#[IsIdempotent]
#[IsOpenWorld]
class GetWorkflowTool extends Tool
{
    protected string $name = 'get-n8n-workflow';

    public function description(): string
    {
        return 'Retrieve a full n8n workflow definition by ID, including nodes and connections.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'workflowId' => $schema->string()
                ->description('The n8n workflow ID.')
                ->required(),
            'excludePinnedData' => $schema->boolean()
                ->description('Exclude pinned node data from the response to reduce size.')
                ->nullable(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'workflowId'        => ['required', 'string'],
            'excludePinnedData' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();

        try {
            $workflow = N8nClient::workflows()->get(
                $validated['workflowId'],
                (bool) ($validated['excludePinnedData'] ?? false)
            );

            return Response::structured(['success' => true, 'data' => $workflow]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
