<?php

namespace App\Controllers\Admin;

use App\Models\SiteSettingModel;

class Settings extends BaseAdminController
{
    public function index(): string
    {
        $settings = (new SiteSettingModel())->allAsMap();

        return $this->render('Settings', 'settings', 'settings/index', [
            'settings' => $settings,
        ]);
    }

    public function save(): \CodeIgniter\HTTP\RedirectResponse
    {
        require_csrf();

        $model = new SiteSettingModel();
        $keys = [
            'site_name', 'site_tagline', 'site_description', 'logo_url',
            'contact_email', 'contact_phone', 'contact_address',
            'facebook_url', 'twitter_url', 'youtube_url',
            'meta_keywords',
        ];

        foreach ($keys as $key) {
            $value = sanitize($this->request->getPost($key) ?? '');
            $existing = $model->where('setting_key', $key)->first();
            if ($existing) {
                $model->update($existing['id'], ['setting_value' => $value]);
            } else {
                $model->insert(['setting_key' => $key, 'setting_value' => $value]);
            }
        }

        return redirect()->to('admin/settings')->with('success', 'Settings saved successfully.');
    }
}
