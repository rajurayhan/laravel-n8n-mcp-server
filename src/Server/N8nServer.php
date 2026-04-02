<?php
/*
 * This file is part of the Laravel n8n MCP Server package.
 *
 * Copyright (c) 2026 Raju Rayhan
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Raju\N8nMcp\Server;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Raju\N8nMcp\Tools\Credential\CreateCredentialTool;
use Raju\N8nMcp\Tools\Credential\DeleteCredentialTool;
use Raju\N8nMcp\Tools\Credential\GetCredentialSchemaTool;
use Raju\N8nMcp\Tools\Credential\GetCredentialTool;
use Raju\N8nMcp\Tools\Credential\ListCredentialsTool;
use Raju\N8nMcp\Tools\Credential\MoveCredentialTool;
use Raju\N8nMcp\Tools\Execution\DeleteExecutionsTool;
use Raju\N8nMcp\Tools\Execution\GetExecutionTool;
use Raju\N8nMcp\Tools\Execution\ListExecutionsTool;
use Raju\N8nMcp\Tools\Execution\WaitForExecutionTool;
use Raju\N8nMcp\Tools\Project\AddProjectUsersTool;
use Raju\N8nMcp\Tools\Project\ChangeProjectUserRoleTool;
use Raju\N8nMcp\Tools\Project\CreateProjectTool;
use Raju\N8nMcp\Tools\Project\DeleteProjectTool;
use Raju\N8nMcp\Tools\Project\ListProjectsTool;
use Raju\N8nMcp\Tools\Project\RemoveProjectUserTool;
use Raju\N8nMcp\Tools\Project\UpdateProjectTool;
use Raju\N8nMcp\Tools\Security\GenerateSecurityAuditTool;
use Raju\N8nMcp\Tools\SourceControl\PullSourceControlTool;
use Raju\N8nMcp\Tools\Tag\CreateManyTagsTool;
use Raju\N8nMcp\Tools\Tag\CreateTagTool;
use Raju\N8nMcp\Tools\Tag\DeleteManyTagsTool;
use Raju\N8nMcp\Tools\Tag\DeleteTagTool;
use Raju\N8nMcp\Tools\Tag\GetTagTool;
use Raju\N8nMcp\Tools\Tag\ListTagsTool;
use Raju\N8nMcp\Tools\Tag\UpdateTagTool;
use Raju\N8nMcp\Tools\User\ChangeUserRoleTool;
use Raju\N8nMcp\Tools\User\CreateUserTool;
use Raju\N8nMcp\Tools\User\DeleteUserTool;
use Raju\N8nMcp\Tools\User\GetUserTool;
use Raju\N8nMcp\Tools\User\ListUsersTool;
use Raju\N8nMcp\Tools\Variable\CreateManyVariablesTool;
use Raju\N8nMcp\Tools\Variable\CreateVariableTool;
use Raju\N8nMcp\Tools\Variable\DeleteManyVariablesTool;
use Raju\N8nMcp\Tools\Variable\DeleteVariableTool;
use Raju\N8nMcp\Tools\Variable\ListVariablesTool;
use Raju\N8nMcp\Tools\Variable\UpdateVariableTool;
use Raju\N8nMcp\Tools\Webhook\TriggerWebhookTool;
use Raju\N8nMcp\Tools\Workflow\ActivateManyWorkflowsTool;
use Raju\N8nMcp\Tools\Workflow\ActivateWorkflowTool;
use Raju\N8nMcp\Tools\Workflow\CreateWorkflowTool;
use Raju\N8nMcp\Tools\Workflow\DeactivateManyWorkflowsTool;
use Raju\N8nMcp\Tools\Workflow\DeactivateWorkflowTool;
use Raju\N8nMcp\Tools\Workflow\DeleteManyWorkflowsTool;
use Raju\N8nMcp\Tools\Workflow\DeleteWorkflowTool;
use Raju\N8nMcp\Tools\Workflow\ExportWorkflowsTool;
use Raju\N8nMcp\Tools\Workflow\GetWorkflowTool;
use Raju\N8nMcp\Tools\Workflow\ImportWorkflowsTool;
use Raju\N8nMcp\Tools\Workflow\ListWorkflowsTool;
use Raju\N8nMcp\Tools\Workflow\MoveWorkflowTool;
use Raju\N8nMcp\Tools\Workflow\SearchWorkflowsByWebhookTool;
use Raju\N8nMcp\Tools\Workflow\UpdateWorkflowTool;
use Raju\N8nMcp\Tools\WorkflowTag\ListWorkflowTagsTool;
use Raju\N8nMcp\Tools\WorkflowTag\UpdateWorkflowTagsTool;

#[Name('n8n MCP Server')]
#[Version('1.0.0')]
#[Instructions(
    'Use this server to manage n8n workflows, executions, credentials, users, projects, variables, tags, and webhooks. ' .
    'Prefer list queries before fetching individual records. ' .
    'For workflow creation, always include at minimum one Start node in the nodes array. ' .
    'Destructive operations (delete, move) require explicit confirmation before proceeding. ' .
    'Bulk operations (activateMany, deactivateMany, deleteMany) return per-ID results so partial failures are visible.'
)]
class N8nServer extends Server
{
    protected array $tools = [
        // Workflow
        ListWorkflowsTool::class,
        GetWorkflowTool::class,
        SearchWorkflowsByWebhookTool::class,
        CreateWorkflowTool::class,
        UpdateWorkflowTool::class,
        DeleteWorkflowTool::class,
        DeleteManyWorkflowsTool::class,
        ActivateWorkflowTool::class,
        ActivateManyWorkflowsTool::class,
        DeactivateWorkflowTool::class,
        DeactivateManyWorkflowsTool::class,
        MoveWorkflowTool::class,
        ExportWorkflowsTool::class,
        ImportWorkflowsTool::class,
        // WorkflowTag
        ListWorkflowTagsTool::class,
        UpdateWorkflowTagsTool::class,
        // Execution
        ListExecutionsTool::class,
        GetExecutionTool::class,
        WaitForExecutionTool::class,
        DeleteExecutionsTool::class,
        // Credential
        ListCredentialsTool::class,
        GetCredentialTool::class,
        GetCredentialSchemaTool::class,
        CreateCredentialTool::class,
        DeleteCredentialTool::class,
        MoveCredentialTool::class,
        // User
        ListUsersTool::class,
        GetUserTool::class,
        CreateUserTool::class,
        ChangeUserRoleTool::class,
        DeleteUserTool::class,
        // Project
        ListProjectsTool::class,
        CreateProjectTool::class,
        UpdateProjectTool::class,
        DeleteProjectTool::class,
        AddProjectUsersTool::class,
        ChangeProjectUserRoleTool::class,
        RemoveProjectUserTool::class,
        // Variable
        ListVariablesTool::class,
        CreateVariableTool::class,
        CreateManyVariablesTool::class,
        UpdateVariableTool::class,
        DeleteVariableTool::class,
        DeleteManyVariablesTool::class,
        // Tag
        ListTagsTool::class,
        GetTagTool::class,
        CreateTagTool::class,
        CreateManyTagsTool::class,
        UpdateTagTool::class,
        DeleteTagTool::class,
        DeleteManyTagsTool::class,
        // Source Control
        PullSourceControlTool::class,
        // Security / Audit
        GenerateSecurityAuditTool::class,
        // Webhook
        TriggerWebhookTool::class,
    ];
}
