<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RefController extends Controller
{
    public const SESSION_KEY = 'ref.code';
    public const SESSION_EXPIRES_KEY = 'ref.expires_at';
    public const TTL_SECONDS = 86400;

    public function enter(Request $request, string $code): RedirectResponse
    {
        $hr = self::lookupHr($code);

        if ($hr) {
            $request->session()->put(self::SESSION_KEY, $hr->ref_code);
            $request->session()->put(self::SESSION_EXPIRES_KEY, now()->addSeconds(self::TTL_SECONDS)->timestamp);
        }

        return redirect('/');
    }

    public static function activeRefHr(Request $request): ?User
    {
        $code = $request->session()->get(self::SESSION_KEY);
        $expiresAt = (int) $request->session()->get(self::SESSION_EXPIRES_KEY);

        if (! $code || ! $expiresAt || $expiresAt < now()->timestamp) {
            $request->session()->forget([self::SESSION_KEY, self::SESSION_EXPIRES_KEY]);

            return null;
        }

        return self::lookupHr($code);
    }

    private static function lookupHr(string $code): ?User
    {
        return User::query()
            ->whereRaw('LOWER(ref_code) = ?', [mb_strtolower(trim($code))])
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['hr', 'hr_manager']))
            ->first();
    }
}
