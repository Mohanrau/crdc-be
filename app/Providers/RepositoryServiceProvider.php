<?php
namespace App\Providers;

use App\Interfaces\{
    Authorizations\RoleGroupInterface,
    Authorizations\RoleInterface,
    Dashboard\DashboardInterface,
    Enrollments\EnrollmentInterface,
    Locations\EntityInterface,
    Locations\LocationInterface,
    Masters\MasterDataInterface,
    Masters\MasterInterface,
    Modules\ModuleInterface,
    Sales\SaleExchangeInterface,
    Staff\StaffInterface,
    Uploader\UploaderInterface,
    Users\UserInterface,
    Locations\CountryInterface,
    Locations\StateInterface,
    Locations\CityInterface,
    Currency\CurrencyInterface,
    Products\ProductCategoryInterface,
    Products\ProductInterface,
    Kitting\KittingInterface,
    ProductAndKitting\ProductAndKittingInterface,
    Dummy\DummyInterface,
    Promotions\PromotionFreeItemsInterface,
    Languages\LanguageInterface,
    Members\MemberTreeInterface,
    Members\MemberInterface,
    General\CwSchedulesInterface,
    Sales\SaleInterface,
    Invoices\InvoiceInterface,
    Settings\SettingsInterface,
    Payments\PaymentInterface,
    Workflows\WorkflowInterface,
    Integrations\YonyouInterface,
    Bonus\BonusInterface,
    Virel\VirelInterface,
    FileManagement\SmartLibraryInterface,
    Stockists\StockistInterface,
    Integrations\CimbMposInterface,
    Shop\ShopProductAndKittingFilteringInterface,
    Shop\ShopFavoriteInterface,
    Shop\ShopCartInterface,
    EWallet\EWalletInterface,
    Campaigns\CampaignInterface,
    Campaigns\EsacVoucherTypeInterface,
    Campaigns\EsacVoucherSubTypeInterface,
    Campaigns\EsacVoucherInterface,
    Campaigns\EsacPromotionInterface,
    Shop\ShopSaleInterface,
    Locations\ZoneInterface
};
use App\Repositories\{
    Authorizations\RoleGroupRepository,
    Authorizations\RoleRepository,
    Dashboard\DashboardRepository,
    Enrollments\EnrollmentRepository,
    Integrations\YonyouRepository,
    Locations\EntityRepository,
    Locations\LocationRepository,
    Masters\MasterDataRepository,
    Masters\MasterRepository,
    Modules\ModuleRepository,
    Sales\SaleExchangeRepository,
    Shop\ShopFavoritesRepository,
    Staff\StaffRepository,
    Uploader\UploaderRepository,
    Users\UserRepository,
    Locations\CountryRepository,
    Locations\StateRepository,
    Locations\CityRepository,
    Currency\CurrencyRepository,
    Products\ProductCategoryRepository,
    Products\ProductRepository,
    Kitting\KittingRepository,
    ProductAndKitting\ProductAndKittingRepository,
    Dummy\DummyRepository,
    Promotions\PromotionFreeItemRepository,
    Languages\LanguageRepository,
    Members\MemberTreeRepository,
    Members\MemberRepository,
    General\CwSchedulesRepository,
    Sales\SaleRepository,
    Invoices\InvoiceRepository,
    Settings\SettingsRepository,
    Payments\PaymentRepository,
    Workflows\WorkflowRepository,
    Bonus\BonusRepository,
    Virel\VirelRepository,
    FileManagement\SmartLibraryRepository,
    Stockists\StockistRepository,
    Integrations\CimbMposRepository,
    Shop\ShopProductAndKittingFilteringRepository,
    Shop\ShopCartRepository,
    EWallet\EWalletRepository,
    Campaigns\CampaignRepository,
    Campaigns\EsacVoucherTypeRepository,
    Campaigns\EsacVoucherSubTypeRepository,
    Campaigns\EsacVoucherRepository,
    Campaigns\EsacPromotionRepository,
    Shop\ShopSaleRepository,
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

        $this->app->singleton(ZoneInterface::class, ZoneRepository::class);

        //Uploader interfaces, Repositories-----------------------------------------------------------------------------
        $this->app->bindIf(UploaderInterface::class, UploaderRepository::class);

        //Currency Repository Interface---------------------------------------------------------------------------------
        $this->app->singleton(CurrencyInterface::class, CurrencyRepository::class);

        //Products categories, products---------------------------------------------------------------------------------
        $this->app->bindIf(ProductCategoryInterface::class, ProductCategoryRepository::class);

        //Products------------------------------------------------------------------------------------------------------
        $this->app->bindIf(ProductInterface::class, ProductRepository::class);

        //Kitting-------------------------------------------------------------------------------------------------------
        $this->app->bindIf(KittingInterface::class, KittingRepository::class);

        //Product and Kitting-------------------------------------------------------------------------------------------
        $this->app->singleton(ProductAndKittingInterface::class, ProductAndKittingRepository::class);

        //DummyInterface------------------------------------------------------------------------------------------------
        $this->app->bindIf(DummyInterface::class, DummyRepository::class);

        //Promotion Free Items------------------------------------------------------------------------------------------
        $this->app->bindIf(PromotionFreeItemsInterface::class, PromotionFreeItemRepository::class);

        //Languages-----------------------------------------------------------------------------------------------------
        $this->app->bindIf(LanguageInterface::class, LanguageRepository::class);

        //Members-------------------------------------------------------------------------------------------------------
        $this->app->bindIf(MemberInterface::class, MemberRepository::class);

        $this->app->bindIf(MemberTreeInterface::class, MemberTreeRepository::class);

        //General-------------------------------------------------------------------------------------------------------
        $this->app->bindIf(CwSchedulesInterface::class, CwSchedulesRepository::class);

        //Sales and salesExchange---------------------------------------------------------------------------------------
        $this->app->bindIf(SaleInterface::class, SaleRepository::class);

        $this->app->bindIf(SaleExchangeInterface::class, SaleExchangeRepository::class);
        //Invoice-------------------------------------------------------------------------------------------------------
        $this->app->bindIf(InvoiceInterface::class, InvoiceRepository::class);

        //Payment-------------------------------------------------------------------------------------------------------
        $this->app->bindIf(PaymentInterface::class, PaymentRepository::class);

        //Settings------------------------------------------------------------------------------------------------------
        $this->app->bindIf(SettingsInterface::class, SettingsRepository::class);

        //Workflow------------------------------------------------------------------------------------------------------
        $this->app->bindIf(WorkflowInterface::class, WorkflowRepository::class);

        //Bonus---------------------------------------------------------------------------------------------------------
        $this->app->bindIf(BonusInterface::class, BonusRepository::class);

        //YonyouIntegration---------------------------------------------------------------------------------------------
        $this->app->bindIf(YonyouInterface::class, YonyouRepository::class);

        //Virel---------------------------------------------------------------------------------------------------------
        $this->app->bindIf(VirelInterface::class, VirelRepository::class);

        //Smart Library-------------------------------------------------------------------------------------------------
        $this->app->bindIf(SmartLibraryInterface::class, SmartLibraryRepository::class);

        //Stockist------------------------------------------------------------------------------------------------------
        $this->app->bindIf(StockistInterface::class, StockistRepository::class);

        //Shop Products And Kitting Filtering --------------------------------------------------------------------------
        $this->app->bindIf(ShopProductAndKittingFilteringInterface::class,
            ShopProductAndKittingFilteringRepository::class);

        //Shop Favorites -----------------------------------------------------------------------------------------------
        $this->app->bindIf(ShopFavoriteInterface::class, ShopFavoritesRepository::class);

        //Shop Cart ----------------------------------------------------------------------------------------------------
        $this->app->bindIf(ShopCartInterface::class, ShopCartRepository::class);

        //Shop Sales ----------------------------------------------------------------------------------------------------
        $this->app->bindIf(ShopSaleInterface::class, ShopSaleRepository::class);

        //EWallet-------------------------------------------------------------------------------------------------------
        $this->app->bindIf(EWalletInterface::class, EWalletRepository::class);

        //CIMB Mpos-----------------------------------------------------------------------------------------------------
        $this->app->bindIf(CimbMposInterface::class, CimbMposRepository::class);

        //Campaign------------------------------------------------------------------------------------------------------
        $this->app->bindIf(CampaignInterface::class, CampaignRepository::class);

        $this->app->bindIf(EsacVoucherTypeInterface::class, EsacVoucherTypeRepository::class);

        $this->app->bindIf(EsacVoucherSubTypeInterface::class, EsacVoucherSubTypeRepository::class);

        $this->app->bindIf(EsacVoucherInterface::class, EsacVoucherRepository::class);

        $this->app->bindIf(EsacPromotionInterface::class, EsacPromotionRepository::class);

        //Enrollment----------------------------------------------------------------------------------------------------
        $this->app->singleton(EnrollmentInterface::class, EnrollmentRepository::class);

        //Dashboard-----------------------------------------------------------------------------------------------------
        $this->app->bindIf(DashboardInterface::class, DashboardRepository::class);
    }
}
