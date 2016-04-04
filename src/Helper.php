<?php namespace Sugar\Favorites;

use App\User;
use Sugar\Favorites\Models\Like;

class Helper {

    protected $config;

    public function __construct() {
        $this->config = config('favorites');
    }

    /**
     * Determines if we are using sessions or not
     * @return bool
     */
    public function isSessionUser() {
        $session_enabled = $this->config['sessions'];
        $userId = auth()->id();

        if (!$userId && $session_enabled) {
            return true;
        }
        return false;
    }

    /**
     * Fetch the primary ID of the currently logged in user or the Session ID
     * @param  bool $sessionUser
     * @return number
     */
    public function getUserId($sessionUser = false) {
        $userId = auth()->id();
        if ($sessionUser && !$userId) {
            $userId = $this->sessionId();
        }

        return $userId;
    }

    /**
     * Fetch the session ID of the current user, creates a session if needed
     * @return number
     */
    public function sessionId() {
        return session()->getId();
    }

    /**
     * Convert session likes to user likes
     * @param $oldSessionId
     * @param $user_id
     */
    public function convertSessionToUserLikes($oldSessionId, $user_id) {
        // sessions need to be active
        if ($this->config['sessions']) {
            $session_likes = Like::where('session_like', true)->where('user_id', $oldSessionId)->get();
            foreach ($session_likes as $session_like) {
                $this->convertSessionToUserLike($session_like, $user_id);
            }
        }
    }

    /**
     * Converts a sessionlike to a userlike.
     * @param Like $like
     * @param $user_id
     */
    private function convertSessionToUserLike(Like $like, $user_id) {
        // Checks if the like already exists for the logged in user
        // if it does we need to unlike the session like

        if (Like::where('session_like', false)
            ->where('user_id', $user_id)
            ->where('likeable_type', $like->likeable_type)
            ->where('likeable_id', $like->likeable_id)
            ->exists()
        ) {
            $like->delete();
            return;
        }

        $like->session_like = false;
        $like->user_id = $user_id;
        $like->save();
    }
}