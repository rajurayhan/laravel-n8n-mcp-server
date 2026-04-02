<?php

namespace Raju\N8nMcp\Tools\WorkflowTag;

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

#[Title('List n8n Workflow Tags')]
#[IsReadOnly]
#[IsIdempotent]
class ListWorkflowTagsTool extends Tool
{
    protected string $name = 'list-n8n-workflow-tags';

    public function description(): string
    {
        return 'List all tags associated with a specific n8n workflow.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'workflowId' => $schema->string()
                ->description('The n8n workflow ID.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'workflowId' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        try {
            $tags = N8nClient::workflows()->tags($validator->validated()['workflowId']);

            return Response::structured(['success' => true, 'data' => $tags]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
