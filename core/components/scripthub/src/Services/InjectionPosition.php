<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services;

enum InjectionPosition: string
{
    case Head = 'head';
    case BodyEnd = 'body_end';
    case AfterBodyOpen = 'after_body_open';
}
