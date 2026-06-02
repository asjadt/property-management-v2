<?php

namespace App\Models;

class PropertyStatus
{
    public const AVAILABLE = 'available';
    public const OCCUPIED = 'occupied';
    public const RESERVED = 'reserved';
    public const UNDER_MAINTENANCE = 'under_maintenance';
    public const OFF_MARKET = 'off_market';
    public const EVICTION = 'eviction';
    public const NOTICE_GIVEN = 'notice_given';
    public const UNDER_OFFER = 'under_offer';
    public const SOLD = 'sold';
    public const BLOCKED = 'blocked';

    public static function values(): array
    {
        return [
            self::AVAILABLE,
            self::OCCUPIED,
            self::RESERVED,
            self::UNDER_MAINTENANCE,
            self::OFF_MARKET,
            self::EVICTION,
            self::NOTICE_GIVEN,
            self::UNDER_OFFER,
            self::SOLD,
            self::BLOCKED,
        ];
    }

    public static function options(): array
    {
        return [
            [
                'value' => self::AVAILABLE,
                'label' => 'Available / Vacant',
                'description' => 'The property or unit is empty and ready to be rented or sold.',
            ],
            [
                'value' => self::OCCUPIED,
                'label' => 'Occupied / Tenanted',
                'description' => 'A tenant is currently living in or using the property under a lease agreement.',
            ],
            [
                'value' => self::RESERVED,
                'label' => 'Reserved / Pending',
                'description' => 'An application has been approved or a deal is in progress, but move-in or contracts are not fully finalized.',
            ],
            [
                'value' => self::UNDER_MAINTENANCE,
                'label' => 'Under Maintenance / Repairs',
                'description' => 'The property is temporarily unavailable due to repairs, renovations, or cleaning.',
            ],
            [
                'value' => self::OFF_MARKET,
                'label' => 'Off Market',
                'description' => 'The property is not currently being advertised or offered for rent/sale.',
            ],
            [
                'value' => self::EVICTION,
                'label' => 'Eviction / Legal Process',
                'description' => 'The tenant is in the process of being removed due to non-payment or lease violations.',
            ],
            [
                'value' => self::NOTICE_GIVEN,
                'label' => 'Notice Given',
                'description' => 'The current tenant has given notice they plan to leave, so the unit will soon become vacant.',
            ],
            [
                'value' => self::UNDER_OFFER,
                'label' => 'Under Offer',
                'description' => 'Buyer interest is confirmed and the deal is in progress.',
            ],
            [
                'value' => self::SOLD,
                'label' => 'Sold',
                'description' => 'The property sale transaction has been completed.',
            ],
            [
                'value' => self::BLOCKED,
                'label' => 'Blocked / Held',
                'description' => 'The property is temporarily unavailable due to owner use, internal hold, or administrative reasons.',
            ],
        ];
    }
}
