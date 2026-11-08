<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware("auth:sanctum")->group(function () {
    Route::get("/staff", [StaffController::class, "index"]);
    Route::post("/staff", [StaffController::class, "store"]);
    Route::get("/staff/{id}", [StaffController::class, "show"]);
    Route::put("/staff/{id}", [StaffController::class, "update"]);
    Route::delete("/staff/{id}", [StaffController::class, "destroy"]);
    
    Route::get("/staff-me", [StaffAuthController::class, "me"]);
    Route::post("/staff-logout", [StaffAuthController::class, "logout"]);
});

// Public Customer Routes
Route::apiResource("customer", CustomerController::class)
    ->parameter("customer", "customer");

// Public Staff Login Route
Route::post("/staff/login", [StaffAuthController::class, "login"]);