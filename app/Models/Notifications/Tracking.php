<?php
namespace App\Models\Notifications;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    protected $table = 'notification_tracking';

    protected $fillable = [
        'user_id',
        'from',
        'to',
        'cc',
        'bcc',
        'channel',
        'subject',
        'body'
    ];

    /**
     * get user data for a given trackingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
