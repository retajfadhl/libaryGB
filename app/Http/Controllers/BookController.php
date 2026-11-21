<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookStoreRequst;
use App\Http\Requests\BookUpdateRequst;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $books = Book::query()
            ->when($request->search, fn ($query, $search) => $query->where("name", "like", "%$search%"))
            ->when($request->category_id, fn ($query, $id) => $query->where("category_id", $id))
            ->get();
        return BookResource::collection($books);
    }

    public function store(BookStoreRequst $request)
    {
        $book = Book::create($request->validated());
        return BookResource::make($book);
    }

    public function show(Book $book)
    {
        return BookResource::make($book->load("category"));
    }

    public function update(BookUpdateRequst $request, Book $book)
    {
        $book->update($request->validated());
        return BookResource::make($book);
    }

    public function destroy(Book $book)
    {
        if ($book->customers()->exists()) {
            return response()->json([
                "message" => "This book has borrowings and can't be deleted"
            ], 409);
        }
        $book->delete();
        return response()->json([
            "message" => "deleted"
        ]);
    }
}
