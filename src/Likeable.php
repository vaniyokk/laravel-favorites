<?php namespace Sugar\Favorites;

use Carbon\Carbon;
use Sugar\Favorites\Models\Like;

/**
 * Copyright (C) 2016 Gregory Claeyssens
 */
trait Likeable {

    /**
     * Boot the soft taggable trait for a model.
     *
     * @return void
     */
    public static function bootLikeable() {
        if (static::removeLikesOnDelete()) {
            static::deleting(function ($model) {
                $model->removeLikes();
            });
        }
    }

    /**
     * Fetch records that are liked by a given user.
     * Ex: Book::whereLikedBy(123, false)->get();
     * @param $query
     * @param null $userId
     * @param null $sessionUser
     * @return
     */
    public function scopeWhereLikedBy($query, $userId = null, $sessionUser = null) {
        $helper = new Helper();
        if (is_null($sessionUser)) $sessionUser = $helper->isSessionUser();
        if (is_null($userId)) $userId = $helper->getUserId($sessionUser);

        return $query->whereHas('likes', function ($q) use ($userId, $sessionUser) {
            $q->where('user_id', '=', $userId)->where('session_like', $sessionUser);
        });
    }

    /**
     * Fetch like count with a date range.
     * @param $from
     * @param null $to
     * @return mixed
     */
    public function getLikeCountByDate($from, $to = null) {
        $query = $this->likes();
        if (!empty($to)) {
            $range = [new Carbon($from), new Carbon($to)];
        } else {
            $range = [
                (new Carbon($from))->startOfDay(),
                (new Carbon($to))->endOfDay(),
            ];
        }

        return $query->whereBetween('created_at', $range)->count();
    }

    /**
     * Populate the $model->likes attribute
     */
    public function getLikeCountAttribute() {
        return $this->likes()->count();
    }

    /**
     * Collection of the likes on this record
     */
    public function likes() {
        return $this->morphMany(Like::class, 'likeable');
    }

    /**
     * Add a like for this record by the given user.
     * @param $userId mixed - If null will use currently logged in user.
     * @param null $sessionUser
     * @return boolean
     */
    public function like($userId = null, $sessionUser = null) {
        $helper = new Helper();
        if (is_null($sessionUser)) $sessionUser = $helper->isSessionUser();
        if (is_null($userId)) $userId = $helper->getUserId($sessionUser);

        if ($userId) {
            $like = $this->likes()
                ->where('user_id', '=', $userId)
                ->where('session_like', $sessionUser)
                ->first();

            if ($like) return true;

            $like = new Like();
            $like->user_id = $userId;
            $like->session_like = $sessionUser;
            $this->likes()->save($like);
            return true;
        }
        return false;
    }

    /**
     * Remove a like from this record for the given user.
     * @param $userId mixed - If null will use currently logged in user.
     * @param null $sessionUser
     * @return boolean
     */
    public function unlike($userId = null, $sessionUser = null) {
        $helper = new Helper();
        if (is_null($sessionUser)) $sessionUser = $helper->isSessionUser();
        if (is_null($userId)) $userId = $helper->getUserId($sessionUser);

        if ($userId) {
            $like = $this->likes()
                ->where('user_id', '=', $userId)
                ->where('session_like', $sessionUser)
                ->first();

            if (!$like) {
                return true;
            }

            $like->delete();
            return true;
        }
        return false;
    }

    /**
     * Has the currently logged in user already "liked" the current object
     *
     * @param string $userId
     * @return boolean
     */
    public function liked($userId = null, $sessionUser = null) {
        $helper = new Helper();
        if (is_null($sessionUser)) $sessionUser = $helper->isSessionUser();
        if (is_null($userId)) $userId = $helper->getUserId($sessionUser);

        return (bool)$this->likes()
            ->where('user_id', '=', $userId)
            ->where('session_like', $sessionUser)
            ->count();
    }

    /**
     * Did the currently logged in user like this model
     * Example : if($book->liked) { }
     * @return boolean
     */
    public function getLikedAttribute() {
        return $this->liked();
    }

    /**
     * Should remove likes on model row delete (defaults to true)
     * public static removeLikesOnDelete = false;
     */
    public static function removeLikesOnDelete() {
        return isset(static::$removeLikesOnDelete)
            ? static::$removeLikesOnDelete
            : true;
    }

    /**
     * Delete likes related to the current record
     */
    public function removeLikes() {
        Like::where('likeable_type', $this->morphClass)->where('likeable_id', $this->id)->delete();
    }
}
