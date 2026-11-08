<?php

use App\Models\Staff;

test('staff can login with valid credentials and receive a token', function () {
    // Arrange: Create a test staff member
    $staff = Staff::create([
        'name' => 'Test Staff',
        'email' => 'teststaff@gmail.com',
        'password' => bcrypt('password123'),
        'phone_number' => '0911234567',
        'role' => 'admin',
    ]);

    // Act: Send POST request to login
    $response = $this->postJson('/api/staff/login', [
        'email' => 'teststaff@gmail.com',
        'password' => 'password123',
    ]);

    // Assert: Check status and token return
    $response->assertStatus(200)
             ->assertJsonStructure(['token']);
});