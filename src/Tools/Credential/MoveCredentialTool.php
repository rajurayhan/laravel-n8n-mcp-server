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

#[Title('Move n8n Credential to Project')]
class MoveCredentialTool extends Tool
{
    protected string $name = 'move-n8n-credential';

    public function description(): string
    {
        return 'Transfer an n8n credential to a different project.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'credentialId' => $schema->string()
                ->description('The n8n credential ID to move.')
                ->required(),
            'destinationProjectId' => $schema->string()
                ->description('The target project ID to move the credential into.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'credentialId'         => ['required', 'string'],
            'destinationProjectId' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();

        try {
            $result = N8nClient::credentials()->transfer(
                $validated['credentialId'],
                $validated['destinationProjectId']
            );

            return Response::structured(['success' => true, 'data' => $result]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
