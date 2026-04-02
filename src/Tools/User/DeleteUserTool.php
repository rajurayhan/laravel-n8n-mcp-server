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

#[Title('Delete n8n User')]
class DeleteUserTool extends Tool
{
    protected string $name = 'delete-n8n-user';

    public function description(): string
    {
        return 'Delete an n8n user by their ID or email address. This action is irreversible.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'idOrEmail' => $schema->string()
                ->description('The user ID or email address to delete.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'idOrEmail' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        try {
            $result = N8nClient::users()->delete($validator->validated()['idOrEmail']);

            return Response::structured(['success' => true, 'data' => $result]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
