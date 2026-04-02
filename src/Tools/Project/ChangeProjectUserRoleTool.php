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

#[Title('Change User Role in n8n Project')]
class ChangeProjectUserRoleTool extends Tool
{
    protected string $name = 'change-n8n-project-user-role';

    public function description(): string
    {
        return 'Change the role of an existing user within a specific n8n project.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'projectId' => $schema->string()
                ->description('The n8n project ID.')
                ->required(),
            'userId' => $schema->string()
                ->description('The user ID whose role should be changed.')
                ->required(),
            'role' => $schema->string()
                ->description('The new role to assign (e.g. member, admin).')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'projectId' => ['required', 'string'],
            'userId'    => ['required', 'string'],
            'role'      => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();

        try {
            N8nClient::projects()->changeUserRole(
                $validated['projectId'],
                $validated['userId'],
                $validated['role']
            );

            return Response::structured([
                'success' => true,
                'message' => "User role changed to '{$validated['role']}' in project.",
            ]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
