<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    private const ASSIGNABLE_ROLES = ['hr', 'hr_manager'];

    public function index(): Response
    {
        $accounts = User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'hr_manager', 'hr']))
            ->orderByDesc('id')
            ->get(['id', 'name', 'email', 'ref_code', 'is_active', 'created_at'])
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->getRoleNames()->first(),
                'ref_code' => $u->ref_code,
                'is_active' => $u->is_active,
                'created_at' => $u->created_at?->toIso8601String(),
                'is_self' => $u->id === Auth::id(),
            ]);

        return Inertia::render('admin/accounts/index', [
            'accounts' => $accounts,
            'roles' => self::ASSIGNABLE_ROLES,
            'public_url' => rtrim(config('app.url'), '/'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'password' => ['required', 'string', 'min:8', 'max:120'],
            'role' => ['required', Rule::in(self::ASSIGNABLE_ROLES)],
            'ref_code' => $this->refCodeRules(),
        ], [
            'name.required' => 'Vui lòng nhập tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.unique' => 'Email này đã được sử dụng.',
            'password.required' => 'Vui lòng đặt mật khẩu.',
            'password.min' => 'Mật khẩu cần ít nhất 8 ký tự.',
            'role.required' => 'Vui lòng chọn vai trò.',
            ...$this->refCodeMessages(),
        ]);

        $this->ensureRefCodeUnique($data['ref_code']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => true,
            'email_verified_at' => now(),
            'ref_code' => $data['ref_code'],
        ]);

        $user->assignRole($data['role']);

        return back()->with('success', "Đã tạo tài khoản {$user->email} (ref {$user->ref_code}).");
    }

    public function update(Request $request, User $account): RedirectResponse
    {
        $this->ensureManageable($account);

        $data = $request->validate([
            'role' => ['required', Rule::in(self::ASSIGNABLE_ROLES)],
        ], [
            'role.in' => 'Vai trò không hợp lệ.',
        ]);

        $account->syncRoles([$data['role']]);

        return back()->with('success', "Đã đổi vai trò cho {$account->name}.");
    }

    public function updateRefCode(Request $request, User $account): RedirectResponse
    {
        $this->ensureManageable($account);

        $data = $request->validate([
            'ref_code' => $this->refCodeRules(),
        ], $this->refCodeMessages());

        $this->ensureRefCodeUnique($data['ref_code'], $account->id);

        $account->update(['ref_code' => $data['ref_code']]);

        return back()->with('success', "Đã đổi ref code của {$account->name} thành {$account->ref_code}.");
    }

    public function resetPassword(Request $request, User $account): RedirectResponse
    {
        $this->ensureManageable($account);

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'max:120'],
        ], [
            'password.min' => 'Mật khẩu cần ít nhất 8 ký tự.',
        ]);

        $account->forceFill(['password' => Hash::make($data['password'])])->save();

        return back()->with('success', "Đã đặt lại mật khẩu cho {$account->name}.");
    }

    public function toggleActive(User $account): RedirectResponse
    {
        $this->ensureManageable($account, allowSelf: false);

        $account->update(['is_active' => ! $account->is_active]);

        $state = $account->is_active ? 'kích hoạt lại' : 'vô hiệu hoá';

        return back()->with('success', "Đã {$state} tài khoản {$account->name}.");
    }

    public function destroy(User $account): RedirectResponse
    {
        $this->ensureManageable($account, allowSelf: false);

        $email = $account->email;
        $account->delete();

        return back()->with('success', "Đã xoá tài khoản {$email}.");
    }

    private function refCodeRules(): array
    {
        return ['required', 'string', 'min:4', 'max:20', 'regex:/^[A-Za-z0-9]+$/'];
    }

    private function refCodeMessages(): array
    {
        return [
            'ref_code.required' => 'Vui lòng nhập mã ref.',
            'ref_code.min' => 'Mã ref cần ít nhất 4 ký tự.',
            'ref_code.max' => 'Mã ref không được dài quá 20 ký tự.',
            'ref_code.regex' => 'Mã ref chỉ được chứa chữ cái và số.',
        ];
    }

    private function ensureRefCodeUnique(string $code, ?int $ignoreId = null): void
    {
        $exists = User::query()
            ->whereRaw('LOWER(ref_code) = ?', [mb_strtolower($code)])
            ->when($ignoreId, fn ($q, $id) => $q->where('id', '!=', $id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'ref_code' => 'Mã ref này đã được dùng, vui lòng chọn mã khác.',
            ]);
        }
    }

    private function ensureManageable(User $account, bool $allowSelf = true): void
    {
        if ($account->hasRole('admin')) {
            abort(403, 'Không thể thao tác trên tài khoản admin.');
        }

        if (! $allowSelf && $account->id === Auth::id()) {
            abort(403, 'Không thể tự thao tác trên tài khoản của chính mình.');
        }
    }
}
