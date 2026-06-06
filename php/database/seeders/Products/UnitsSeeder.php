<?php

namespace Database\Seeders\Products;

use App\Models\Products\Unit;
use Illuminate\Database\Seeder;

class UnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unitsData = [
            ['name' => 'Kilogram', 'abbreviation' => 'kg', 'description' => 'Unit of mass/weight', 'is_active' => true],
            ['name' => 'Gram', 'abbreviation' => 'g', 'description' => 'Unit of mass', 'is_active' => true],
            ['name' => 'Liter', 'abbreviation' => 'L', 'description' => 'Unit of volume', 'is_active' => true],
            ['name' => 'Milliliter', 'abbreviation' => 'ml', 'description' => 'Unit of volume', 'is_active' => true],
            ['name' => 'Meter', 'abbreviation' => 'm', 'description' => 'Unit of length', 'is_active' => true],
            ['name' => 'Centimeter', 'abbreviation' => 'cm', 'description' => 'Unit of length', 'is_active' => true],
            ['name' => 'Piece', 'abbreviation' => 'pcs', 'description' => 'Individual items', 'is_active' => true],
            ['name' => 'Box', 'abbreviation' => 'box', 'description' => 'Box containing items', 'is_active' => true],
            ['name' => 'Dozen', 'abbreviation' => 'dz', 'description' => 'Set of twelve items', 'is_active' => true],
            ['name' => 'Pack', 'abbreviation' => 'pk', 'description' => 'Package of items', 'is_active' => true],
        ];

        foreach ($unitsData as $unit) {
            Unit::create($unit);
        }
    }
}
