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
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;

#[Title('Update n8n Workflow')]
#[IsOpenWorld]
class UpdateWorkflowTool extends Tool
{
    protected string $name = 'update-n8n-workflow';

    public function description(): string
    {
        return 'Replace an existing n8n workflow definition. All workflow fields (name, nodes, connections) must be supplied — n8n performs a full PUT replace.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'workflowId' => $schema->string()
                ->description('The n8n workflow ID to update.')
                ->required(),
            'name' => $schema->string()
                ->description('Workflow name.')
                ->max(255)
                ->required(),
            'nodes' => $schema->array()
                ->description('Full nodes array for the workflow.')
                ->required(),
            'connections' => $schema->object()
                ->description('Full connections object for the workflow.')
                ->required(),
            'settings' => $schema->object()
                ->description('Optional workflow settings.')
                ->nullable(),
            'active' => $schema->boolean()
                ->description('Active state for the workflow.')
                ->nullable(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'workflowId'  => ['required', 'string'],
            'name'        => ['required', 'string', 'max:255'],
            'nodes'       => ['required', 'array', 'min:1'],
            'connections' => ['required', 'array'],
            'settings'    => ['nullable', 'array'],
            'active'      => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();

        $payload = [
            'name'        => $validated['name'],
            'nodes'       => $validated['nodes'],
            'connections' => $validated['connections'],
        ];

        if (array_key_exists('settings', $validated)) {
            $payload['settings'] = $validated['settings'];
        }
        if (array_key_exists('active', $validated)) {
            $payload['active'] = (bool) $validated['active'];
        }

        try {
            $workflow = N8nClient::workflows()->update($validated['workflowId'], $payload);

            return Response::structured(['success' => true, 'data' => $workflow]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
