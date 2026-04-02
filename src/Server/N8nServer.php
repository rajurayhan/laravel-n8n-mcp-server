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
use Raju\N8nMcp\Tools\Credential\ListCredentialsTool;
use Raju\N8nMcp\Tools\Credential\MoveCredentialTool;
use Raju\N8nMcp\Tools\Execution\DeleteExecutionsTool;
use Raju\N8nMcp\Tools\Execution\GetExecutionTool;
use Raju\N8nMcp\Tools\Execution\ListExecutionsTool;
use Raju\N8nMcp\Tools\Project\CreateProjectTool;
use Raju\N8nMcp\Tools\Project\DeleteProjectTool;
use Raju\N8nMcp\Tools\Project\ListProjectsTool;
use Raju\N8nMcp\Tools\Project\UpdateProjectTool;
use Raju\N8nMcp\Tools\Security\GenerateSecurityAuditTool;
use Raju\N8nMcp\Tools\SourceControl\PullSourceControlTool;
use Raju\N8nMcp\Tools\Tag\CreateTagTool;
use Raju\N8nMcp\Tools\Tag\DeleteTagTool;
use Raju\N8nMcp\Tools\Tag\GetTagTool;
use Raju\N8nMcp\Tools\Tag\ListTagsTool;
use Raju\N8nMcp\Tools\Tag\UpdateTagTool;
use Raju\N8nMcp\Tools\User\ChangeUserRoleTool;
use Raju\N8nMcp\Tools\User\CreateUserTool;
use Raju\N8nMcp\Tools\User\DeleteUserTool;
use Raju\N8nMcp\Tools\User\GetUserTool;
use Raju\N8nMcp\Tools\User\ListUsersTool;
use Raju\N8nMcp\Tools\Variable\CreateVariableTool;
use Raju\N8nMcp\Tools\Variable\DeleteVariableTool;
use Raju\N8nMcp\Tools\Variable\ListVariablesTool;
use Raju\N8nMcp\Tools\Webhook\TriggerWebhookTool;
use Raju\N8nMcp\Tools\Workflow\ActivateWorkflowTool;
use Raju\N8nMcp\Tools\Workflow\CreateWorkflowTool;
use Raju\N8nMcp\Tools\Workflow\DeactivateWorkflowTool;
use Raju\N8nMcp\Tools\Workflow\DeleteWorkflowTool;
use Raju\N8nMcp\Tools\Workflow\GetWorkflowTool;
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
    'Destructive operations (delete, move) require explicit confirmation before proceeding.'
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
        ActivateWorkflowTool::class,
        DeactivateWorkflowTool::class,
        MoveWorkflowTool::class,
        // WorkflowTag
        ListWorkflowTagsTool::class,
        UpdateWorkflowTagsTool::class,
        // Execution
        ListExecutionsTool::class,
        GetExecutionTool::class,
        DeleteExecutionsTool::class,
        // Credential
        ListCredentialsTool::class,
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
        // Variable
        ListVariablesTool::class,
        CreateVariableTool::class,
        DeleteVariableTool::class,
        // Tag
        ListTagsTool::class,
        GetTagTool::class,
        CreateTagTool::class,
        UpdateTagTool::class,
        DeleteTagTool::class,
        // Source Control
        PullSourceControlTool::class,
        // Security / Audit
        GenerateSecurityAuditTool::class,
        // Webhook
        TriggerWebhookTool::class,
    ];
}
