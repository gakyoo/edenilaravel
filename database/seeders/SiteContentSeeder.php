<?php

namespace Database\Seeders;

use App\Models\SiteContent;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    /** Default editable site content (keys map to what the frontend displays). */
    public function run(): void
    {
        $defaults = [
            // Hero section
            ['key' => 'hero_eyebrow', 'value' => 'Edeni Realtors — Tanzania', 'group' => 'hero'],
            ['key' => 'hero_title', 'value' => "Find Your Next Home or Investment in Tanzania", 'group' => 'hero'],
            ['key' => 'hero_subtitle', 'value' => 'Houses, apartments, land, and commercial properties across the country. Browse listings, enquire on WhatsApp, and work with verified agents.', 'group' => 'hero'],

            // Stats strip
            ['key' => 'stat_1_value', 'value' => '100+', 'group' => 'stats'],
            ['key' => 'stat_1_label', 'value' => 'Properties listed', 'group' => 'stats'],
            ['key' => 'stat_2_value', 'value' => '5+', 'group' => 'stats'],
            ['key' => 'stat_2_label', 'value' => 'Regions covered', 'group' => 'stats'],
            ['key' => 'stat_3_value', 'value' => '24/7', 'group' => 'stats'],
            ['key' => 'stat_3_label', 'value' => 'WhatsApp enquiries', 'group' => 'stats'],

            // Contact / business
            ['key' => 'contact_whatsapp', 'value' => '+255 759 210 560', 'group' => 'contact'],
            ['key' => 'contact_whatsapp_raw', 'value' => '255759210560', 'group' => 'contact'],
            ['key' => 'contact_email', 'value' => 'info@edenire.co.tz', 'group' => 'contact'],
            ['key' => 'contact_location', 'value' => 'Arusha, Tanzania', 'group' => 'contact'],

            // Footer / SEO
            ['key' => 'footer_about', 'value' => "Tanzania's real estate platform by Edeni Realtors. Buy, sell, and rent with confidence.", 'group' => 'footer'],
            ['key' => 'seo_description', 'value' => "Tanzania's real estate platform by Edeni Realtors. Browse verified properties for sale and rent across Dar es Salaam, Arusha, Mwanza, Dodoma and Zanzibar. Enquire on WhatsApp.", 'group' => 'seo'],
        ];

        foreach ($defaults as $row) {
            SiteContent::updateOrCreate(['key' => $row['key']], $row);
        }
    }
}
