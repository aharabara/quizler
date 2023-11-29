<?php

namespace App\Representation;

enum RepresentationType: string
{
    case TURBO = 'turbo';
    case HTML = 'html';
    case FORM_SUBMITTED = 'form_submitted';
}
