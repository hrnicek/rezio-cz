<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FilePondController extends Controller
{
    /**
     * Upload a file to temporary storage.
     */
    public function process(Request $request)
    {
        $request->validate([
            'filepond' => ['required', 'file'],
        ]);

        $file = $request->file('filepond');

        // Store in a temporary directory.
        // Since this is a tenant controller, Storage::disk('public') might be tenant-scoped if configured,
        // or we might need to use a specific disk.
        // For now, we'll assume the default disk is appropriate or we use 'public'.
        // We'll store it in 'tmp' directory.

        $path = $file->store('tmp', 'public');

        // Return the path as the serverId
        return $path;
    }

    /**
     * Delete a file from temporary storage.
     */
    public function revert(Request $request)
    {
        // The body contains the serverId which is the path we returned in process
        $path = $request->getContent();

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        return response('');
    }
}
