<?php

Namespace Simplydigital\CaseManagement\Enums;

enum CustomerContactRoles: string
{
    case Sales_Contact = 'sales_contact';
    case Primary_Service_Contact = 'primary_service_contact';
    case Billing_Contact = 'billing_contact';
    case General_Contact = 'general_contact';

    public function label(): string
    {
        return match($this) {
            self::Sales_Contact => 'Sales Contact',
            self::Primary_Service_Contact => 'Primary Service Contact',
            self::Billing_Contact => 'Billing Contact',
            self::General_Contact => 'General Contact',
        };
    }

    public static function options(): array
    {
        return [
            self::Sales_Contact->value => self::Sales_Contact->label(),
            self::Primary_Service_Contact->value => self::Primary_Service_Contact->label(),
            self::Billing_Contact->value => self::Billing_Contact->label(),
            self::General_Contact->value => self::General_Contact->label(),
        ];
    }

}