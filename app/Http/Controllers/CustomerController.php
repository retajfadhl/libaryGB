<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerStoreRequest;
use App\Http\Requests\CustomerUpdateRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CustomerResource::collection(Customer::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerStoreRequest $request)
    {
        $customer = Customer::create($request->validated());
        return CustomerResource::make($customer);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return CustomerResource::make($customer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerUpdateRequest $request, Customer $customer)
    {
        $customer->update($request->validated());
        return CustomerResource::make($customer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->json(["message" => "deleted "]);
    }
}
