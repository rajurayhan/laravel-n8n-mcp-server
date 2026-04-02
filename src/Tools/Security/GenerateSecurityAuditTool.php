<?php

namespace Raju\N8nMcp\Tools\Security;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use KayedSpace\N8n\Exceptions\N8nException;
use KayedSpace\N8n\Facades\N8nClient;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Title('Generate n8n Security Audit')]
class GenerateSecurityAuditTool extends Tool
{
    protected string $name = 'generate-n8n-security-audit';

    public function description(): string
    {
        return 'Run a security audit of the n8n instance and return diagnostics by category. Requires owner/admin privileges.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'categories' => $schema->array()
                ->description('Optional list of audit categories to include (e.g. credentials, workflows, nodes). Omit to run all categories.')
                ->nullable(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'categories'   => ['nullable', 'array'],
            'categories.*' => ['string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();
        $options   = [];

        if (!empty($validated['categories'])) {
            $options['categories'] = $validated['categories'];
        }

        try {
            $audit = N8nClient::audit()->generate($options);

            return Response::structured(['success' => true, 'data' => $audit]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
