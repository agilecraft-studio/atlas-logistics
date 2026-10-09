<?php

/*
@copyright

Fleet Manager v7.0

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

*/

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = "messages";
    protected $fillable = ['from_user', 'to_user', 'content', 'read_at'];

    public function fromUser()
    {
        return $this->hasOne("App\Model\User", "id", "from_user")->withTrashed();
    }

    public function toUser()
    {
        return $this->hasOne("App\Model\User", "id", "to_user")->withTrashed();
    }

}
