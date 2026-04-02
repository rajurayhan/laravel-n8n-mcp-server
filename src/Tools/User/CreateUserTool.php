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

#[Title('Create (Invite) n8n User')]
class CreateUserTool extends Tool
{
    protected string $name = 'create-n8n-user';

    public function description(): string
    {
        return 'Invite one or more users to the n8n instance. Sends an invitation email to each address.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'users' => $schema->array()
                ->description('Array of user objects to invite. Each object must have an email field and optionally a role (member or admin).')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'users'          => ['required', 'array', 'min:1'],
            'users.*.email'  => ['required', 'email'],
            'users.*.role'   => ['nullable', 'string', 'in:member,admin,global:admin,global:member'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        try {
            $result = N8nClient::users()->create($validator->validated()['users']);

            return Response::structured(['success' => true, 'data' => $result]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
