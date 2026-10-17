<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToStore;

class Customer extends Model
{
    use BelongsToStore;
}
