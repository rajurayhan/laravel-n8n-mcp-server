<?php

namespace Raju\N8nMcp\Tools\WorkflowTag;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use KayedSpace\N8n\Exceptions\N8nException;
use KayedSpace\N8n\Facades\N8nClient;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Title('Update n8n Workflow Tags')]
class UpdateWorkflowTagsTool extends Tool
{
    protected string $name = 'update-n8n-workflow-tags';

    public function description(): string
    {
        return 'Replace the full set of tags on an n8n workflow. Pass an array of tag ID strings. Passing an empty array removes all tags.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'workflowId' => $schema->string()
                ->description('The n8n workflow ID.')
                ->required(),
            'tagIds' => $schema->array()
                ->description('Array of tag ID strings to assign to the workflow. Replaces existing tags.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->all();

        $validator = Validator::make($arguments, [
            'workflowId' => ['required', 'string'],
            'tagIds'     => ['required', 'array'],
            'tagIds.*'   => ['string'],
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->first());
        }

        $validated = $validator->validated();

        try {
            $result = N8nClient::workflows()->updateTags(
                $validated['workflowId'],
                $validated['tagIds']
            );

            return Response::structured(['success' => true, 'data' => $result]);
        } catch (N8nException $e) {
            return Response::error($e->getMessage());
        }
    }
}
