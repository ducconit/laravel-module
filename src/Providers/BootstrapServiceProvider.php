<?php

namespace DNT\Module\Providers;

use DNT\Module\Contracts\Management;
use DNT\Module\Contracts\Module as ModuleContract;
use DNt\Module\Contracts\ModuleLoader as Loader;
use DNT\Module\Supports\Manager;
use DNT\Module\Supports\Module;
use DNT\Module\Supports\ModuleLoader;
use Illuminate\Support\ServiceProvider;

class BootstrapServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->loadConfig();
		$this->registerService();
		$this->__registerModules();
	}

	private function loadConfig()
	{
		$this->mergeConfigFrom(dirname(__DIR__, 2) . '/config/module.php', 'module');
	}

	private function registerService(): void
	{
		$this->app->singleton(Loader::class, function ($app) {
			return new ModuleLoader($app['config']);
		});

		$this->app->bind(ModuleContract::class, Module::class);

		$this->app->singleton(Management::class, function ($app) {
			$manager = new Manager($app);
			$manager->setLoader($app[Loader::class]);
			return $manager;
		});
	}

	private function __registerModules(): void
	{
		$this->app->make(Management::class)->register();
	}

	public function boot()
	{
		$this->__bootModules();

	}

	private function __bootModules(): void
	{
		$this->app->make(Management::class)->boot();
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides(): array
	{
		return [
			Loader::class,
			Module::class,
			Management::class,
		];
	}
}
