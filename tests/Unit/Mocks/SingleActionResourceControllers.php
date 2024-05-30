<?php

namespace CleanBandits\SingleActionResourceControllers\Tests\Unit\Mocks;

use Illuminate\Routing\Controller;

class SingleActionResourceControllers extends Controller
{
    public function __invoke(): mixed {}
}
