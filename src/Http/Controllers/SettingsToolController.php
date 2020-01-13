<?php

namespace Bakerkretzmar\NovaSettingsTool\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Valuestore\Valuestore;
use DB;

class SettingsToolController
{
    protected $store;

    public function __construct()
    {
        $this->store = Valuestore::make(config('nova-settings-tool.path', storage_path('app/settings.json')));
    }

    public function read()
    {
        if (config('nova-settings-tool.storage') == 'file') {
            $values = $this->store->all();

            $settings = collect(config('nova-settings-tool.settings'));

            $panels = $settings->where('panel', '!=', null)->pluck('panel')->unique()->flatMap(function ($panel) use ($settings) {
                return [$panel => $settings->where('panel', $panel)->pluck('key')->all()];
            })->when($settings->where('panel', null)->isNotEmpty(), function ($collection) use ($settings) {
                return $collection->merge(['_default' => $settings->where('panel', null)->pluck('key')->all()]);
            })->all();

            $settings = $settings->map(function ($setting) use ($values) {
                return array_merge([
                    'type'  => 'text',
                    'label' => ucfirst($setting['key']),
                    'value' => $values[$setting['key']] ?? null,
                ], $setting);
            })->keyBy('key')->all();

            return response()->json(compact('settings', 'panels'));
        } else {
            $settings = [];
            $tableName = config('nova-settings-tool.table_name');
            $settings['settings'] = DB::table($tableName)->get()->keyBy('key');


            foreach ($settings['settings'] as $key => $setting) {
                if ($setting->options !== null) {
                    $setting->options = json_decode($setting->options, 1);
                }
                if ($setting->panel === null) {
                    $settings['panels']['_default'][] = $key;
                    continue;
                }

                $settings['panels'][$setting->panel][] = $setting->key;
            }

            return response()->json($settings);
//
        }
    }

    public function write(Request $request)
    {
        if (config('nova-settings-tool.storage') == 'file') {
            foreach ($request->all() as $key => $value) {
                $this->store->put($key, $value);
            }
        } else {
            $tableName = config('nova-settings-tool.table_name');
            foreach ($request->all() as $settingName => $value) {
                $panels = DB::table($tableName)
                    ->where('key', $settingName)
                    ->update(['value' => $value]);
            }
        }
        return response()->json();
    }

    public function manageSettings(Request $request = null)
    {
        $settings = config('nova-settings-tool.settings');

        $tableName = config('nova-settings-tool.table_name');
        $extractedKeys = [];

        foreach ($settings as $key => $setting) {
            $extractedKeys[] = $setting['key'];
            if (array_key_exists('options', $setting)) {
                $setting['options'] = json_encode($setting['options']);
            }
            DB::table($tableName)->updateOrInsert(['key' => $setting['key']], $setting);
        }

        DB::table($tableName)->whereNotIn('key', $extractedKeys)->delete();
        return response()->json();
    }
}
