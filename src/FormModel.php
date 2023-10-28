<?php
/*
 *  -- BEGIN LICENSE BLOCK ----------------------------------
 *
 *  This file is part of dcCKEditorAddons, a plugin for DotClear2.
 *
 *  Licensed under the GPL version 2.0 license.
 *  See LICENSE file or
 *  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 *  -- END LICENSE BLOCK ------------------------------------
 */

declare(strict_types=1);

namespace Dotclear\Plugin\dcCKEditorAddons;

class FormModel
{
    private bool $is_admin = false;

    private bool $active = false;

    private bool $check_validity = false;

    private string $repository_path = '';

    private array $plugins = [];

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function setIsAdmin(bool $is_admin): self
    {
        $this->is_admin = $is_admin;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getCheckValidity(): bool
    {
        return $this->check_validity;
    }

    public function setCheckValidity(bool $check_validity): self
    {
        $this->check_validity = $check_validity;

        return $this;
    }

    public function getRepositoryPath(): string
    {
        return $this->repository_path;
    }

    public function setRepositoryPath(string $repository_path): self
    {
        $this->repository_path = $repository_path;

        return $this;
    }

    public function getPlugins(): array
    {
        return $this->plugins;
    }

    public function setPlugins(array $plugins): self
    {
        $this->plugins = $plugins;

        return $this;
    }
}
