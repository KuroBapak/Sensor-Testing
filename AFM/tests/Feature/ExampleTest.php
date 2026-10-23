<?php

test('the home page sends visitors to the main tank view', function () {
    $this->get(route('home'))->assertRedirect(route('tank.index'));
});
