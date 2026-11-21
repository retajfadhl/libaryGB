<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Staff;
use Laravel\Sanctum\Sanctum;

function day(int $days): string
{
    return now()->addDays($days)->toDateString();
}

function makeBook(string $name = "Harry Potter", string $isbn = "111"): Book
{
    $category = Category::firstOrCreate(["name" => "Novels"], ["details" => "Stories and novels"]);
    return Book::create([
        "name" => $name, "ISBN" => $isbn, "author" => "A", "publisher" => "P",
        "publish_date" => "2000-01-01", "category_id" => $category->id,
    ]);
}

function makeCustomer(string $phone): Customer
{
    return Customer::create(["name" => "C$phone", "phone_number" => $phone, "password" => "password123"]);
}

function makeStaff(string $role = "admin"): Staff
{
    return Staff::create([
        "name" => "S", "email" => "$role@t.com", "password" => "password123",
        "role" => $role, "phone_number" => "09$role",
    ]);
}

function borrow(Customer $customer, Book $book, int $from, int $to)
{
    Sanctum::actingAs($customer, ["*"], "customers");
    return test()->postJson("/api/borrow", [
        "book_id" => $book->id, "borrowing_date" => day($from), "return_date" => day($to),
    ]);
}

test("task 1: a new customer can register and use the token", function () {
    $token = $this->postJson("/api/customer/register", [
        "name" => "New", "phone_number" => "0910000000", "password" => "password123",
    ])->assertStatus(201)->json("token");

    $this->withToken($token)->getJson("/api/customer-me")->assertOk();
});

test("task 2: staff manage customers, customers can't", function () {
    Sanctum::actingAs(makeStaff(), ["*"], "staff");
    $this->getJson("/api/customer")->assertOk();

    $this->app["auth"]->forgetGuards();
    Sanctum::actingAs(makeCustomer("0911111111"), ["*"], "customers");
    $this->getJson("/api/customer")->assertUnauthorized();
});

test("task 3: return date before borrowing date is rejected", function () {
    borrow(makeCustomer("0911111111"), makeBook(), 5, 2)->assertStatus(422);
    borrow(makeCustomer("0922222222"), makeBook("B", "222"), -1, 2)->assertStatus(422);
});

test("task 4: two customers can't have the same book on overlapping days", function () {
    $book = makeBook();
    $a = makeCustomer("0911111111");
    $b = makeCustomer("0922222222");

    borrow($a, $book, 1, 6)->assertStatus(201);
    borrow($b, $book, 3, 9)->assertStatus(422);
    borrow($b, $book, 7, 11)->assertStatus(201);

    $this->app["auth"]->forgetGuards();
    Sanctum::actingAs(makeStaff(), ["*"], "staff");
    $this->putJson("/api/borrow", ["customer_id" => $a->id, "book_id" => $book->id])->assertOk();

    $c = makeCustomer("0933333333");
    borrow($c, $book, 3, 5)->assertStatus(201);
});

test("task 5: a customer can't have more than 3 open borrowings", function () {
    $customer = makeCustomer("0911111111");
    foreach (["1", "2", "3"] as $i) {
        borrow($customer, makeBook("Book $i", $i), 1, 3)->assertStatus(201);
    }
    borrow($customer, makeBook("Book 4", "4"), 1, 3)->assertStatus(422);
});

test("tasks 6 and 7: staff see all and overdue borrowings", function () {
    $book = makeBook();
    $customer = makeCustomer("0911111111");
    $customer->books()->attach($book->id, [
        "borrowing_date" => day(-5), "return_date" => day(-1), "is_returned" => false,
    ]);

    Sanctum::actingAs(makeStaff(), ["*"], "staff");
    $this->getJson("/api/borrow/all")->assertOk()->assertJsonPath("data.0.book.id", $book->id);
    $this->getJson("/api/borrow/all?is_returned=1")->assertOk()->assertJsonCount(0, "data");
    $this->getJson("/api/borrow/overdue")->assertOk()->assertJsonCount(1, "data");

    $this->putJson("/api/borrow", ["customer_id" => $customer->id, "book_id" => $book->id])->assertOk();
    $this->getJson("/api/borrow/overdue")->assertOk()->assertJsonCount(0, "data");
});

test("tasks 8 and 9: search books and see if they are available", function () {
    $harry = makeBook("Harry Potter", "1");
    makeBook("Cosmos", "2");
    $customer = makeCustomer("0911111111");

    borrow($customer, $harry, 1, 3)->assertStatus(201);

    Sanctum::actingAs($customer);
    $this->getJson("/api/book?search=har")->assertOk()
        ->assertJsonCount(1, "data")
        ->assertJsonPath("data.0.is_available", false);
    $this->getJson("/api/book")->assertOk()->assertJsonCount(2, "data");
});

test("task 10: only admins manage staff", function () {
    Sanctum::actingAs(makeStaff("librarian"), ["*"], "staff");
    $this->postJson("/api/staff", [
        "name" => "X", "email" => "x@t.com", "password" => "password123",
        "role" => "admin", "phone_number" => "0999999999",
    ])->assertForbidden();
});

test("a customer can cancel a borrowing only before it starts", function () {
    $book = makeBook();
    $customer = makeCustomer("0911111111");

    borrow($customer, $book, 2, 4)->assertStatus(201);
    $this->deleteJson("/api/borrow/{$book->id}")->assertOk();
    $this->deleteJson("/api/borrow/{$book->id}")->assertNotFound();

    borrow($customer, $book, 0, 4)->assertStatus(201);
    $this->deleteJson("/api/borrow/{$book->id}")->assertNotFound();
});

test("a category with books can't be deleted", function () {
    $book = makeBook();
    Sanctum::actingAs(makeStaff(), ["*"], "staff");
    $this->deleteJson("/api/category/{$book->category_id}")->assertStatus(409);
});
