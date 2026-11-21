<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Customer;
use Illuminate\Http\Request;

class borrowingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return response()->json(["data" => $user->books]);
    }

    public function borrow(Request $request){
        $data = $request->validate([
            "book_id"        => ["required", "exists:books,id"],
            "borrowing_date" => ["required", "date", "after_or_equal:today"],
            "return_date"    => ["required", "date", "after:borrowing_date"]
        ]);

        $user = auth()->user();

        if ($user->books()->wherePivot("is_returned", false)->count() >= 3) {
            return response()->json([
                "message" => "You already have 3 books that are not returned"
            ], 422);
        }

        // Two date ranges overlap when old start <= new end and old end >= new start
        $taken = Borrowing::where("book_id", $data["book_id"])
            ->where("is_returned", false)
            ->where("borrowing_date", "<=", $data["return_date"])
            ->where("return_date", ">=", $data["borrowing_date"])
            ->exists();

        if ($taken) {
            return response()->json([
                "message" => "This book is already borrowed for these dates"
            ], 422);
        }

        $user->books()->attach(
            [$data["book_id"]],
            ["borrowing_date" => $data["borrowing_date"],
             "return_date"    => $data["return_date"],
             "is_returned"    => false]
        );
        return response()->json(["message" => "Book borrowed"], 201);
    }

    public function destroy($id){
        $user = auth()->user();

        // Only a borrowing that has not started yet can be cancelled, so the history stays
        $deleted = $user->books()
            ->wherePivot("borrowing_date", ">", now()->toDateString())
            ->detach($id);

        if (!$deleted) {
            return response()->json([
                "message" => "No borrowing of this book that can still be cancelled"
            ], 404);
        }
        return response()->json(["message" => "done"]);
    }

    public function markAsReturned(Request $request){
        $request->validate([
            "customer_id" => ["required", "exists:customers,id"],
            "book_id"     => ["required", "exists:books,id"],
        ]);

        $customer = Customer::find($request->customer_id);

        $borrowing = $customer->books()
            ->wherePivot("book_id", $request->book_id)
            ->wherePivot("is_returned", false)
            ->first();

        if (!$borrowing) {
            return response()->json([
                "message" => "This customer has no open borrowing for this book"
            ], 404);
        }

        $customer->books()
            ->wherePivot("is_returned", false)
            ->updateExistingPivot($request->book_id, [
                "is_returned" => true,
            ]);

        return response()->json([
            "message"        => "Book marked as returned",
            "customer_id"    => $customer->id,
            "book_id"        => $borrowing->id,
            "borrowing_date" => $borrowing->pivot->borrowing_date,
        ]);
    }

    public function all(Request $request){
        $borrowings = Borrowing::with("customer", "book");

        if ($request->has("is_returned")) {
            $borrowings->where("is_returned", $request->boolean("is_returned"));
        }

        return response()->json(["data" => $borrowings->get()]);
    }

    public function overdue(){
        $borrowings = Borrowing::with("customer", "book")
            ->where("is_returned", false)
            ->where("return_date", "<", now()->toDateString())
            ->get();

        return response()->json(["data" => $borrowings]);
    }
}
