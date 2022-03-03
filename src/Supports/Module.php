<?php

namespace DNT\Module\Supports;

use DNT\Json\Json;
use DNT\Module\Contracts\Module as ModuleContract;
use DNt\Module\Contracts\ModuleLoader;
use DNT\Module\Enums\ModuleStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

class Module implements ModuleContract
{
	use Macroable;

	protected ModuleLoader $loader;

	/**
	 * The path to module.json
	 * @var string
	 */
	protected string $path;

	protected array $_info = [];

	protected bool $loaded = false;

	public function __construct(string $path)
	{
		$this->path = $path;
		$this->_loadInfo();
	}

	protected function _loadInfo()
	{
		if ($this->loaded) {
			return;
		}
		$this->_info = Json::make($this->path)->all();
	}

	public function reload(): static
	{
		$this->loaded = false;
		$this->_loadInfo();
		return $this;
	}

	public function getStatus(): string
	{
		return ModuleStatus::toString($this->isEnable());
	}

	public function isEnable(): bool
	{
		return $this->get('enable', false);
	}

	public function get(string $key, mixed $default = null): mixed
	{
		return Arr::get($this->_info, $key, $default);
	}

	public function getKey(): string
	{
		return $this->get('key', '');
	}

	public function getName(): string
	{
		return $this->get('name', '');
	}

	public function getProviders(): array
	{
		return $this->get('providers', []);
	}

	public function getAliases(): array
	{
		return $this->get('aliases', []);
	}

	public function getFiles(): array
	{
		return $this->get('files', []);
	}

	public function getPath(string $path = null): string
	{
		$dir = dirname($this->path);
		return $path ? Str::finish($dir, DIRECTORY_SEPARATOR) . $path : $dir;
	}

	public function getFileInfo(): string
	{
		return $this->path;
	}

	public function getLangPaths(): array
	{
		return $this->get('langs', []);
	}

	public function getViewPaths(): array
	{
		return $this->get('views', []);
	}
}
