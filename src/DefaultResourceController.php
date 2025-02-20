<?php

namespace CleanBandits\SingleActionResourceControllers;

class DefaultResourceController implements ResourceController
{
    public function namespace(string $name, string $action, ?string $namespace = null): string
    {
        return $this->namespaceBase($namespace) . '\\' . $this->name($name) . '\\' . ucfirst($action) . 'Controller';
    }

    protected function name(string $name): string
    {
        return collect(explode('.', $name))->map(fn (string $name) => str($name)->studly())->join('\\');
    }

    protected function namespaceBase(?string $namespace = null): string
    {
        return rtrim($namespace ?? config('single-action-resource-controllers.controllers_namespace'), '\\');
    }
}
