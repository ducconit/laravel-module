<?php

namespace DNT\Module\Contracts;

interface Module
{
	public function getStatus(): string;

	public function getPath(): string;

	public function getFileInfo(): string;

	public function isEnable(): bool;

	public function reload(): Module;

	public function getName(): string;

	public function getKey(): string;

	public function getLangPaths(): array;

	public function getViewPaths(): array;

	public function getProviders(): array;

	public function getAliases(): array;

	public function get(string $key, mixed $default = null): mixed;
}
