<?php

namespace CleanBandits\SingleActionResourceControllers;

class DefaultResourceController implements ResourceController
{
    public function namespace(string $name, string $action): string
    {
        return config('single-action-resource-controllers.controllers_namespace') . $this->name($name) . '\\' . ucfirst($action) . 'Controller';
    }

    protected function name(string $name): string
    {
        return collect(explode('.', $name))->map(fn (string $name) => str($name)->studly())->join('\\');
    }
}
