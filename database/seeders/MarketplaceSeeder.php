<?php

namespace Database\Seeders;

use App\Models\MarketplaceProduct;
use App\Models\MarketplaceProductImage;
use App\Models\School;
use App\Models\Students;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds demo data for the student marketplace: a storefront row in
 * `marketplaces` plus 4 products in `marketplace_products` (mixed
 * draft/published) for school1.student1@venturekids.test.
 *
 * Note: `marketplace_products.marketplace_id` is NOT NULL, but
 * Student\StudentMarketplaceController::save() (the real "Add Product" flow)
 * never sets it and MarketplaceProduct's $fillable doesn't include it either
 * — the `marketplaces` table isn't referenced anywhere else in the app. This
 * looks like a pre-existing bug (the storefront concept was likely dropped
 * but the column/table cleanup was never finished) that would break real
 * product creation today. This seeder inserts a `marketplaces` row directly
 * via DB::table() purely to satisfy the NOT NULL constraint for demo data;
 * it doesn't fix the underlying controller bug.
 */
class MarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        $studentUser = User::where('email', 'school1.student1@venturekids.test')->first();
        $student = $studentUser ? Students::where('user_id', $studentUser->id)->first() : null;
        $currencyId = DB::table('currencies')->orderBy('id')->value('id');
        $tenantId = $student ? optional(School::find($student->school_id))->tenant_id : null;

        if (!$student || !$currencyId) {
            $this->command?->error('Student school1.student1@venturekids.test or currencies not found — run SchoolSeeder first.');
            return;
        }

        $marketplaceId = DB::table('marketplaces')->where('student_id', $student->id)->value('id');

        if (!$marketplaceId) {
            $marketplaceId = DB::table('marketplaces')->insertGetId([
                'student_id' => $student->id,
                'name' => "{$studentUser->name}'s Shop",
                'currency_id' => $currencyId,
                'story' => 'A little shop of handmade and eco-friendly things I make myself, inspired by my VentureKids projects.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $products = [
            [
                'name' => 'GreenBite Reusable Snack Wrap',
                'story' => 'Made after learning how much plastic wrap gets thrown away after school snacks — this is my reusable, washable alternative.',
                'description' => 'A cotton-and-beeswax wrap that keeps snacks fresh and can be reused for a whole year, replacing hundreds of plastic wrappers.',
                'special_feature' => 'Washable, reusable, and comes in 3 fun colors.',
                'price' => 5.00,
                'status' => 1,
                'image' => 'greenbite-wrap.png',
            ],
            [
                'name' => 'Friendship Bracelet Kits',
                'story' => 'I love making bracelets with my friends, so I put together kits so anyone can make their own.',
                'description' => 'A beginner-friendly kit with thread, beads, and a simple pattern card to make 3 friendship bracelets.',
                'special_feature' => 'Includes a step-by-step pattern card for beginners.',
                'price' => 8.00,
                'status' => 1,
                'image' => 'friendship-bracelet-kit.png',
            ],
            [
                'name' => 'Hand-Poured Mini Candles',
                'story' => 'I started making candles as a hobby and realized they make great small gifts.',
                'description' => 'Small soy-wax candles poured by hand in reusable tins, lightly scented and safe for indoor use.',
                'special_feature' => 'Long burn time and a reusable tin you can keep.',
                'price' => 6.00,
                'status' => 0,
                'image' => 'mini-candles.png',
            ],
            [
                'name' => 'Custom Bookmark Set',
                'story' => 'I made these for my own books first, then realized my classmates wanted some too.',
                'description' => 'A set of 4 laminated paper bookmarks with hand-drawn designs, each with a coordinating ribbon tassel.',
                'special_feature' => 'Each set can be personalized with a name or initial.',
                'price' => 4.00,
                'status' => 0,
                'image' => 'bookmark-set.png',
            ],
        ];

        foreach ($products as $item) {
            $product = MarketplaceProduct::firstOrCreate(
                ['student_id' => $student->id, 'name' => $item['name']],
                [
                    'currency_id' => $currencyId,
                    'story' => $item['story'],
                    'description' => $item['description'],
                    'special_feature' => $item['special_feature'],
                    'price' => $item['price'],
                    'status' => $item['status'],
                ]
            );

            // marketplace_id isn't in $fillable (see class docblock), so it's
            // set explicitly here regardless of whether the row was just
            // created or already existed from a prior run.
            if ((int) $product->marketplace_id !== (int) $marketplaceId) {
                $product->marketplace_id = $marketplaceId;
                $product->save();
            }

            if ($tenantId && $product->images()->doesntExist()) {
                MarketplaceProductImage::create([
                    'marketplace_product_id' => $product->id,
                    'image_path' => 'tenants/' . $tenantId . '/student/marketplace/' . $item['image'],
                ]);
            }
        }

        $this->command?->info('Seeded 1 marketplace storefront with ' . count($products) . ' products for school1.student1.');
    }
}
