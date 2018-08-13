<?php
namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\Token;

class Guest extends Model
{
    protected $table = 'guests';

    protected $fillable = [
        'referrer_user_id',
        'token_id',
        'unique_id',
        'login_code',
        'medium',
        'temp_data'
    ];

    /**
     * get user details for the given guestObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    /**
     * get token details for the given guestObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function token()
    {
        return $this->belongsTo(Token::class, 'token_id');
    }
}
