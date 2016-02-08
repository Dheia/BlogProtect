<?php namespace KurtJensen\BlogProtect\Models;

use KurtJensen\Passage\Models\Key;
use Model;

/**
 * Settings Model
 */
class Settings extends Model
{
    public $perm_options = [];

    use \October\Rain\Database\Traits\Validation;

    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'kurtjensen_blogprotect_settings';

    public $settingsFields = 'fields.yaml';

    /**
     * Validation rules
     */
    public $rules = [
        'public_perm' => 'required',
        'default_perm' => 'required',
    ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'permission' => ['KurtJensen\Passage\Models\Key',
            'otherKey' => 'id'],
    ];

    public function __construct()
    {
        parent::__construct();
        $options = array_flip($this->getDropdownOptions());

        $this->public_perm = array_get($options, 'blog_public', 0);

        $this->default_perm = 0;
    }

    public function getDropdownOptions($fieldName = null, $keyValue = null)
    {
        if (count($this->perm_options)) {
            return $this->perm_options;
        }

        return $this->perm_options = Key::lists('name', 'id');
    }

}
