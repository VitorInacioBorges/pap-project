<?php

test('the application redirects the root to the login screen', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});
