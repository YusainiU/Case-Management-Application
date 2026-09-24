<?php

namespace Simplydigital\AIManagement\AI\DTO;

final readonly class CaseContext
{

   /**
        @param array<string, mixed> $customer
        @param array<string, mixed> $asset
        @param array<int, array<string, mixed>> $comments
    */

   public function __construct(

       public int|string $id,

       public string $title,

       public string $description,

       public ?string $status,

       public ?string $priority,

       public ?string $category,

       public array $customer = [],

       public array $assets = [],

       public array $comments = [],

   ) {}

}
