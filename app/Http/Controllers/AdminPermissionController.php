<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;

class AdminPermissionController extends Controller
{
    // Define the system features/tabs you want to manage
    public static array $availableFeatures = [
        'supplies' => 'Supplies & Inventory',
        'patients' => 'Patient Records',
        'reports'  => 'Reports & Analytics',
        'family'   => 'Family Profiles',
        'audit_logs' => 'Audit Logs',
        'view_all_citizens'  => 'View All Citizens (Global)',
    ];

    public function index()
{
    // Filter specifically for users where the role is 'bhw' 
    // (or adjust 'bhw' if your database stores it differently, e.g., 'BHW')
    $bhwUsers = User::where('role', 'bhw')
                    ->with('permissions')
                    ->get();
                    
    $features = self::$availableFeatures;

    return view('admin.permissions', compact('bhwUsers', 'features'));
}

    public function update(Request $request, User $user)
    {
        $inputPermissions = $request->input('permissions', []);

        foreach (self::$availableFeatures as $featureKey => $featureLabel) {
            $accessLevel = $inputPermissions[$featureKey] ?? 'none'; 

            // For the view_all_citizens feature, treat 'write' or 'read' as enabled, 'none' as disabled
            if ($featureKey === 'view_all_citizens') {
                $accessLevel = ($accessLevel !== 'none') ? 'write' : 'none';
            } else {
                if (!in_array($accessLevel, ['none', 'read', 'write'])) {
                    $accessLevel = 'none';
                }
            }

            UserPermission::updateOrCreate(
                ['user_id' => $user->id, 'feature' => $featureKey],
                ['access' => $accessLevel]
            );
        }

        return redirect()->back()->with('success', "Permissions updated successfully for {$user->name}.");
    }
}
