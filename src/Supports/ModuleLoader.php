<?php

namespace DNT\Module\Supports;

use DNT\Json\Json;
use DNT\Module\Contracts\Module;
use DNT\Module\Contracts\ModuleLoader as Contract;
use DNT\Module\Enums\ModuleStatus;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

class ModuleLoader implements Contract
{
	use Macroable;

	/**
	 * All the registered paths to JSON translation files.
	 *
	 * @var array
	 */
	protected array $_jsonPaths = [];

	protected array $_modules = [];

	protected bool $loaded = false;

	/**
	 * List module active
	 * @var array
	 */
	protected array $_moduleActive = [];

	/**
	 * List module deactivate
	 * @var array
	 */
	protected array $_moduleDeactivate = [];

	/**
	 * The path save status of modules
	 * @var string
	 */
	protected string $_statusFile;

	public function __construct(Repository $config)
	{
		$this->_statusFile = $config['module.status_file'];
		$this->__resolvePath($config['module.path']);
	}

	private function __resolvePath(string $path)
	{
		if (!file_exists($path)) {
			@mkdir($path, 666, true);
		}
		$this->addJsonPath($path);
	}

	/**
	 * Add json paths
	 * @param string $path
	 */
	public function addJsonPath(string $path): Contract
	{
		$this->_jsonPaths[] = Str::finish($path, DIRECTORY_SEPARATOR);
		$this->loaded = false;
		return $this;
	}

	/**
	 * Get status of module
	 * @param Module|string $module
	 * @return ModuleStatus|string
	 */
	public function getStatus(Module|string $module): ModuleStatus|string
	{
		if (is_string($module)) {
			return ModuleStatus::toString($this->_modules[$module]["enable"]);
		}
		return $module->getStatus();
	}

	public function get(string $key): mixed
	{
		return @$this->all()[$key];
	}

	public function all(): array
	{
		$this->_readAndBinding();
		return $this->_modules;
	}

	protected function _readAndBinding(): void
	{
		if ($this->loaded) {
			return;
		}
		$modules = $this->_load();
		$result = [];
		foreach ($modules as $module) {
			if (isset($module['key']) && $module['key']) {
				$result[$module['key']] = ModuleStatus::toString(@$module['enable']);
			}
		}

		Json::make($this->_statusFile, true)->setAttributes($result)->save();
		[$this->_moduleActive, $this->_moduleDeactivate] = Collection::make($modules)->partition(function ($module) {
			return ModuleStatus::isEnable(@$module['enable']);
		})->toArray();
	}

	/**
	 * Read and return content json paths
	 * @return array
	 */
	protected function _load(): array
	{
		$result = [];
		$files = [];
		foreach ($this->_jsonPaths as $path) {
			$files = array_merge($files, glob($path . DIRECTORY_SEPARATOR . "*" . DIRECTORY_SEPARATOR . "module.json"));
		}
		$json = new Json();
		foreach ($files as $file) {
			$result[$file] = $json->setPath($file)->all();
		}
		return $this->_modules = $result;
	}

	public function moduleActives(): array
	{
		return $this->getByStatus(ModuleStatus::ENABLE);
	}

	/**
	 * @param ModuleStatus $status
	 * @return array
	 */
	public function getByStatus(ModuleStatus $status): array
	{
		$this->_readAndBinding();
		if ($status == ModuleStatus::ENABLE) {
			return $this->_moduleActive;
		}
		return $this->_moduleDeactivate;
	}

	public function reload(): void
	{
		$this->loaded = false;
		$this->_load();
	}

	/**
	 * Get paths
	 * @return array
	 */
	public function getPaths(): array
	{
		return $this->_jsonPaths;
	}
}
