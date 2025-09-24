<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // For now, we'll return static roles since we're not using a roles table
        $roles = [
            ['id' => 1, 'name' => 'admin', 'display_name' => 'Administrator'],
            ['id' => 2, 'name' => 'manager', 'display_name' => 'Manager'],
            ['id' => 3, 'name' => 'cashier', 'display_name' => 'Cashier'],
        ];
        
        return response()->json($roles);
    }

    /**
     * Store a newly created role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // In a full implementation, this would create a new role
        return response()->json(['message' => 'Role creation not implemented'], 405);
    }

    /**
     * Display the specified role.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // For now, we'll return static roles since we're not using a roles table
        $roles = [
            1 => ['id' => 1, 'name' => 'admin', 'display_name' => 'Administrator'],
            2 => ['id' => 2, 'name' => 'manager', 'display_name' => 'Manager'],
            3 => ['id' => 3, 'name' => 'cashier', 'display_name' => 'Cashier'],
        ];
        
        if (!isset($roles[$id])) {
            return response()->json(['message' => 'Role not found'], 404);
        }
        
        return response()->json($roles[$id]);
    }

    /**
     * Update the specified role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // In a full implementation, this would update a role
        return response()->json(['message' => 'Role update not implemented'], 405);
    }

    /**
     * Remove the specified role.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // In a full implementation, this would delete a role
        return response()->json(['message' => 'Role deletion not implemented'], 405);
    }
}