<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Data\Admin\CustomerData;
use App\Exports\CustomersExport;
use App\Http\Controllers\Controller;
use App\Imports\CustomersImport;
use App\Models\CRM\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $customers = Customer::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('ico', 'like', "%{$search}%");
                });
            })->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Customers/Index', [
            'customers' => CustomerData::collect($customers),
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],

            'is_company' => ['boolean'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'ico' => ['nullable', 'string', 'max:20'],
            'dic' => ['nullable', 'string', 'max:20'],
            'has_vat' => ['boolean'],

            'billing_street' => ['nullable', 'string', 'max:255'],
            'billing_city' => ['nullable', 'string', 'max:100'],
            'billing_zip' => ['nullable', 'string', 'max:20'],
            'billing_country' => ['nullable', 'string', 'size:2'],

            'internal_notes' => ['nullable', 'string'],
            'is_registered' => ['boolean'],
        ]);

        Customer::query()->create($validated);

        return back()->with('success', 'Zákazník byl úspěšně vytvořen.');
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],

            'is_company' => ['boolean'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'ico' => ['nullable', 'string', 'max:20'],
            'dic' => ['nullable', 'string', 'max:20'],
            'has_vat' => ['boolean'],

            'billing_street' => ['nullable', 'string', 'max:255'],
            'billing_city' => ['nullable', 'string', 'max:100'],
            'billing_zip' => ['nullable', 'string', 'max:20'],
            'billing_country' => ['nullable', 'string', 'size:2'],

            'internal_notes' => ['nullable', 'string'],
            'is_registered' => ['boolean'],
        ]);

        $customer->update($validated);

        return back()->with('success', 'Zákazník byl úspěšně upraven.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return back()->with('success', 'Zákazník byl smazán.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls'],
        ]);

        try {
            Excel::import(new CustomersImport, $request->file('file'));

            return back()->with('success', 'Import byl úspěšně dokončen.');
        } catch (\Exception $e) {
            return back()->with('error', 'Chyba při importu: '.$e->getMessage());
        }
    }

    public function export(): BinaryFileResponse
    {
        return Excel::download(new CustomersExport, 'customers.xlsx');
    }
}
