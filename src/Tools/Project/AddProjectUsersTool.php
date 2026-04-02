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

#[Title('Add Users to n8n Project')]
class AddProjectUsersTool extends Tool
{
    protected string $name = 'add-n8n-project-users';

    public function description(): string
    {
        return 'Add one or more users to an n8n project with specified roles. Each relation object must include userId and role.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'projectId' => $schema->string()
                ->description('The n8n project ID to add users to.')
                ->required(),
            'relations' => $schema->array()
                ->description('Array of relation objects. Each must have userId (string) and role (e.g. member, admin).')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'projectId'          => ['required', 'string'],
            'relations'          => ['required', 'array', 'min:1'],
            'relations.*.userId' => ['required', 'string'],
            'relations.*.role'   => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();

        try {
            N8nClient::projects()->addUsers($validated['projectId'], $validated['relations']);

            return Response::structured([
                'success' => true,
                'message' => 'Users added to project successfully.',
            ]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
