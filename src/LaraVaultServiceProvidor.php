<?php
/**
 * @author: timbroder
 * Date: 4/11/16
 * @copyright 2015 Kidfund Inc
 */

namespace Kidfund\LaraVault;

use Exception;
use Illuminate\Support\ServiceProvider;
use Kidfund\ThinTransportVaultClient\TransitClient;
use Kidfund\ThinTransportVaultClient\VaultEncrypts;

class LaraVaultServiceProvidor extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
    }

    public static function getTransitClient()
    {
        $enabled = config('vault.enabled');

        if (!$enabled) {
            return;
        }

        $vaultAddr = config('vault.addr');
        $vaultToken = config('vault.token');

        if ($vaultToken == null || $vaultToken == 'none') {
            throw new Exception('Vault token must be configured');
        }

        $_client = new TransitClient($vaultAddr, $vaultToken);

        return $_client;
    }

    /**
     * Register the command.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(TransitClient::class, function () {
            return $this::getTransitClient();
        });

        $this->app->singleton(LaraVaultHasher::class, function ($app) {
            return new LaraVaultHasher($app[TransitClient::class]);
        });

        $this->app->singleton(VaultEncrypts::class, function ($app) {
            return $app[TransitClient::class];
        });
    }
}
