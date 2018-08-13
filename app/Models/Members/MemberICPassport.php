<?php
namespace App\Models\Members;

use App\Models\Users\User;
use Facades\App\Helpers\Classes\Uploader;
use Illuminate\Database\Eloquent\Model;

class MemberICPassport extends Model
{  
    protected $table = 'members_ic_passport';

    protected $appends = ['image_link'];

    protected $fillable = [
        'user_id',
        'type',
        'image_path',
        'verified_by'
    ];

    /**
     * return verified by - user details for a given memberObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the image url.
     *
     * @return string
     */
    public function getImageLinkAttribute()
    {
        return Uploader::getFileLink('file', 'member_ic_passport', $this->image_path);
    }
}
