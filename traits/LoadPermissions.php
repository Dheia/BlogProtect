<?php namespace KurtJensen\BlogProtect\Traits;

use Auth;
use RainLab\User\Components\Account;
use RainLab\User\Models\User as User;
//use ShahiemSeymor\Roles\Models\UserPermission as Permission;
use ShahiemSeymor\Roles\Models\UserGroup as UserGroup;
use KurtJensen\BlogProtect\Models\Settings;

trait LoadPermissions
{      
    /**
     * @var array Permissions array for current user
     */
    public $permarray = [];
    
    public function loadPermissions()
    {
        if (count($this->permarray)) return $this->permarray;
        
        $account = new Account;
        
        $deny_perm = intval( Settings::get('deny_perm'));

        if(Auth::check())
        {
            $roles = json_decode(User::find($account->user()->id)->groups);
            foreach($roles as $role)
            {
                foreach(UserGroup::find($role->id)->perms as $perm)
                {
                    if ($perm->id != $deny_perm)
                        $this->permarray[$perm->id]=$perm->id;
                }
                
            }
        asort($this->permarray);
        return $this->permarray;
        }
        else
        $this->permarray = [Settings::get('public_perm')];
        return $this->permarray;
    }
}
