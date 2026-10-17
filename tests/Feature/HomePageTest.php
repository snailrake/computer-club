<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_root_redirects_to_the_catalog(): void
    {
        $this->get('/')->assertRedirect('/computers');
    }
}
