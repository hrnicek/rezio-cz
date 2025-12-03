<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Data\Admin\CustomerData;
use App\Exports\CustomersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Admin\Customer\ImportCustomerRequest;
use App\Http\Requests\Tenant\Admin\Customer\StoreCustomerRequest;
use App\Http\Requests\Tenant\Admin\Customer\UpdateCustomerRequest;
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

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        Customer::query()->create($request->validated());

        return back()->with('success', 'Zákazník byl úspěšně vytvořen.');
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return back()->with('success', 'Zákazník byl úspěšně upraven.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return back()->with('success', 'Zákazník byl smazán.');
    }

    public function import(ImportCustomerRequest $request): RedirectResponse
    {
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
