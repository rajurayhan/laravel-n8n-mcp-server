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
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Title('Export n8n Workflows')]
#[IsReadOnly]
#[IsIdempotent]
class ExportWorkflowsTool extends Tool
{
    protected string $name = 'export-n8n-workflows';

    public function description(): string
    {
        return 'Export one or more n8n workflows as full JSON definitions. Useful for backup or migration. Returns an array of workflow objects.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'ids' => $schema->array()
                ->description('Array of workflow ID strings to export.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        try {
            $workflows = N8nClient::workflows()->export($validator->validated()['ids']);

            return Response::structured([
                'success' => true,
                'data'    => $workflows,
                'meta'    => ['exported' => count($workflows)],
            ]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
