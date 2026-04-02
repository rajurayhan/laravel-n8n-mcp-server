<?php

namespace Raju\N8nMcp\Tools\User;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use KayedSpace\N8n\Exceptions\N8nException;
use KayedSpace\N8n\Facades\N8nClient;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Title('Change n8n User Role')]
class ChangeUserRoleTool extends Tool
{
    protected string $name = 'change-n8n-user-role';

    public function description(): string
    {
        return 'Change the global role of an n8n user by their ID or email.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'idOrEmail' => $schema->string()
                ->description('The user ID or email address.')
                ->required(),
            'newRoleName' => $schema->string()
                ->description('The new role to assign. Common values: global:admin, global:member.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'idOrEmail'   => ['required', 'string'],
            'newRoleName' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();

        try {
            $result = N8nClient::users()->changeRole(
                $validated['idOrEmail'],
                $validated['newRoleName']
            );

            return Response::structured(['success' => true, 'data' => $result]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
