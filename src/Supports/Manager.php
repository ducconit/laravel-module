<?php

namespace DNT\Module\Supports;

use DNT\Json\Json;
use DNT\Module\Contracts\Management;
use DNT\Module\Contracts\Module;
use DNt\Module\Contracts\ModuleLoader;
use DNT\Module\Exceptions\ModuleNotFound;
use Exception;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

class Manager implements Management
{
	use Macroable;

	/**
	 * @var array Module
	 */
	protected array $_modules = [];

	protected ModuleLoader $_loader;

	protected Repository $_config;

	protected Application $_container;

	public function __construct(Application $container)
	{
		$this->_container = $container;
		$this->_config = $container->make('config');
	}

	public function setLoader(ModuleLoader $loader)
	{
		$this->_loader = $loader;
	}

	public function config(string $key): mixed
	{
		return $this->_config->get('module.' . $key);
	}

	public function register(): void
	{
		foreach ($this->_loader->all() as $path => $_) {
			$module = $this->addModule($path);
			$this->_modules[$module->getKey()] = $module;
		}
	}

	public function addModule(string $path): Module
	{
		$module = new \DNT\Module\Supports\Module($path);
		if (!$module->isEnable()) {
			return $module;
		}
		/**
		 * Load files
		 */
		foreach ($module->getFiles() as $file) {
			$path = Str::finish($module->getPath(), DIRECTORY_SEPARATOR) . $file;
			if (file_exists($path) && is_file($path) && pathinfo($path, PATHINFO_EXTENSION) == "php") {
				require_once $path;
			}
		}
		/**
		 * Register providers
		 */
		(new ProviderRepository($this->_container, new Filesystem(), $this->_getCachedModulesPath($module->getKey())))
			->load($module->getProviders());
		/**
		 * Load aliases
		 */
		foreach ($module->getAliases() as $alias => $abstract) {
			$this->_container->alias($abstract, $alias);
		}
		/**
		 * dispatch event register
		 */
		$this->_container['events']->dispatch(sprintf('module.%s.register', $module->getKey()), [$this]);
		return $module;
	}

	protected function _getCachedModulesPath(string $name): string
	{
		return Str::replaceLast('services.php', Str::slug($name) . '_module.php', $this->_container->getCachedServicesPath());
	}

	public function boot(): void
	{
		foreach ($this->_modules as $module) {
			if (!$module->isEnable()) {
				continue;
			}
			/**
			 * Register namespace view module
			 */
			foreach ($module->getViewPaths() as $path) {
				$this->_container->make('view')->addNamespace($module->getKey(), $module->getPath($path));
			}
			/**
			 * Register namespace translate
			 */
			foreach ($module->getLangPaths() as $path) {
				$this->_container->make('translator')->addNamespace($module->getKey(), $module->getPath($path));
				$this->_container->make('translator')->addJsonPath($module->getPath($path));
			}
			/**
			 * dispatch event boot
			 */
			$this->_container['events']->dispatch(sprintf('module.%s.boot', $module->getKey()), [$this]);
		}
	}

	public function all(): array
	{
		return $this->_modules;
	}

	/**
	 * @throws ModuleNotFound
	 */
	public function enable(Module|string ...$modules): Management
	{
		$json = new Json();
		foreach ($modules as $module) {
			$module = $this->get($module);
			if ($module->isEnable()) {
				continue;
			}
			$json = $json->setPath($module->getFileInfo())->set('enable', true);
			$this->_container['events']->dispatch(sprintf('module.%s.enable', $module->getKey()), $module);
		}
		if ($json->getPath()) {
			$json->save();
		}
		$this->_loader->reload();
		return $this;
	}

	/**
	 * @throws ModuleNotFound
	 */
	public function get(Module|string $module): Module
	{
		if (is_string($module)) {
			try {
				$module = $this->_modules[$module];
			} catch (Exception $exception) {
				throw new ModuleNotFound("Module: " . $module . " not found");
			}
		}
		return $module;
	}

	public function disable(Module|string ...$modules): Management
	{
		$json = new Json();
		foreach ($modules as $module) {
			$module = $this->get($module);
			if (!$module->isEnable()) {
				continue;
			}
			$json = $json->setPath($module->getFileInfo())->set('enable', false);
			$this->_container['events']->dispatch(sprintf('module.%s.disable', $module->getKey()), $module);
		}
		if ($json->getPath()) {
			$json->save();
		}
		$this->_loader->reload();
		return $this;
	}
}
