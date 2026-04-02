<?php

namespace Raju\N8nMcp\Tools\SourceControl;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use KayedSpace\N8n\Exceptions\N8nException;
use KayedSpace\N8n\Facades\N8nClient;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Title('Pull n8n Source Control')]
class PullSourceControlTool extends Tool
{
    protected string $name = 'pull-n8n-source-control';

    public function description(): string
    {
        return 'Trigger a Git pull from the connected source control repository to sync workflows and credentials. Requires source control to be configured in the n8n instance.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'force' => $schema->boolean()
                ->description('Force the pull even if there are local changes (overwrites local state).')
                ->nullable(),
            'variables' => $schema->object()
                ->description('Optional variables to set during the pull operation.')
                ->nullable(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'force'     => ['nullable', 'boolean'],
            'variables' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();
        $payload   = array_filter([
            'force'     => $validated['force'] ?? null,
            'variables' => $validated['variables'] ?? null,
        ], fn ($v) => $v !== null);

        try {
            $result = N8nClient::sourceControl()->pull($payload);

            return Response::structured(['success' => true, 'data' => $result]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
