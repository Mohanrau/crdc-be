<?php
namespace App\Models\Members;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Traits\{
    Member,
    Bonus
};
use App\Models\{
    Bonus\BonusSummary, Bonus\EnrollmentRank, Locations\Country, Masters\MasterData, Users\User, Bonus\TeamBonusRank
};
use App\Models\Members\Member as MemberModel;

class MemberTree extends Model
{
    use Member, Bonus;

    protected $table = 'member_trees';

    protected $fillable = [
        'user_id',
        'sponsor_parent_user_id',
        'sponsor_depth_level',
        'placement_parent_user_id',
        'placement_depth_level',
        'placement_position',
        'sponsor_position',
        'total_direct_downlines',
        'total_downlines',
        'total_active_left_downlines',
    ];

    protected $placementPositions = [
        0 => 'null',
        1 => 'left',
        2 => 'right'
    ];

    protected $depthRelations;

    /**
     * get the placement position array or value if $position is set
     *
     * @param int|null $position
     * @return array|false|int|string
     */
    public function getPlacementPosition(int $position = null)
    {
        return ($position == null)? $this->placementPositions :  $this->placementPositions[$position];
    }

    /**
     * get member details for a given memberTreeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->hasOne(MemberModel::class,'user_id','user_id')
            ->with('user');
    }

    public function memberWithoutUser()
    {
        return $this->hasOne(MemberModel::class,'user_id','user_id');
    }

    /**
     * get user for a given memberObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * get member details with ranking for a given memberTreeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function memberWithRank()
    {
        return $this->belongsTo(MemberModel::class,'user_id','user_id')
            ->with(['enrollmentRank','effectiveRank','highestRank']);
    }

    public function bonusSummary()
    {
        return $this->hasMany(BonusSummary::class, 'user_id', 'user_id');
    }

    /**
     * get member's placement children
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function placementChildren()
    {
        return $this->hasMany(MemberTree::class,'placement_parent_user_id','user_id')
            ->orderBy('placement_position');
            //->with('member');
    }

    /**
     * get member's sponsor children
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sponsorChildren()
    {
        return $this->hasMany(MemberTree::class,'sponsor_parent_user_id','user_id');
    }

    /**
     * recursively retrieve all the sponsor children on unlimited descendants
     *
     * @return $this
     */
    public function sponsorChildrenRecursive()
    {
        return $this->sponsorChildren()->with('sponsorChildrenRecursive');
    }

    /**
     * get member's placement children up to 10 depth
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function placementChildrenRecursive()
    {
        return $this->placementChildren()
                    ->with('placementChildren.
                    placementChildren.
                    placementChildren.
                    placementChildren.
                    placementChildren.
                    placementChildren.
                    placementChildren.
                    placementChildren');
    }

    /**
     * get member's placement children with depth level
     *
     * @param int $depth
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function placementChildrenRecursiveWithDepth(int $depth)
    {
        $this->depthRelations = 'placementChildren';

        for ($i = 0; $i <= $depth-4; $i++)
        {
            $this->depthRelations = $this->depthRelations.'.'.'placementChildren';
        }
        return $this->placementChildren()
            ->with($this->depthRelations);
    }

    /**
     * get the sponsor parent details for a given member
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(MemberTree::class, 'sponsor_parent_user_id', 'user_id')
            ->with('member');
    }

    /**
     * get the placement parent details for a given member
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function placement()
    {
        return $this->belongsTo(MemberTree::class, 'placement_parent_user_id', 'user_id')
            ->with('member');
    }
}
