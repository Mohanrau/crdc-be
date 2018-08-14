<?php
namespace App\Providers;

use App\Interfaces\{
    Authorizations\RoleGroupInterface,
    Authorizations\RoleInterface,
    Dashboard\DashboardInterface,
    Masters\MasterDataInterface,
    Masters\MasterInterface,
    Modules\ModuleInterface,
    Staff\StaffInterface,
    Uploader\UploaderInterface,
    Users\UserInterface,
    Currency\CurrencyInterface,
    Languages\LanguageInterface,
    Settings\SettingsInterface,
};
use App\Repositories\{
    Authorizations\RoleGroupRepository,
    Authorizations\RoleRepository,
    Dashboard\DashboardRepository,
    Locations\EntityRepository,
    Locations\LocationRepository,
    Masters\MasterDataRepository,
    Masters\MasterRepository,
    Modules\ModuleRepository,
    Staff\StaffRepository,
    Uploader\UploaderRepository,
    Users\UserRepository,
    Locations\CountryRepository,
    Locations\StateRepository,
    Locations\CityRepository,
    Languages\LanguageRepository,
    Settings\SettingsRepository,
    Locations\ZoneRepository
};
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //Register User or staff Repositories and bind it to interface--------------------------------------------------
        $this->app->singleton(UserInterface::class, UserRepository::class);

        $this->app->singleton(StaffInterface::class, StaffRepository::class);

        //Register Module Repositories and bind it to interface---------------------------------------------------------
        $this->app->singleton(ModuleInterface::class, ModuleRepository::class);

        //RoleGroup and Role interfaces, Repositories ------------------------------------------------------------------
        $this->app->singleton(RoleGroupInterface::class, RoleGroupRepository::class);

        $this->app->singleton(RoleInterface::class, RoleRepository::class);

        //Master and MasterData interfaces, Repositories ---------------------------------------------------------------
        $this->app->singleton(MasterInterface::class, MasterRepository::class);

        $this->app->singleton(MasterDataInterface::class, MasterDataRepository::class);

        //Countries, Entities and Locations interfaces, Repositories----------------------------------------------------
        $this->app->singleton(CountryInterface::class, CountryRepository::class);

        $this->app->singleton(StateInterface::class, StateRepository::class);

        $this->app->singleton(CityInterface::class, CityRepository::class);

        $this->app->singleton(EntityInterface::class, EntityRepository::class);

        $this->app->singleton(LocationInterface::class, LocationRepository::class);

        //Uploader interfaces, Repositories-----------------------------------------------------------------------------
        $this->app->bindIf(UploaderInterface::class, UploaderRepository::class);

        //Currency Repository Interface---------------------------------------------------------------------------------
        $this->app->singleton(CurrencyInterface::class, CurrencyRepository::class);

        //Languages-----------------------------------------------------------------------------------------------------
        $this->app->bindIf(LanguageInterface::class, LanguageRepository::class);

        //Settings------------------------------------------------------------------------------------------------------
        $this->app->bindIf(SettingsInterface::class, SettingsRepository::class);
        
        //Dashboard-----------------------------------------------------------------------------------------------------
        $this->app->bindIf(DashboardInterface::class, DashboardRepository::class);
    }
}
