<?php namespace KurtJensen\BlogProtect\Traits;

use DB;
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
        
        $User = Auth::getUser();
        
        $deny_perm = intval( Settings::get('deny_perm'));

        if ( $User )
        {
            $roles = DB::table('shahiemseymor_assigned_roles')->
                        where('user_id','=',$User->id)->lists('role_id');
            
            $this->permarray = DB::table('shahiemseymor_permission_role')->
                    wherein('role_id', $roles)->
                    where('permission_id','<>', $deny_perm)->
                    lists('permission_id');

            if (!count($this->permarray)) $this->permarray = [0];
            
            $this->permarray = array_unique($this->permarray);
            return $this->permarray;
         }
        else
        $this->permarray = [Settings::get('public_perm')];
        return $this->permarray;
    }
}
