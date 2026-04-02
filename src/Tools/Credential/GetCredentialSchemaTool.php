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

#[Title('Get n8n Credential Schema')]
#[IsReadOnly]
#[IsIdempotent]
class GetCredentialSchemaTool extends Tool
{
    protected string $name = 'get-n8n-credential-schema';

    public function description(): string
    {
        return 'Retrieve the JSON schema for a specific n8n credential type (e.g. slackApi, httpBasicAuth, openAiApi). Use this before creating a credential to know which fields are required.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'credentialTypeName' => $schema->string()
                ->description('The n8n credential type name (e.g. slackApi, httpBasicAuth, googleSheetsOAuth2Api).')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'credentialTypeName' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        try {
            $schema = N8nClient::credentials()->schema($validator->validated()['credentialTypeName']);

            return Response::structured(['success' => true, 'data' => $schema]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
