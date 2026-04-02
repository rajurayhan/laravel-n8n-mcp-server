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

#[Title('List n8n Credentials')]
#[IsReadOnly]
#[IsIdempotent]
class ListCredentialsTool extends Tool
{
    protected string $name = 'list-n8n-credentials';

    public function description(): string
    {
        return 'List all credentials stored in n8n, optionally filtered by project.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'includeScopes' => $schema->boolean()
                ->description('Include credential scopes in the response.')
                ->nullable(),
            'projectId' => $schema->string()
                ->description('Filter credentials by project ID.')
                ->nullable(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'includeScopes' => ['nullable', 'boolean'],
            'projectId'     => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();
        $filters   = array_filter([
            'includeScopes' => $validated['includeScopes'] ?? null,
            'projectId'     => $validated['projectId'] ?? null,
        ], fn ($v) => $v !== null);

        try {
            $data        = N8nClient::credentials()->list($filters);
            $credentials = is_array($data['data'] ?? null) ? $data['data'] : (is_array($data) && array_is_list($data) ? $data : []);

            $compact = array_map(fn (array $c) => [
                'id'        => $c['id'] ?? null,
                'name'      => $c['name'] ?? null,
                'type'      => $c['type'] ?? null,
                'createdAt' => $c['createdAt'] ?? null,
                'updatedAt' => $c['updatedAt'] ?? null,
            ], $credentials);

            return Response::structured(['success' => true, 'data' => $compact]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
