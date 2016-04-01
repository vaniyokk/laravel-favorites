Laravel Likeable Plugin
============

[![Build Status](https://travis-ci.org/sugar-agency/laravel-likeable.svg?branch=master)](https://travis-ci.org/sugar-agency/laravel-likeable)
[![Latest Stable Version](https://poser.pugx.org/sugar-agency/laravel-likeable/v/stable.svg)](https://packagist.org/packages/sugar-agency/laravel-likeable)
[![License](https://poser.pugx.org/sugar-agency/laravel-likeable/license.svg)](https://packagist.org/packages/sugar-agency/laravel-likeable)

Trait for Laravel Eloquent models to allow easy implementation of a "like" or "favorite" or "remember" feature.  
Based heavily on rtconner/laravel-likeable.


## Composer Install

	composer require sugar/laravel-likeable

## Install and then run the migrations

```php
'providers' => [
	\Sugar\Likeable\LikeableServiceProvider::class,
],
```

```bash
php artisan vendor:publish --provider="Sugar\Likeable\LikeableServiceProvider"
php artisan migrate
```

#### Setup your models

```php
class Article extends \Illuminate\Database\Eloquent\Model {
	use \Sugar\Likeable\Likeable;
}
```

## Configuration file

After installation, the config file is located at *config/likeable.php*  
You can :
* enable session fallback for likes
* define the lifetime (in minutes) for the command likeable:clean
* define if the command likeable:clean should delete only session likes

## Likeable Trait

#### Like model

```php
$article->like(); // like the article for current user / session
$article->like($myUserId, $false); // like the article for specific user
```

#### Unlike model

```php
$article->unlike(); // like the article for current user / session
$article->unlike($myUserId, $false); // unlike the article for specific user
```

#### Like count

```php
$article->likeCount; // get count of likes
$article->getLikeCountByDate('2012-01-30'); // Count likes for a specific date
$article->getLikeCountByDate('2012-01-30', '2016-01-30'); // Count likes for a date range
```

#### Check if Model is currently liked

```php
$article->liked(); // check if currently logged in user or session user liked the article
$article->liked($myUserId, $false);
```

#### Get collection of existing likes

```php
$article->likes; // Iterable Illuminate\Database\Eloquent\Collection of existing likes 
```

#### Find only articles where user liked them
```php
Article::whereLikedBy()->get(); // for the current user / session
Article::whereLikedBy($myUserId, false)->get(); // for a specific user
```

## Command

#### Delete likes after a certain amount of time
Because someone's favorite could be 'outdated' and not representative anymore.  
There is a command for that. The time after which a like is outdated can be set in the config, also you can define if it should only delete session likes
```sh
php artisan likeable:clean
```

## Helper class
In order to convert session likes to user likes (for example after a visitor registers on you site) you need to use the Helper class.  
This workaround is nescessary because session id's are regenerated after login/logout.

```php
class AuthController extends Controller {
    use AuthenticatesAndRegistersUsers {
        login as traitlogin;
        register as traitregister;
    }
    use ThrottlesLogins;

    public function login(Request $request, \Sugar\Likeable\Helper $helper) {
        $session_id = $helper->sessionId();
        $return = $this->traitlogin($request);
        if(Auth::check()){
            $helper->convertSessionToUserLikes($session_id, Auth::user()->id);
        }
        return $return;
    }
    public function register(Request $request, \Sugar\Likeable\Helper $helper){
        $session_id = $helper->sessionId();
        $return = $this->traitregister($request);
        if(Auth::check()){
            $helper->convertSessionToUserLikes($session_id, Auth::user()->id);
        }
        return $return;
    }
    ...
}
```

## Credits
 - Gregory Claeyssens - http://sugar.gent
 - Robert Conner - http://smartersoftware.net
