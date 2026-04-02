<?php

namespace Raju\N8nMcp\Tools\Execution;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use KayedSpace\N8n\Exceptions\ExecutionFailedException;
use KayedSpace\N8n\Exceptions\N8nException;
use KayedSpace\N8n\Facades\N8nClient;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Title('Wait for n8n Execution to Complete')]
class WaitForExecutionTool extends Tool
{
    protected string $name = 'wait-for-n8n-execution';

    public function description(): string
    {
        return 'Poll an n8n execution until it reaches a terminal state (success, error, crashed) or a timeout is reached. Returns the final execution state including node data.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'executionId' => $schema->integer()
                ->description('The n8n execution ID to poll.')
                ->required(),
            'timeout' => $schema->integer()
                ->description('Maximum seconds to wait before giving up. Default 60, max 300.')
                ->min(5)
                ->max(300)
                ->nullable(),
            'interval' => $schema->integer()
                ->description('Polling interval in seconds between status checks. Default 2, min 1.')
                ->min(1)
                ->max(30)
                ->nullable(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'executionId' => ['required', 'integer'],
            'timeout'     => ['nullable', 'integer', 'min:5', 'max:300'],
            'interval'    => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();

        try {
            $result = N8nClient::executions()->wait(
                (int) $validated['executionId'],
                (int) ($validated['timeout'] ?? 60),
                (int) ($validated['interval'] ?? 2)
            );

            return Response::structured(['success' => true, 'data' => $result]);
        } catch (ExecutionFailedException $e) {
            return Response::error('Execution failed or timed out: ' . $e->getMessage());
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
