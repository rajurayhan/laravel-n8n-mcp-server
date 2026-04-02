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

#[Title('Activate Multiple n8n Workflows')]
class ActivateManyWorkflowsTool extends Tool
{
    protected string $name = 'activate-many-n8n-workflows';

    public function description(): string
    {
        return 'Activate multiple n8n workflows at once by providing an array of workflow IDs. Returns per-ID success/error results.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'ids' => $schema->array()
                ->description('Array of workflow ID strings to activate.')
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
            $results = N8nClient::workflows()->activateMany($validator->validated()['ids']);

            return Response::structured(['success' => true, 'data' => $results]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
