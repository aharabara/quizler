<?php

namespace App\Representation\Handler;

use App\Representation\RepresentAs;
use App\Representation\RepresentationType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AutoconfigureTag('representation.handler')]
interface RepresentationHandler
{
    public function getType(): RepresentationType;

    public function supports(Request $request, array $data, array $representations): bool;

    public function handle(Request $request, array $data, array $representations): Response;

}
