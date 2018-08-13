<?php
namespace App\Models\Bonus;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Traits\HasAudit;

class TeamBonusRank extends Model
{
    use HasAudit;

    protected $table = 'team_bonus_ranks';

    protected $fillable = [
        "id",
        "rank_code",
        "rank_name",
        "min_ps",
        "no_of_lines",
        "min_line_rank",
        "line_rank_count",
        "min_payleg_gcv",
        "max_gcv_bf",
        "max_payout",
        "status"
    ];
}
