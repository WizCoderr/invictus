<?php

namespace Database\Seeders;

use App\Models\BusinessCategory;
use App\Models\Chapter;
use Illuminate\Database\Seeder;

class RegistrationSeeder extends Seeder
{
    public function run(): void
    {
        // firstOrCreate: safe to re-run on a live site without duplicating anything.
        foreach (['Protector', 'Solis', 'Creator', 'Emperor', 'Elysium'] as $name) {
            Chapter::firstOrCreate(['name' => $name]);
        }

        // Samples to start from — the organiser edits the real list in Admin > Event Setup.
        $categories = [
            'Accounting & Taxation', 'Architecture', 'Automobile', 'Banking & Finance',
            'Catering & Food', 'Civil Construction', 'Digital Marketing', 'Education & Training',
            'Electrical Contracting', 'Event Management', 'Healthcare', 'Hospitality',
            'Insurance', 'Interior Design', 'IT & Software', 'Jewellery',
            'Legal Services', 'Logistics & Transport', 'Manufacturing', 'Photography & Video',
            'Printing & Packaging', 'Real Estate', 'Solar Energy', 'Textiles & Garments',
            'Travel & Tourism',
        ];

        foreach ($categories as $name) {
            BusinessCategory::firstOrCreate(['name' => $name]);
        }
    }
}
