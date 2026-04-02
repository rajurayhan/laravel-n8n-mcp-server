<?php

namespace Raju\N8nMcp\Tools\Credential;

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

#[Title('Get n8n Credential by ID')]
#[IsReadOnly]
#[IsIdempotent]
class GetCredentialTool extends Tool
{
    protected string $name = 'get-n8n-credential';

    public function description(): string
    {
        return 'Retrieve a specific n8n credential by its ID. Sensitive data fields are masked by n8n.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'credentialId' => $schema->string()
                ->description('The n8n credential ID to retrieve.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'credentialId' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        try {
            $credential = N8nClient::credentials()->get($validator->validated()['credentialId']);

            return Response::structured(['success' => true, 'data' => $credential]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
