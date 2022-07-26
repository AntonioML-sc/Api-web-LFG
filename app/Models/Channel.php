<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\Uuids;

class Channel extends Model
{
    use Uuids;
    use HasFactory;
    
    protected $primaryKey = 'id';

    // relationships
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->using(ChannelUser::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
