<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services;

enum FieldType: string
{
    case Text = 'text';
    case Number = 'number';
    case Toggle = 'toggle';
    case Select = 'select';
    case Textarea = 'textarea';
    case Code = 'code';
    case Password = 'password';
}
