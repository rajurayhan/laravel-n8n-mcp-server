<?php

namespace Raju\N8nMcp\Tools\Variable;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use KayedSpace\N8n\Exceptions\N8nException;
use KayedSpace\N8n\Facades\N8nClient;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Title('Delete Multiple n8n Variables')]
class DeleteManyVariablesTool extends Tool
{
    protected string $name = 'delete-many-n8n-variables';

    public function description(): string
    {
        return 'Delete multiple n8n environment variables at once by their IDs. Returns per-ID success/error results.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'ids' => $schema->array()
                ->description('Array of variable ID strings to delete.')
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
            $results = N8nClient::variables()->deleteMany($validator->validated()['ids']);

            return Response::structured(['success' => true, 'data' => $results]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
