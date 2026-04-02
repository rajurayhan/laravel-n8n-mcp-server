<?php

namespace Raju\N8nMcp\Tools\Project;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use KayedSpace\N8n\Exceptions\N8nException;
use KayedSpace\N8n\Facades\N8nClient;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Title('Delete n8n Project')]
class DeleteProjectTool extends Tool
{
    protected string $name = 'delete-n8n-project';

    public function description(): string
    {
        return 'Delete an n8n project by ID. Workflows and credentials within the project may be reassigned or deleted depending on n8n configuration.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'projectId' => $schema->string()
                ->description('The n8n project ID to delete.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'projectId' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        try {
            $result = N8nClient::projects()->delete($validator->validated()['projectId']);

            return Response::structured(['success' => true, 'data' => $result]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
