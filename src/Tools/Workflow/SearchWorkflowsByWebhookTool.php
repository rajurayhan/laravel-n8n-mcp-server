<?php

namespace Raju\N8nMcp\Tools\Workflow;

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
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Title('Search n8n Workflows by Webhook')]
#[IsReadOnly]
#[IsIdempotent]
#[IsOpenWorld]
class SearchWorkflowsByWebhookTool extends Tool
{
    protected string $name = 'search-n8n-workflows-by-webhook';

    public function description(): string
    {
        return 'Find n8n workflows that contain a Webhook node matching the given path or URL substring. Fetches full workflow details for each workflow to inspect nodes.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'webhookPath' => $schema->string()
                ->description('Webhook path or URL substring to search for (e.g. "my-webhook" or "https://..."). Case-insensitive.')
                ->required(),
            'activeOnly' => $schema->boolean()
                ->description('When true, only search active workflows. Defaults to false.')
                ->nullable(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'webhookPath' => ['required', 'string'],
            'activeOnly'  => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated    = $validator->validated();
        $searchPath   = mb_strtolower($validated['webhookPath']);
        $activeOnly   = (bool) ($validated['activeOnly'] ?? false);

        try {
            $filters   = $activeOnly ? ['active' => true] : [];
            $listData  = N8nClient::workflows()->list($filters);
            $workflows = is_array($listData['data'] ?? null) ? $listData['data'] : (is_array($listData) && array_is_list($listData) ? $listData : []);

            $matches = [];

            foreach ($workflows as $summary) {
                $workflowId = $summary['id'] ?? null;
                if ($workflowId === null) {
                    continue;
                }

                try {
                    $full  = N8nClient::workflows()->get((string) $workflowId, true);
                    $nodes = is_array($full['nodes'] ?? null) ? $full['nodes'] : [];

                    foreach ($nodes as $node) {
                        $nodeType = mb_strtolower((string) ($node['type'] ?? ''));

                        if (!str_contains($nodeType, 'webhook')) {
                            continue;
                        }

                        $parameters = $node['parameters'] ?? [];
                        $path       = mb_strtolower((string) ($parameters['path'] ?? ''));
                        $url        = mb_strtolower((string) ($parameters['url'] ?? ''));

                        if (str_contains($path, $searchPath) || str_contains($url, $searchPath)) {
                            $matches[] = [
                                'workflowId'   => $workflowId,
                                'workflowName' => $summary['name'] ?? null,
                                'active'       => (bool) ($summary['active'] ?? false),
                                'nodeName'     => $node['name'] ?? null,
                                'nodeType'     => $node['type'] ?? null,
                                'webhookPath'  => $parameters['path'] ?? null,
                                'webhookUrl'   => $parameters['url'] ?? null,
                            ];
                            break;
                        }
                    }
                } catch (N8nException) {
                    // Skip workflows we cannot read
                    continue;
                }
            }

            return Response::structured([
                'success' => true,
                'data'    => $matches,
                'meta'    => ['matched' => count($matches), 'searched' => count($workflows)],
            ]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
