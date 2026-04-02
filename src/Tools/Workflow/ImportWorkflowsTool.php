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

#[Title('Import n8n Workflows')]
#[IsOpenWorld]
class ImportWorkflowsTool extends Tool
{
    protected string $name = 'import-n8n-workflows';

    public function description(): string
    {
        return 'Import one or more n8n workflow definitions. IDs are stripped so each workflow is created as a new instance. Returns per-workflow success/error results.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'workflows' => $schema->array()
                ->description('Array of workflow definition objects (as exported by export-n8n-workflows). Each must include name, nodes, and connections.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'workflows'   => ['required', 'array', 'min:1'],
            'workflows.*' => ['array'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        try {
            $results = N8nClient::workflows()->import($validator->validated()['workflows']);

            return Response::structured([
                'success' => true,
                'data'    => $results,
                'meta'    => ['attempted' => count($results)],
            ]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
