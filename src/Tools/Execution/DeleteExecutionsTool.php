<?php

namespace Raju\N8nMcp\Tools\Execution;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use KayedSpace\N8n\Exceptions\N8nException;
use KayedSpace\N8n\Facades\N8nClient;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Title('Delete n8n Executions')]
class DeleteExecutionsTool extends Tool
{
    protected string $name = 'delete-n8n-executions';

    public function description(): string
    {
        return 'Delete one or more n8n execution records by their numeric IDs.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'ids' => $schema->array()
                ->description('Array of numeric execution IDs to delete.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $ids = array_map('intval', $validator->validated()['ids']);

        try {
            if (count($ids) === 1) {
                $result = N8nClient::executions()->delete($ids[0]);
            } else {
                $result = N8nClient::executions()->deleteMany($ids);
            }

            return Response::structured(['success' => true, 'data' => $result]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
