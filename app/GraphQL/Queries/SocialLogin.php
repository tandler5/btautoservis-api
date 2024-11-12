<?php

namespace App\GraphQL\Queries;

use App\Models\Setting;

final class SocialLogin
{
    public function __invoke()
    {
        // Získáme všechna relevantní nastavení
        $settings = Setting::whereIn('name', ['facebook_app_id', 'google_client_id', 'enable_google_login', 'enable_facebook_login'])->get()->toArray();
        
        $result = [
            'facebook_app_id' => null,  // výchozí hodnota null
            'google_client_id' => null  // výchozí hodnota null
        ];

        // Přepínače pro stav povolení
        $enableGoogleLogin = true;
        $enableFacebookLogin = true;

        foreach ($settings as $setting) {
            // Kontrola stavu enable_google_login
            if ($setting['name'] === 'enable_google_login' && $setting['value'] === 'off') {
                $enableGoogleLogin = false;
            }

            // Kontrola stavu enable_facebook_login
            if ($setting['name'] === 'enable_facebook_login' && $setting['value'] === 'off') {
                $enableFacebookLogin = false;
            }

            // Naplníme hodnotu pro facebook_app_id, pokud je povolený
            if ($setting['name'] === 'facebook_app_id' && $enableFacebookLogin) {
                $result['facebook_app_id'] = $setting['value'];
            }

            // Naplníme hodnotu pro google_client_id, pokud je povolený
            if ($setting['name'] === 'google_client_id' && $enableGoogleLogin) {
                $result['google_client_id'] = $setting['value'];
            }
        }

        return $result;
    }
}
