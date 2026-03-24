<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    private string $appearanceKey = 'appearance';
    private string $companyKey = 'company';

    private function defaultAppearance(): array
    {
        return [
            'theme' => 'system',
            'accent' => 'purple',
            'reduced_motion' => false,
        ];
    }

    private function defaultProfile(): array
    {
        return [
            'name' => 'Felix Tondura',
            'email' => 'felix@fishifox.com',
            'role' => 'Administrator',
            'avatar_seed' => 'Felix',
            'avatar_url' => null,
        ];
    }

    private function defaultCompany(): array
    {
        return [
            'company_name' => 'My Company',
            'company_email' => 'company@example.com',
            'company_phone' => '+94 77 123 4567',
            'company_address' => 'Colombo, Sri Lanka',
            'company_logo' => null,
        ];
    }

    public function index()
    {
        $profile = Profile::query()->first();
        if (!$profile) {
            $profile = Profile::create($this->defaultProfile());
        }

        $appearanceRow = Setting::query()->where('key', $this->appearanceKey)->first();
        $appearance = $appearanceRow?->value ?? $this->defaultAppearance();

        $companyRow = Setting::query()->where('key', $this->companyKey)->first();
        $company = $companyRow?->value ?? $this->defaultCompany();

        return response()->json([
            'profile' => [
                'name' => $profile->name,
                'email' => $profile->email,
                'role' => $profile->role,
                'avatar_seed' => $profile->avatar_seed ?? $profile->name,
                'avatar_url' => $profile->avatar_url,
            ],
            'appearance' => [
                'theme' => $appearance['theme'] ?? 'system',
                'accent' => $appearance['accent'] ?? 'purple',
                'reduced_motion' => (bool)($appearance['reduced_motion'] ?? false),
            ],
            'company' => [
                'company_name' => $company['company_name'] ?? '',
                'company_email' => $company['company_email'] ?? '',
                'company_phone' => $company['company_phone'] ?? '',
                'company_address' => $company['company_address'] ?? '',
                'company_logo' => $company['company_logo'] ?? null,
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'avatar_seed' => ['nullable', 'string', 'max:255'],
        ]);

        $profile = Profile::query()->first();
        if (!$profile) {
            $profile = Profile::create(array_merge($this->defaultProfile(), $data));
        } else {
            $profile->update($data);
        }

        return response()->json([
            'name' => $profile->name,
            'email' => $profile->email,
            'role' => $profile->role,
            'avatar_seed' => $profile->avatar_seed ?? $profile->name,
            'avatar_url' => $profile->avatar_url,
        ]);
    }

    public function updateAppearance(Request $request)
    {
        $data = $request->validate([
            'theme' => ['required', 'in:system,light,dark'],
            'accent' => ['required', 'in:purple,teal,slate'],
            'reduced_motion' => ['required', 'boolean'],
        ]);

        $row = Setting::query()->updateOrCreate(
            ['key' => $this->appearanceKey],
            ['value' => $data]
        );

        return response()->json($row->value);
    }

    public function updateCompany(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:1000'],
        ]);

        $existing = Setting::query()->where('key', $this->companyKey)->first();
        $oldValue = $existing?->value ?? $this->defaultCompany();

        $newValue = array_merge($oldValue, $data);

        $row = Setting::query()->updateOrCreate(
            ['key' => $this->companyKey],
            ['value' => $newValue]
        );

        return response()->json($row->value);
    }

    public function uploadCompanyLogo(Request $request)
    {
        $request->validate([
            'company_logo' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $companyRow = Setting::query()->where('key', $this->companyKey)->first();
        $company = $companyRow?->value ?? $this->defaultCompany();

        $dir = public_path('uploads/company');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!empty($company['company_logo']) && str_contains($company['company_logo'], '/uploads/company/')) {
            $oldName = basename($company['company_logo']);
            $oldPath = public_path('uploads/company/' . $oldName);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $file = $request->file('company_logo');
        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $filename);

        $company['company_logo'] = url('/uploads/company/' . $filename);

        $row = Setting::query()->updateOrCreate(
            ['key' => $this->companyKey],
            ['value' => $company]
        );

        return response()->json([
            'company_logo' => $row->value['company_logo'] ?? null,
        ]);
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $profile = Profile::query()->first();
        if (!$profile) {
            $profile = Profile::create($this->defaultProfile());
        }

        $dir = public_path('uploads/avatars');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if ($profile->avatar_url && str_contains($profile->avatar_url, '/uploads/avatars/')) {
            $oldName = basename($profile->avatar_url);
            $oldPath = public_path('uploads/avatars/' . $oldName);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $file = $request->file('avatar');
        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $filename);

        $profile->avatar_url = url('/uploads/avatars/' . $filename);
        $profile->save();

        return response()->json([
            'avatar_url' => $profile->avatar_url,
        ]);
    }
}