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
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Title('Get n8n User')]
#[IsReadOnly]
#[IsIdempotent]
class GetUserTool extends Tool
{
    protected string $name = 'get-n8n-user';

    public function description(): string
    {
        return 'Get a specific n8n user by their ID or email address.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'idOrEmail' => $schema->string()
                ->description('The user ID or email address.')
                ->required(),
            'includeRole' => $schema->boolean()
                ->description('Include the user role in the response.')
                ->nullable(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'idOrEmail'   => ['required', 'string'],
            'includeRole' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();

        try {
            $user = N8nClient::users()->get(
                $validated['idOrEmail'],
                (bool) ($validated['includeRole'] ?? false)
            );

            return Response::structured(['success' => true, 'data' => $user]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
