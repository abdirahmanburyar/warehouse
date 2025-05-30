<?php

namespace App\Http\Controllers;

use App\Events\GlobalPermissionChanged;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    /**
     * Test broadcasting a permission change event.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function testPermissionEvent(Request $request)
    {
        try {
            // Get the authenticated user or use a test user ID
            $userId = $request->input('user_id', auth()->id());
            $user = User::findOrFail($userId);
            
            Log::info('TestController: Testing permission event broadcast', [
                'user_id' => $user->id,
                'user_name' => $user->name
            ]);
            
            // Update the permission_updated_at timestamp
            $user->permission_updated_at = now();
            $user->save();
            
            // Manually broadcast the event
            event(new GlobalPermissionChanged($user));
            
            return response()->json([
                'success' => true,
                'message' => 'Test permission event broadcast successfully',
                'user_id' => $user->id
            ]);
        } catch (\Exception $e) {
            Log::error('TestController: Error broadcasting test event', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
