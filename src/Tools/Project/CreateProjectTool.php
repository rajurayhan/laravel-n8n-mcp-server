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

#[Title('Create n8n Project')]
class CreateProjectTool extends Tool
{
    protected string $name = 'create-n8n-project';

    public function description(): string
    {
        return 'Create a new n8n project to organize workflows and credentials.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()
                ->description('Project name.')
                ->max(255)
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        try {
            $project = N8nClient::projects()->create(['name' => $validator->validated()['name']]);

            return Response::structured(['success' => true, 'data' => $project]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
