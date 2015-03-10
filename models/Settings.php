<?php namespace KurtJensen\BlogProtect\Models;

use Model;

use ShahiemSeymor\Roles\Models\UserPermission as Permission;

/**
 * Settings Model
 */
class Settings extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'kurtjensen_blogprotect_settings';

    public $settingsFields = 'fields.yaml';

    /**
     * Validation rules
     */
    public $rules = [
        'public_perm'    => 'required',
        'deny_perm'    => 'required',
        'default_perm'   => 'required',
    ];
    
     /**
     * @var array Relations
     */
    public $belongsTo = [
        'permission' =>       ['ShahiemSeymor\Roles\Models\UserPermission', 
                        'otherKey'=>'id'],
        ];


    public function __construct()
    {
        parent::__construct();
        $options = $this->getDropdownOptions();
        
        $this->public_perm = $this->public_perm?$this->public_perm :
            array_search( 'blog_public', $options);
            
        $this->deny_perm = $this->deny_perm?$this->deny_perm : 
            array_search( 'blog_deny_all', $options);
            
        $this->default_perm = $this->default_perm?$this->default_perm : 
            array_search( 'blog_deny_all', $options);
    }
    

    public function getDropdownOptions($fieldName = null, $keyValue = null)
    {
        $permissions = Permission::get();
        foreach ($permissions as $permission)
            $options[$permission->id] = $permission->name;
            
        return $options;
    }

}
