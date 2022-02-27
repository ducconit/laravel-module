<?php

namespace DNT\Module\Contracts;

interface Management
{
	/**
	 * Get all modules
	 * @return array
	 */
	public function all(): array;

	/**
	 * Get module by name
	 * @param string $name
	 * @return null|Module
	 */
	public function get(string $name): ?Module;

	/**
	 * Add module to loader
	 * @param string $path
	 * @return Module
	 */
	public function addModule(string $path): Module;

	/**
	 * Enable modules
	 * @param Module|string ...$module
	 * @return Management
	 */
	public function enable(Module|string ...$module): Management;

	/**
	 * Disable modules
	 * @param Module|string ...$module
	 * @return Management
	 */
	public function disable(Module|string ...$module): Management;

	/**
	 * Get configuration of module management
	 * @param string $key
	 * @return mixed
	 */
	public function config(string $key): mixed;

	/**
	 * Load and register modules
	 */
	public function register(): void;
}
