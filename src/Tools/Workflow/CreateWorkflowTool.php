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

#[Title('Create n8n Workflow')]
#[IsOpenWorld]
class CreateWorkflowTool extends Tool
{
    protected string $name = 'create-n8n-workflow';

    public function description(): string
    {
        return 'Create a new n8n workflow. Provide a name, nodes array, and connections object at minimum.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()
                ->description('Workflow name.')
                ->max(255)
                ->required(),
            'nodes' => $schema->array()
                ->description('Array of n8n node objects. Must include at least one Start or trigger node.')
                ->required(),
            'connections' => $schema->object()
                ->description('Connections object mapping node outputs to inputs.')
                ->required(),
            'settings' => $schema->object()
                ->description('Optional workflow settings (e.g. executionOrder, saveDataSuccessExecution).')
                ->nullable(),
            'tags' => $schema->array()
                ->description('Optional array of tag objects ({id}) to attach to the workflow.')
                ->nullable(),
            'active' => $schema->boolean()
                ->description('Whether to activate the workflow immediately after creation.')
                ->nullable(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'name'        => ['required', 'string', 'max:255'],
            'nodes'       => ['required', 'array', 'min:1'],
            'connections' => ['required', 'array'],
            'settings'    => ['nullable', 'array'],
            'tags'        => ['nullable', 'array'],
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

        if (isset($validated['settings'])) {
            $payload['settings'] = $validated['settings'];
        }
        if (isset($validated['tags'])) {
            $payload['tags'] = $validated['tags'];
        }

        try {
            $workflow = N8nClient::workflows()->create($payload);

            if (!empty($validated['active'])) {
                N8nClient::workflows()->activate($workflow['id']);
                $workflow['active'] = true;
            }

            return Response::structured(['success' => true, 'data' => $workflow]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
