<?php

// 1. Migration untuk Users table (enhanced)
// database/migrations/2024_01_01_000001_create_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('avatar')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};

// 2. Migration untuk Event Organizers
// database/migrations/2024_01_01_000002_create_event_organizers_table.php

return new class extends Migration
{
    public function up()
    {
        Schema::create('event_organizers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('organization_name');
            $table->string('organization_slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->string('website')->nullable();
            $table->string('instagram')->nullable();
            $table->string('twitter')->nullable();
            $table->string('facebook')->nullable();
            $table->text('address');
            $table->string('city');
            $table->string('province');
            $table->string('postal_code');
            $table->string('contact_person');
            $table->string('contact_phone');
            $table->string('contact_email');
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('verification_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_organizers');
    }
};

// 3. Migration untuk Categories
// database/migrations/2024_01_01_000003_create_categories_table.php

return new class extends Migration
{
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('categories');
    }
};

// 4. Migration untuk Events
// database/migrations/2024_01_01_000004_create_events_table.php

return new class extends Migration
{
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('event_organizers')->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('terms_conditions')->nullable();
            $table->string('banner_image');
            $table->json('gallery_images')->nullable();
            $table->enum('type', ['online', 'offline', 'hybrid'])->default('offline');
            $table->string('venue_name')->nullable();
            $table->text('venue_address')->nullable();
            $table->string('venue_city')->nullable();
            $table->string('venue_province')->nullable();
            $table->decimal('venue_latitude', 10, 8)->nullable();
            $table->decimal('venue_longitude', 11, 8)->nullable();
            $table->string('online_platform')->nullable();
            $table->text('online_link')->nullable();
            $table->datetime('start_datetime');
            $table->datetime('end_datetime');
            $table->datetime('registration_start');
            $table->datetime('registration_end');
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->integer('views_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'start_datetime']);
            $table->index(['category_id', 'status']);
            $table->index(['organizer_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('events');
    }
};

// 5. Migration untuk Ticket Types
// database/migrations/2024_01_01_000005_create_ticket_types_table.php

return new class extends Migration
{
    public function up()
    {
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->integer('quantity');
            $table->integer('sold_quantity')->default(0);
            $table->integer('min_purchase')->default(1);
            $table->integer('max_purchase')->default(10);
            $table->datetime('sale_start');
            $table->datetime('sale_end');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('benefits')->nullable(); // JSON array of benefits
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ticket_types');
    }
};

// 6. Migration untuk Orders
// database/migrations/2024_01_01_000006_create_orders_table.php

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('restrict');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('admin_fee', 12, 2)->default(0);
            $table->decimal('payment_fee', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['pending', 'paid', 'cancelled', 'refunded', 'expired'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'failed', 'refunded'])->default('unpaid');
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->datetime('paid_at')->nullable();
            $table->datetime('expired_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['event_id', 'status']);
            $table->index('order_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};

// 7. Migration untuk Order Items (Tickets)
// database/migrations/2024_01_01_000007_create_order_items_table.php

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_type_id')->constrained()->onDelete('restrict');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
    }
};

// 8. Migration untuk Tickets (Individual tickets)
// database/migrations/2024_01_01_000008_create_tickets_table.php

return new class extends Migration
{
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code')->unique();
            $table->string('qr_code')->unique();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_type_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('attendee_name');
            $table->string('attendee_email');
            $table->string('attendee_phone')->nullable();
            $table->enum('status', ['active', 'used', 'cancelled', 'transferred'])->default('active');
            $table->datetime('used_at')->nullable();
            $table->foreignId('used_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('ticket_code');
            $table->index('qr_code');
            $table->index(['user_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};

// 9. Migration untuk Merchandise Categories
// database/migrations/2024_01_01_000009_create_merchandise_categories_table.php

return new class extends Migration
{
    public function up()
    {
        Schema::create('merchandise_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('merchandise_categories');
    }
};

// 10. Migration untuk Merchandise
// database/migrations/2024_01_01_000010_create_merchandise_table.php

return new class extends Migration
{
    public function up()
    {
        Schema::create('merchandise', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('event_organizers')->onDelete('cascade');
            $table->foreignId('event_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('merchandise_category_id')->constrained()->onDelete('restrict');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('specification')->nullable();
            $table->string('main_image');
            $table->json('gallery_images')->nullable();
            $table->decimal('base_price', 12, 2);
            $table->integer('stock_quantity')->default(0);
            $table->integer('sold_quantity')->default(0);
            $table->decimal('weight', 8, 2)->nullable(); // in grams
            $table->json('dimensions')->nullable(); // {length, width, height}
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['organizer_id', 'is_active']);
            $table->index(['event_id', 'is_active']);
            $table->index(['merchandise_category_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('merchandise');
    }
};

// 11. Migration untuk Merchandise Variants (Size, Color, etc.)
// database/migrations/2024_01_01_000011_create_merchandise_variants_table.php

return new class extends Migration
{
    public function up()
    {
        Schema::create('merchandise_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchandise_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Size S - Red"
            $table->json('attributes'); // {"size": "S", "color": "Red"}
            $table->string('sku')->unique();
            $table->decimal('price_adjustment', 12, 2)->default(0); // + or - from base price
            $table->integer('stock_quantity')->default(0);
            $table->integer('sold_quantity')->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('merchandise_variants');
    }
};

// 12. Migration untuk Merchandise Orders
// database/migrations/2024_01_01_000012_create_merchandise_orders_table.php

return new class extends Migration
{
    public function up()
    {
        Schema::create('merchandise_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('organizer_id')->constrained('event_organizers')->onDelete('restrict');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('admin_fee', 12, 2)->default(0);
            $table->decimal('payment_fee', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'failed', 'refunded'])->default('unpaid');
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->datetime('paid_at')->nullable();
            $table->datetime('expired_at');

            // Shipping Information
            $table->string('shipping_name');
            $table->string('shipping_phone');
            $table->text('shipping_address');
            $table->string('shipping_city');
            $table->string('shipping_province');
            $table->string('shipping_postal_code');
            $table->string('shipping_method')->nullable();
            $table->string('tracking_number')->nullable();
            $table->datetime('shipped_at')->nullable();
            $table->datetime('delivered_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['organizer_id', 'status']);
            $table->index('order_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('merchandise_orders');
    }
};

// 13. Migration untuk Merchandise Order Items
// database/migrations/2024_01_01_000013_create_merchandise_order_items_table.php

return new class extends Migration
{
    public function up()
    {
        Schema::create('merchandise_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchandise_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('merchandise_id')->constrained()->onDelete('restrict');
            $table->foreignId('merchandise_variant_id')->nullable()->constrained()->onDelete('restrict');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->json('variant_details')->nullable(); // Snapshot of variant at time of order
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('merchandise_order_items');
    }
};

// 14. Migration untuk Coupons/Promo Codes
// database/migrations/2024_01_01_000014_create_coupons_table.php

return new class extends Migration
{
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->nullable()->constrained('event_organizers')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed_amount']);
            $table->decimal('value', 12, 2);
            $table->decimal('minimum_amount', 12, 2)->nullable();
            $table->decimal('maximum_discount', 12, 2)->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->integer('usage_limit_per_user')->nullable();
            $table->datetime('valid_from');
            $table->datetime('valid_until');
            $table->enum('applicable_to', ['tickets', 'merchandise', 'both'])->default('tickets');
            $table->json('applicable_events')->nullable(); // Array of event IDs
            $table->json('applicable_merchandise')->nullable(); // Array of merchandise IDs
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('code');
            $table->index(['organizer_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupons');
    }
};

// 15. Migration untuk Coupon Usage
// database/migrations/2024_01_01_000015_create_coupon_usages_table.php

return new class extends Migration
{
    public function up()
    {
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('merchandise_order_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('discount_amount', 12, 2);
            $table->timestamps();

            $table->index(['coupon_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupon_usages');
    }
};

// 16. Migration untuk Reviews/Ratings
// database/migrations/2024_01_01_000016_create_reviews_table.php

return new class extends Migration
{
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('merchandise_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('merchandise_order_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_verified_purchase')->default(false);
            $table->boolean('is_approved')->default(true);
            $table->datetime('approved_at')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'is_approved']);
            $table->index(['merchandise_id', 'is_approved']);
            $table->index(['user_id', 'is_approved']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reviews');
    }
};

// 17. Migration untuk Notifications
// database/migrations/2024_01_01_000017_create_notifications_table.php

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};

// 18. Migration untuk Settings
// database/migrations/2024_01_01_000018_create_settings_table.php

return new class extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->text('description')->nullable();
            $table->string('group')->default('general');
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};

// 19. Migration untuk Event Tags (Many-to-Many)
// database/migrations/2024_01_01_000019_create_tags_table.php

return new class extends Migration
{
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tags');
    }
};

// 20. Migration untuk Event Tags Pivot
// database/migrations/2024_01_01_000020_create_event_tags_table.php

return new class extends Migration
{
    public function up()
    {
        Schema::create('event_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['event_id', 'tag_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_tags');
    }
};

// 21. Migration untuk Wishlists
// database/migrations/2024_01_01_000021_create_wishlists_table.php

return new class extends Migration
{
    public function up()
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('merchandise_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'event_id']);
            $table->unique(['user_id', 'merchandise_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('wishlists');
    }
};

// 22. Seeder untuk Default Roles dan Permissions
// database/seeders/RolePermissionSeeder.php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Event Management
            'view events',
            'create events',
            'edit events',
            'delete events',
            'publish events',

            // Ticket Management
            'view tickets',
            'create tickets',
            'edit tickets',
            'delete tickets',
            'scan tickets',

            // Order Management
            'view orders',
            'edit orders',
            'refund orders',

            // Merchandise Management
            'view merchandise',
            'create merchandise',
            'edit merchandise',
            'delete merchandise',

            // Merchandise Order Management
            'view merchandise orders',
            'edit merchandise orders',
            'ship merchandise orders',

            // Organizer Management
            'view organizers',
            'verify organizers',
            'suspend organizers',

            // Coupon Management
            'view coupons',
            'create coupons',
            'edit coupons',
            'delete coupons',

            // Review Management
            'view reviews',
            'moderate reviews',

            // Analytics
            'view analytics',
            'view reports',

            // System Settings
            'view settings',
            'edit settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Super Admin Role
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin Role
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'view users',
            'create users',
            'edit users',
            'view events',
            'edit events',
            'publish events',
            'view tickets',
            'scan tickets',
            'view orders',
            'edit orders',
            'refund orders',
            'view merchandise',
            'edit merchandise',
            'view merchandise orders',
            'edit merchandise orders',
            'ship merchandise orders',
            'view organizers',
            'verify organizers',
            'view coupons',
            'edit coupons',
            'view reviews',
            'moderate reviews',
            'view analytics',
            'view reports',
        ]);

        // Event Organizer Role
        $organizer = Role::create(['name' => 'organizer']);
        $organizer->givePermissionTo([
            'view events',
            'create events',
            'edit events',
            'delete events',
            'view tickets',
            'create tickets',
            'edit tickets',
            'scan tickets',
            'view orders',
            'edit orders',
            'view merchandise',
            'create merchandise',
            'edit merchandise',
            'delete merchandise',
            'view merchandise orders',
            'edit merchandise orders',
            'ship merchandise orders',
            'view coupons',
            'create coupons',
            'edit coupons',
            'delete coupons',
            'view reviews',
            'view analytics',
        ]);

        // Customer Role
        $customer = Role::create(['name' => 'customer']);
        $customer->givePermissionTo([
            'view events',
            'view tickets',
            'view orders',
            'view merchandise',
            'view merchandise orders',
        ]);
    }
}
