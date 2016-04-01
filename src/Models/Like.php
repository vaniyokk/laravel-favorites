<?php namespace Sugar\Likeable\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Model;

class Like extends Eloquent
{
	protected $table = 'likeable_likes';
	public $timestamps = true;
	protected $fillable = ['likeable_id', 'likeable_type', 'user_id'];

	public function likeable()
	{
		return $this->morphTo();
	}
}