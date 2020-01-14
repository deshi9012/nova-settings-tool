<?php
namespace Bakerkretzmar\NovaSettingsTool\Helpers;

use DB;

class SettingsHelper
{
    public static function getValue($setting)
    {
        /*
        * TODO this have to be finished to read from file also
        */
        if (config('nova-settings-tool.storage') == 'file') {
            return;
        } else {
            $tableName = config('nova-settings-tool.table_name');
            return DB::table($tableName)
        ->where('key', $setting)
        ->select('value')
        ->get();
        }
    }
}
