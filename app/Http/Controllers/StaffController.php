<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return StaffResource::collection(Staff::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StaffStoreRequest $request)
{
    $staff = Staff::create($request->validated());
    return StaffResource::make($staff);
}

    /**
     * Display the specified resource.
     */
    public function show( $id)
    {
        return StaffResource::make(Staff::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(StaffUpdateRequest $request, $id)
{
    $staff = Staff::findOrFail($id);
    $staff->update($request->validated());
    return StaffResource::make($staff);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Staff::findOrFail($id)->delete();
        return response()->json("Staff member deleted successfully.");
    }
}
