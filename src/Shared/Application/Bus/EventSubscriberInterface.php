<?php

declare(strict_types=1);

namespace Shared\Application\Bus;

/**
 * Event Subscriber Interface
 *
 * All event subscribers must implement this interface
 */
interface EventSubscriberInterface
{
    /**
     * Return the events this subscriber is listening to
     *
     * Format: [EventClass::class => 'methodName']
     *
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array;
}
