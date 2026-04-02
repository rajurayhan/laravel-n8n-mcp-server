<?php

namespace Raju\N8nMcp\Tools\Webhook;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use KayedSpace\N8n\Exceptions\N8nException;
use KayedSpace\N8n\Facades\N8nClient;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Title('Trigger n8n Webhook')]
class TriggerWebhookTool extends Tool
{
    protected string $name = 'trigger-n8n-webhook';

    public function description(): string
    {
        return 'Trigger an n8n workflow via its webhook path. The webhook must be active on the n8n instance. Payload is passed as the request body.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'path' => $schema->string()
                ->description('The webhook path as configured in the n8n Webhook node (e.g. my-workflow or /production/my-workflow).')
                ->required(),
            'payload' => $schema->object()
                ->description('Optional JSON payload to send to the webhook.')
                ->nullable(),
            'async' => $schema->boolean()
                ->description('When true, the webhook is triggered asynchronously via a queue job and returns immediately. Defaults to false (synchronous).')
                ->nullable(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'path'    => ['required', 'string'],
            'payload' => ['nullable', 'array'],
            'async'   => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();
        $payload   = $validated['payload'] ?? [];

        try {
            $client = N8nClient::webhooks();

            if (!empty($validated['async'])) {
                $client = $client->async();
            } else {
                $client = $client->sync();
            }

            $result = $client->request($validated['path'], $payload);

            return Response::structured(['success' => true, 'data' => $result]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
