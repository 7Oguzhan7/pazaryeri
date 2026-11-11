<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class SellerApplicationController extends Controller
{
    public function index(): Response
    {
        $applications = Seller::query()
            ->where('status', Seller::STATUS_PENDING)
            ->with('user:id,name,email')
            ->orderBy('created_at')
            ->paginate(20)
            ->through(fn (Seller $seller): array => [
                'id' => $seller->id,
                'storeName' => $seller->store_name,
                'taxNumber' => $seller->tax_number,
                'iban' => $seller->iban,
                'applicantName' => $seller->user->name,
                'applicantEmail' => $seller->user->email,
            ]);

        return Inertia::render('Admin/SellerApplications/Index', [
            'applications' => $applications,
        ]);
    }

    public function approve(Seller $seller): RedirectResponse
    {
        abort_unless($seller->status === Seller::STATUS_PENDING, 404);

        DB::transaction(function () use ($seller): void {
            $seller->update(['status' => Seller::STATUS_APPROVED]);
            Role::findOrCreate('seller', 'web');
            $seller->user->syncRoles('seller');
        });

        return redirect()
            ->route('admin.seller-applications.index')
            ->with('status', 'seller-application-approved');
    }

    public function reject(Seller $seller): RedirectResponse
    {
        abort_unless($seller->status === Seller::STATUS_PENDING, 404);

        $seller->update(['status' => Seller::STATUS_REJECTED]);

        return redirect()
            ->route('admin.seller-applications.index')
            ->with('status', 'seller-application-rejected');
    }
}
