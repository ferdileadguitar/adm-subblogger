<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'salt', 'password', 'remember_token',
    ];

    private static $__instance = null;

    // ------------------------------------------------------------------------
    // Public Methods
    // ------------------------------------------------------------------------

    public static function getInstance()
    {
        if (self::$__instance === null)
        { self::$__instance = new self; }

        return self::$__instance;
    }

    // ------------------------------------------------------------------------
    
    public static function getUser($credentials = [])
    {
        // Get user by email or username, if not exists return false
        // if exists check is in admin list or not
        // ------------------------------------------------------------------------
        
        // Empty username or password? Meh ... ¯\_(ツ)_/¯
        if (empty($credentials['username']) || empty($credentials['password']))
        { return FALSE; }

        $user = User::where(function($query) use($credentials) {
            $query->where('username', $credentials['username'])
                  ->orWhere('email', $credentials['username']);
        })->select('id', 'email', 'salt', 'password')->first();

        if (! $user)
        { return FALSE; }

        // ------------------------------------------------------------------------
        $list = json_decode(\Storage::get('lists.adm'));
        if (! $list)
        { return FALSE; }

        return in_array($user->email, $list) ? $user->checkPassword($credentials) : FALSE;
    }

    // ------------------------------------------------------------------------
    // Overrides the method to ignore the remember token.
    // ------------------------------------------------------------------------
    
    public function getRememberToken()
    { return null; }

    public function setRememberToken($value)
    {}

    public function getRememberTokenName()
    { return null; }

    public function setAttribute($key, $value)
    {
        $isRememberTokenAttribute = $key == $this->getRememberTokenName();
        if (!$isRememberTokenAttribute)
        { parent::setAttribute($key, $value); }
    }

    // ------------------------------------------------------------------------
    // Private Methods
    // ------------------------------------------------------------------------

    private function checkPassword($credentials)
    {
        if (! $this)
        { return FALSE; }

        return ($this->password == $this->encrypt($credentials['password'], $this->salt)) ? $this : FALSE;   
    }

    // ------------------------------------------------------------------------

    private function encrypt($password = '', $salt = '')
    { return sha1($salt . $password); }

    // ------------------------------------------------------------------------
    // Relations
    // ------------------------------------------------------------------------
    
    public function posts()
    { return $this->hasMany('App\Post'); }

    public function image()
    { return $this->belongsTo('App\Image', 'object_file_id'); }
}
