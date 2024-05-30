<?php

namespace CleanBandits\SingleActionResourceControllers;

interface ResourceController
{
    public function namespace(string $name, string $action): string;
}
