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
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;

#[Title('Create n8n Credential')]
#[IsOpenWorld]
class CreateCredentialTool extends Tool
{
    protected string $name = 'create-n8n-credential';

    public function description(): string
    {
        return 'Create a new n8n credential. Use get-n8n-credential-schema first to discover the required data fields for the credential type.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()
                ->description('Display name for the credential.')
                ->required(),
            'type' => $schema->string()
                ->description('n8n credential type name (e.g. slackApi, httpBasicAuth).')
                ->required(),
            'data' => $schema->object()
                ->description('Credential data fields as required by the credential type schema.')
                ->required(),
            'projectId' => $schema->string()
                ->description('Optional project ID to scope the credential to.')
                ->nullable(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'name'      => ['required', 'string'],
            'type'      => ['required', 'string'],
            'data'      => ['required', 'array'],
            'projectId' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();

        $payload = [
            'name' => $validated['name'],
            'type' => $validated['type'],
            'data' => $validated['data'],
        ];

        if (!empty($validated['projectId'])) {
            $payload['projectId'] = $validated['projectId'];
        }

        try {
            $credential = N8nClient::credentials()->create($payload);

            return Response::structured(['success' => true, 'data' => $credential]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
