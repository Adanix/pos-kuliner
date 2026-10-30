<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Outlet;
use App\Models\Setting;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    public function edit()
    {
        $outlet = Outlet::firstOrCreate([], [
            'name' => 'Kuliner Ciora',
            'tax_percent' => 0,
            'service_charge_percent' => 0,
            'rounding_mode' => 'none',
            'require_open_shift' => true,
            'allow_negative_stock' => false,
        ]);

        $users = User::orderBy('role')->orderBy('name')->get();
        $auditLogs = AuditLog::with('user')->latest('created_at')->take(50)->get();

        $aiQuota = Setting::get('ai.monthly_quota', 300);
        $retentionDays = Setting::get('ai.retention_days', 30);
        $gofoodComm = Setting::get('report.commission.gofood', 0);
        $shopeefoodComm = Setting::get('report.commission.shopeefood', 0);

        return view('settings.index', compact(
            'outlet',
            'users',
            'auditLogs',
            'aiQuota',
            'retentionDays',
            'gofoodComm',
            'shopeefoodComm'
        ));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'outlet_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'receipt_footer' => ['nullable', 'string', 'max:500'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'service_charge_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'rounding_mode' => ['required', 'string', 'in:none,nearest_100'],
            'require_open_shift' => ['nullable', 'boolean'],
            'allow_negative_stock' => ['nullable', 'boolean'],
            'ai_monthly_quota' => ['nullable', 'integer', 'min:10', 'max:10000'],
            'ai_retention_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'commission_gofood' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'commission_shopeefood' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $outlet = Outlet::first();
        if ($outlet) {
            $outlet->update([
                'name' => $validated['outlet_name'],
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'receipt_footer' => $validated['receipt_footer'] ?? null,
                'tax_percent' => $validated['tax_percent'] ?? 0,
                'service_charge_percent' => $validated['service_charge_percent'] ?? 0,
                'rounding_mode' => $validated['rounding_mode'],
                'require_open_shift' => $request->boolean('require_open_shift'),
                'allow_negative_stock' => $request->boolean('allow_negative_stock'),
            ]);
        }

        Setting::set('ai.monthly_quota', (int) ($validated['ai_monthly_quota'] ?? 300), auth()->id());
        Setting::set('ai.retention_days', (int) ($validated['ai_retention_days'] ?? 30), auth()->id());
        Setting::set('report.commission.gofood', (float) ($validated['commission_gofood'] ?? 0), auth()->id());
        Setting::set('report.commission.shopeefood', (float) ($validated['commission_shopeefood'] ?? 0), auth()->id());

        AuditService::log('update_settings', 'Setting', null, ['outlet_name' => $validated['outlet_name']]);

        return redirect()->route('settings.edit')->with('success', 'Pengaturan sistem berhasil disimpan.');
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'string', 'in:owner,cashier'],
            'password' => ['required', 'string', 'min:8'],
            'pin' => ['nullable', 'string', 'min:4', 'max:6'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => UserRole::from($validated['role']),
            'password' => Hash::make($validated['password']),
            'pin_hash' => ! empty($validated['pin']) ? Hash::make($validated['pin']) : null,
            'is_active' => true,
        ]);

        AuditService::log('create_user', 'User', $user->id, ['name' => $user->name, 'email' => $user->email, 'role' => $user->role->value]);

        return redirect()->route('settings.edit')->with('success', "Pengguna '{$user->name}' berhasil ditambahkan.");
    }

    public function toggleUserActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        AuditService::log('toggle_user_active', 'User', $user->id, ['is_active' => $user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('settings.edit')->with('success', "Pengguna '{$user->name}' telah {$status}.");
    }
}
