<?php
namespace App\Models\Campaigns;

use App\Models\{
    Campaigns\Campaign,
    Campaigns\EsacVoucherType,
    Campaigns\EsacVoucherSubType,
    Bonus\TeamBonusRank,
    Bonus\EnrollmentRank,
    Locations\Location,
    Products\ProductCategory,
    Products\Product,
    Kitting\Kitting,
    Masters\MasterData
};
use Illuminate\Database\Eloquent\Model;

class CampaignRule extends Model
{
    protected $table = 'campaign_rules';

    protected $fillable = [
        'parent_id',
        'campaign_id',
        'name',
        'report_title',
        'qualify_member_status',
        'sale_item_quantity',
        'team_bonus_rank_quantity',
        'enrollment_rank_quantity',
        'from_sale_item_level',
        'to_sale_item_level',
        'from_team_bonus_rank_level',
        'to_team_bonus_rank_level',
        'from_enrollment_rank_level',
        'to_enrollment_rank_level',
        'from_cv',
        'to_cv',
        'point',
        'point_value',
        'point_value_multiplier',
        'min_point_value',
        'max_point_value',
        'voucher',
        'voucher_type_id',
        'voucher_sub_type_id',
        'voucher_value',
        'voucher_value_multiplier',
        'min_voucher_value',
        'max_voucher_value',
        'ewallet_money',
        'ewallet_money_value',
        'ewallet_money_value_multiplier',
        'min_ewallet_money_value',
        'max_ewallet_money_value'
    ];
    
    /**
     * get campaign for a given CampaignRuleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id');
    }

    /**
     * get qualify team bonus ranks for a given CampaignRuleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function campaignRuleQualifyTeamBonusRanks()
    {   
        return $this
            ->belongsToMany(TeamBonusRank::class, 'campaign_rule_qualify_team_bonus_ranks', 'campaign_rule_id', 'team_bonus_rank_id')
            ->withTimestamps();
    }

    /**
     * get qualify enrollment ranks for a given CampaignRuleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function campaignRuleQualifyEnrollmentRanks()
    {
        return $this
            ->belongsToMany(EnrollmentRank::class, 'campaign_rule_qualify_enrollment_ranks', 'campaign_rule_id', 'enrollment_rank_id')
            ->withTimestamps();
    }

    /**
     * get esac voucher type for a given CampaignRuleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function voucherType()
    {
        return $this->belongsTo(EsacVoucherType::class, 'voucher_type_id', 'id');
    }

    /**
     * get esac voucher sub type for a given CampaignRuleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function voucherSubType()
    {
        return $this->belongsTo(EsacVoucherSubType::class, 'voucher_sub_type_id', 'id');
    }

    /**
     * get location for a given CampaignRuleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function campaignRuleLocations()
    {
        return $this
            ->belongsToMany(Location::class, 'campaign_rule_locations', 'campaign_rule_id', 'location_id')
            ->withTimestamps();
    }

    /**
     * get product categories for a given CampaignRuleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function campaignRuleProductCategories()
    {
        return $this
            ->belongsToMany(ProductCategory::class, 'campaign_rule_product_categories', 'campaign_rule_id', 'product_category_id')
            ->withTimestamps();
    }

    /**
     * get products for a given CampaignRuleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function campaignRuleProducts()
    {
        return $this
            ->belongsToMany(Product::class, 'campaign_rule_products', 'campaign_rule_id', 'product_id')
            ->withTimestamps();
    }

    /**
     * get kittings for a given CampaignRuleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function campaignRuleKittings()
    {
        return $this
            ->belongsToMany(Kitting::class, 'campaign_rule_kittings', 'campaign_rule_id', 'kitting_id')
            ->withTimestamps();
    }

    /**
     * get sale types for a given CampaignRuleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function campaignRuleSaleTypes()
    {
        return $this
            ->belongsToMany(MasterData::class, 'campaign_rule_sale_types', 'campaign_rule_id', 'sale_type_id')
            ->withTimestamps();
    }

    /**
     * get team bonus ranks for a given CampaignRuleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function campaignRuleTeamBonusRanks()
    {
        return $this
            ->belongsToMany(TeamBonusRank::class, 'campaign_rule_team_bonus_ranks', 'campaign_rule_id', 'team_bonus_rank_id')
            ->withTimestamps();
    }

    /**
     * get enrollment ranks for a given CampaignRuleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function campaignRuleEnrollmentRanks()
    {
        return $this
            ->belongsToMany(EnrollmentRank::class, 'campaign_rule_enrollment_ranks', 'campaign_rule_id', 'enrollment_rank_id')
            ->withTimestamps();
    }
}
