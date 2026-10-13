<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        dd([
            'Akun yg sedang dipakai' => $request->user()->email,
            'Role akun ini' => $request->user()->role,
            'Role yg diizinkan' => $role
        ]);
        if (! $request->user() || $request->user()->role !== $role) {
            abort(403, 'Anda tidak memiliki akses untuk halaman ini.');
        }
        return $next($request);
    }
}
