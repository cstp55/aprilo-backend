<?php

namespace App\Enums;

enum SourceStatus: string
{
    case Uploaded = 'uploaded';
    case Indexing = 'indexing';
    case Indexed = 'indexed';
    case Failed = 'failed';
    case Inactive = 'inactive';
}
