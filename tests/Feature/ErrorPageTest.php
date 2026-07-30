<?php

use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['app.debug' => false]);

    Route::get('/__test/http-error/{status}', fn (int $status) => abort($status))
        ->whereNumber('status');

    Route::get('/__test/server-error', function () {
        throw new RuntimeException('Unexpected test exception.');
    });
});

test('web http exceptions render the branded inertia error page', function (int $status) {
    $this->get("/__test/http-error/{$status}")
        ->assertStatus($status)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Error')
            ->where('status', $status)
        );
})->with([400, 401, 403, 404, 405, 419, 422, 429, 503]);

test('server exceptions render the branded 500 error page when debug is disabled', function () {
    $this->get('/__test/server-error')
        ->assertStatus(500)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Error')
            ->where('status', 500)
        );
});

test('json requests keep json error responses', function () {
    $this->getJson('/__test/http-error/404')
        ->assertStatus(404)
        ->assertHeaderMissing('X-Inertia');
});
