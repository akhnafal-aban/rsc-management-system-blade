<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMembershipPackageRequest;
use App\Http\Requests\UpdateMembershipFeesRequest;
use App\Http\Requests\UpdateMembershipPackageRequest;
use App\Models\Membership;
use App\Services\MembershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    public function __construct(private MembershipService $membershipService) {}

    public function index(): View
    {
        $packages = $this->membershipService->getAllPackageOptions();
        $fees = Membership::getFees();

        return view('pages.admin.settings.index', compact('packages', 'fees'));
    }

    public function storePackage(StoreMembershipPackageRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $config = Membership::getPricingConfig();
        $packages = $config['membership_packages'] ?? [];
        $key = $validated['key'];

        if (array_key_exists($key, $packages)) {
            throw ValidationException::withMessages([
                'key' => 'Key paket sudah digunakan. Gunakan key lain yang unik.',
            ]);
        }

        $packages[$key] = [
            'label' => $validated['label'],
            'price' => (int) $validated['price'],
            'discount_percent' => isset($validated['discount_percent']) ? (int) $validated['discount_percent'] : 0,
            'duration_days' => (int) $validated['duration_days'],
        ];

        $config['membership_packages'] = $packages;
        Membership::savePricingConfig($config);

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Paket membership baru berhasil ditambahkan.');
    }

    public function updatePackage(string $packageKey, UpdateMembershipPackageRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $config = Membership::getPricingConfig();
        $packages = $config['membership_packages'] ?? [];

        if (! array_key_exists($packageKey, $packages)) {
            abort(404);
        }

        $packages[$packageKey]['label'] = $validated['label'];
        $packages[$packageKey]['price'] = (int) $validated['price'];
        $packages[$packageKey]['discount_percent'] = isset($validated['discount_percent']) ? (int) $validated['discount_percent'] : 0;
        $packages[$packageKey]['duration_days'] = (int) $validated['duration_days'];

        $config['membership_packages'] = $packages;
        Membership::savePricingConfig($config);

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Paket membership berhasil diperbarui.');
    }

    public function updateFees(UpdateMembershipFeesRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $config = Membership::getPricingConfig();
        $fees = $config['fees'] ?? [];

        $fees['new_member_fee'] = (int) $validated['new_member_fee'];
        $fees['non_member_visit_daily'] = (int) $validated['non_member_visit_daily'];
        $fees['non_member_swim'] = (int) $validated['non_member_swim'];

        $config['fees'] = $fees;
        Membership::savePricingConfig($config);

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Biaya non-package berhasil diperbarui.');
    }

    public function deletePackage(string $packageKey): RedirectResponse
    {
        $config = Membership::getPricingConfig();
        $packages = $config['membership_packages'] ?? [];

        if (! array_key_exists($packageKey, $packages)) {
            abort(404);
        }

        unset($packages[$packageKey]);

        $config['membership_packages'] = $packages;
        Membership::savePricingConfig($config);

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Paket membership berhasil dihapus.');
    }
}
