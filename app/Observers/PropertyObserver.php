<?php

namespace App\Observers;

use App\Models\Property;

class PropertyObserver
{
    /**
     * Mismo patrón que OperationObserver: rechaza property_type/operation_type
     * fuera del enum real. Antes solo se validaba inline y duplicado en
     * PropertyController (create/update), sin nada que protegiera un
     * camino de alta nuevo (importador, comando, seeder, etc).
     */
    public function creating(Property $property): void
    {
        if ($property->property_type && !array_key_exists($property->property_type, Property::PROPERTY_TYPES)) {
            throw new \InvalidArgumentException("Property::property_type invalido: '{$property->property_type}'.");
        }

        if ($property->operation_type && !array_key_exists($property->operation_type, Property::OPERATION_TYPES)) {
            throw new \InvalidArgumentException("Property::operation_type invalido: '{$property->operation_type}'.");
        }
    }
}
