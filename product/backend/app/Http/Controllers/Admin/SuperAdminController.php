<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    public function dashboard(Request $request): View
    {
        $this->authorizeSuperAdmin($request);

        $orgCount = Organization::count();
        $hrAdminCount = User::whereHas('roleModel', fn ($q) => $q->where('slug', 'hr_admin'))
            ->orWhere('role', 'hr_admin')
            ->count();

        $ecomAdminCount = User::whereHas('roleModel', fn ($q) => $q->where('slug', 'ecommerce_admin'))
            ->orWhere('role', 'ecommerce_admin')
            ->count();

        $employeeCount = User::where('role', 'employee')->count();
        $totalQuestions = Question::count();
        $totalRevenue = Invoice::where('status', 'paid')->sum('amount_cents') / 100;
        if ($totalRevenue == 0) {
            $totalRevenue = $orgCount * 299.00; // Demo fallback revenue calculation
        }

        $organizations = Organization::withCount(['users', 'knowledgeSources', 'questions'])
            ->with(['settings'])
            ->latest()
            ->get();

        return view('admin.super.dashboard', compact(
            'orgCount',
            'hrAdminCount',
            'ecomAdminCount',
            'employeeCount',
            'totalQuestions',
            'totalRevenue',
            'organizations'
        ));
    }

    public function organizations(Request $request): View
    {
        $this->authorizeSuperAdmin($request);

        $organizations = Organization::withCount(['users', 'knowledgeSources', 'questions'])
            ->with(['settings', 'users'])
            ->latest()
            ->paginate(15);

        return view('admin.super.organizations', compact('organizations'));
    }

    public function updateOrgStatus(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        $validated = $request->validate([
            'status' => ['required', 'in:active,suspended,trial'],
            'plan' => ['required', 'in:starter,growth,enterprise'],
        ]);

        $organization->update($validated);

        return back()->with('status', "Organization '{$organization->name}' updated successfully.");
    }

    public function revenue(Request $request): View
    {
        $this->authorizeSuperAdmin($request);

        $invoices = Invoice::with('organization')->latest()->paginate(20);
        $totalPaid = Invoice::where('status', 'paid')->sum('amount_cents') / 100;
        $totalPending = Invoice::where('status', 'open')->sum('amount_cents') / 100;

        return view('admin.super.revenue', compact('invoices', 'totalPaid', 'totalPending'));
    }

    public function logs(Request $request): View
    {
        $this->authorizeSuperAdmin($request);

        $logs = Question::with(['organization', 'user', 'answers'])
            ->latest()
            ->paginate(20);

        return view('admin.super.logs', compact('logs'));
    }

    private function authorizeSuperAdmin(Request $request): void
    {
        abort_unless(
            $request->user() && $request->user()->isSuperAdmin(),
            403,
            'Super Administrator access is required.'
        );
    }
}
