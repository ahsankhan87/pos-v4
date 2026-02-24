<?php

if (! function_exists('current_locale')) {
    function current_locale(): string
    {
        $supportedLocales = config('App')->supportedLocales ?? ['en'];
        $sessionLocale = (string) session('locale');

        if (in_array($sessionLocale, $supportedLocales, true)) {
            return $sessionLocale;
        }

        $requestLocale = service('request')->getLocale();
        if (is_string($requestLocale) && in_array($requestLocale, $supportedLocales, true)) {
            return $requestLocale;
        }

        return (string) (config('App')->defaultLocale ?? 'en');
    }
}

if (! function_exists('is_rtl')) {
    function is_rtl($locale = null)
    {
        $activeLocale = $locale ?: current_locale();
        return in_array($activeLocale, ['ar'], true);
    }
}

if (! function_exists('locale_direction')) {
    function locale_direction($locale = null)
    {
        return is_rtl($locale) ? 'rtl' : 'ltr';
    }
}
