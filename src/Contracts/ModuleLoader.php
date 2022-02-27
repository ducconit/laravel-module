<?php

namespace DNT\Module\Contracts;

use DNT\Module\Enums\ModuleStatus;

interface ModuleLoader
{
	public function all(): array;

	public function get(string $key): mixed;

	public function getStatus(Module|string $module): ModuleStatus|string;

	public function getPaths(): array;

	public function getByStatus(ModuleStatus $status): array;

	public function reload(): void;
}
