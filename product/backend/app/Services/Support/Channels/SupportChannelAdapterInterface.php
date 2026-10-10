<?php

namespace App\Services\Support\Channels;

use App\Models\Organization;
use App\Models\Support\SupportTicketComment;

interface SupportChannelAdapterInterface
{
    public function getChannel(): string;

    public function processInbound(Organization $organization, array $payload): ?SupportTicketComment;

    public function dispatchOutbound(SupportTicketComment $comment): bool;
}
