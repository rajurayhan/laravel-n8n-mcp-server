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

#[Title('Remove User from n8n Project')]
class RemoveProjectUserTool extends Tool
{
    protected string $name = 'remove-n8n-project-user';

    public function description(): string
    {
        return 'Remove a user from a specific n8n project. The user remains in the n8n instance but loses access to the project.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'projectId' => $schema->string()
                ->description('The n8n project ID.')
                ->required(),
            'userId' => $schema->string()
                ->description('The user ID to remove from the project.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'projectId' => ['required', 'string'],
            'userId'    => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();

        try {
            N8nClient::projects()->removeUser($validated['projectId'], $validated['userId']);

            return Response::structured([
                'success' => true,
                'message' => 'User removed from project successfully.',
            ]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
