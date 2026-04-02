<?php

namespace Raju\N8nMcp\Tools\Tag;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use KayedSpace\N8n\Exceptions\N8nException;
use KayedSpace\N8n\Facades\N8nClient;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Title('Delete Multiple n8n Tags')]
class DeleteManyTagsTool extends Tool
{
    protected string $name = 'delete-many-n8n-tags';

    public function description(): string
    {
        return 'Delete multiple n8n tags at once. The tags are removed from all workflows they were applied to. Returns per-ID success/error results.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'ids' => $schema->array()
                ->description('Array of tag ID strings to delete.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        try {
            $results = N8nClient::tags()->deleteMany($validator->validated()['ids']);

            return Response::structured(['success' => true, 'data' => $results]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
