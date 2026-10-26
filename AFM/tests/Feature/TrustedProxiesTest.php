<?php

test('redirects stay on https when the reverse proxy forwards the original scheme', function () {
    $location = $this->get('/tank', ['X-Forwarded-Proto' => 'https'])
        ->assertRedirect()
        ->headers->get('Location');

    expect($location)->toStartWith('https://')->toEndWith('/login');
});

test('redirects stay on http when the request did not come through an https proxy', function () {
    $location = $this->get('/tank')
        ->assertRedirect()
        ->headers->get('Location');

    expect($location)->toStartWith('http://')->toEndWith('/login');
});
