<?php

// Memastikan assertion dasar Pest berjalan sesuai nilai yang diharapkan.
test('that true is true', function () {
    expect(true)->toBeTrue();
});
