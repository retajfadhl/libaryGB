<?php

namespace App\Http\Controllers;

use App\Http\Requests\StaffStoreRequest;
use App\Http\Requests\StaffUpdateRequest;
use App\Http\Resources\StaffResource;
use App\Models\Staff;

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
    abort_if(auth()->user()->role !== "admin", 403, "Only admins can manage staff");
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
    abort_if(auth()->user()->role !== "admin", 403, "Only admins can manage staff");
    $staff = Staff::findOrFail($id);
    $staff->update($request->validated());
    return StaffResource::make($staff);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        abort_if(auth()->user()->role !== "admin", 403, "Only admins can manage staff");
        Staff::findOrFail($id)->delete();
        return response()->json("Staff member deleted successfully.");
    }
}
