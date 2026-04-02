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

#[Title('Move n8n Workflow to Project')]
class MoveWorkflowTool extends Tool
{
    protected string $name = 'move-n8n-workflow';

    public function description(): string
    {
        return 'Move (transfer) an n8n workflow to a different project.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'workflowId' => $schema->string()
                ->description('The n8n workflow ID to move.')
                ->required(),
            'destinationProjectId' => $schema->string()
                ->description('The target project ID to move the workflow into.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'workflowId'           => ['required', 'string'],
            'destinationProjectId' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();

        try {
            $result = N8nClient::workflows()->transfer(
                $validated['workflowId'],
                $validated['destinationProjectId']
            );

            return Response::structured(['success' => true, 'data' => $result]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
