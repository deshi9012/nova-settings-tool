<?php
use DB;

function getValue($setting)
{
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
