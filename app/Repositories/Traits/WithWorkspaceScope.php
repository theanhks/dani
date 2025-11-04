<?php

namespace App\Repositories\Traits;

trait WithWorkspaceScope
{
    protected bool $useWorkspaceScope = true;

    public function disableWorkspaceScope(): static
    {
        $this->useWorkspaceScope = false;
        return $this;
    }

    public function enableWorkspaceScope(): static
    {
        $this->useWorkspaceScope = true;
        return $this;
    }

    public function shouldUseWorkspaceScope(): bool
    {
        return $this->useWorkspaceScope;
    }

    protected function addWorkspaceScope(): void
    {
        if (! $this->shouldUseWorkspaceScope()) {
            return;
        }

        $workspaceId = optional(request()->route('workspace'))->id;

        if (!$workspaceId) {
            throw new \InvalidArgumentException('workspace_id is required but not present in route.');
        }

        $this->scopeQuery(function ($query) use ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        });
    }
}
