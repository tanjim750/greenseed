<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Order;
use App\Models\Product;
use App\Models\Size;

class OrderDetails extends Model
{
    use HasFactory;
    // use SoftDeletes;
    protected $guarded=[];

    public function product(){

        return $this->belongsTo(Product::class);
    }


    public function order(){

        return $this->belongsTo(Order::class);
    }
    
    public function variation(){

        return $this->belongsTo(Variation::class);
    }

}
