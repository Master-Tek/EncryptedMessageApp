<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Helpers\EncryptionHelper;

class Message extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Check if a conversation exists between two users.
     *
     * @param  int  $currentUserId
     * @param  int  $recipientId
     * @return bool
     */
    public static function conversationExists($currentUserId, $recipientId)
    {
        return self::where(function ($query) use ($currentUserId, $recipientId) {
                $query->where('sender_id', $currentUserId)->where('receiver_id', $recipientId);
            })
            ->orWhere(function ($query) use ($currentUserId, $recipientId) {
                $query->where('sender_id', $recipientId)->where('receiver_id', $currentUserId);
            })
            ->exists();
    }

    protected $fillable = [
        'content', 'sender_id', 'receiver_id', 'read_at'
    ];

    protected $dates = ['deleted_at','read_at']; // For soft deletes

    public function setContentAttribute($value)
    {
        $key = env('APP_KEY');
        $this->attributes['content'] = EncryptionHelper::encryptText($value, $key);
    }

    public function getContentAttribute($value)
    {
        $key = env('APP_KEY');
        return EncryptionHelper::decryptText($value, $key);
    }

    public function getHumanizeCreatedAt() {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Relationship to the User model for the receiver
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function lastMessage() {
        return $this->belongsTo(Message::class, 'last_message_id');
    }

    public function messages() {
        return $this->hasMany(Message::class, 'id');
    }
}
