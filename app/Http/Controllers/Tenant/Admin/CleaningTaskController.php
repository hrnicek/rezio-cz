<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\CleaningTask;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CleaningTaskController extends Controller
{
    /**
     * Display a listing of the cleaning tasks.
     */
    public function index(Request $request)
    {
        $cleaningTasks = CleaningTask::with(['booking.property', 'booking.customer', 'property'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/CleaningTasks/Index', [
            'cleaningTasks' => $cleaningTasks,
        ]);
    }

    /**
     * Mark the given cleaning task as completed.
     */
    public function complete(CleaningTask $cleaningTask)
    {
        $cleaningTask->update(['completed_at' => now()]);

        return redirect()->route('admin.cleaning-tasks.index');
    }
}
