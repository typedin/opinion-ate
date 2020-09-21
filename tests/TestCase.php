<?php

namespace Tests;

use Helmich\JsonAssert\JsonAssertions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Traits\HasHeader;

abstract class TestCase extends BaseTestCase
{
    use JsonAssertions, HasHeader;

    const API_URL = "api/v1";

    use CreatesApplication;
}
