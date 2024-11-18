<?php

namespace Larapps\BigcommerceApp\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BCStore extends Model
{
    use HasFactory;

    protected $table = "bc_stores";
    public $timestamps = true;

    public static function scopeRetrieveStore($query,$filter){
        if(isset($filter['store_hash'])){
            $query->where('store_hash', '=', $filter['store_hash']);
        }
        if(isset($filter['id'])){
            $query->where('id','=',$filter['id']);
        }
        if(isset($filter['is_removed'])){
            $query->where('is_removed','=',$filter['is_removed']);
        }
    }

    public static function getHashOnly($values){
        return str_replace("stores/","",$values);
    }

}
