# Filament Admin Panel - Login Instructions
# Filament Admin Panel - Login Instructions

## Admin Panel Access

The Filament admin panel is available at:

**URL**: `http://your-domain.com/admin`

## Default Admin Credentials

Two admin users are seeded in the database:

### Primary Admin Account
- **Email**: `admin@trissan.com`
- **Password**: `password`
- **Name**: Admin Trissan

### Test Account (for development)
- **Email**: `test@example.com`
- **Password**: `password`
- **Name**: Test User

## First-Time Setup

If you've just cloned the repository or reset the database:

```bash
# 1. Run migrations
php artisan migrate

# 2. Seed the database (creates admin users + CMS data)
php artisan db:seed

# 3. Start the development server
php artisan serve

# 4. Visit http://localhost:8000/admin
```

## Security Notes

⚠️ **IMPORTANT**: Change the default passwords in production!

```bash
# Generate a secure password hash
php artisan tinker
>>> Hash::make('your-secure-password-here')
```

Then update the user password in the database or via Filament's user management interface.

## Panel Features

The admin panel includes management for:
- ✅ Global Settings (company info, contact details, social links)
- ✅ Page Settings (hero sections, meta tags, featured content)
- ✅ About Company (vision, mission, facilities, milestones)
- ✅ Services & Service Clusters
- ✅ Works & Work Categories
- ✅ Portfolio Items & Categories
- ✅ Corporate Accreditations & Product Certifications
- ✅ Personnel & Specialty Clusters
- ✅ Contact Submissions (read-only)

## Troubleshooting

### Can't access `/admin`?
- Verify the server is running: `php artisan serve`
- Check the `.env` file has `APP_URL=http://localhost:8000`

### Login fails?
- Verify user exists: `php artisan tinker` → `User::all()`
- Re-run seeders: `php artisan db:seed --class=DatabaseSeeder`

### Permission denied?
- The `User` model implements `FilamentUser` with `canAccessPanel()` returning `true` for all users
- Check `app/Models/User.php` line 25-28

## Adding More Admin Users

Via Tinker:
```php
php artisan tinker
>>> User::create([
    'name' => 'New Admin',
    'email' => 'newadmin@trissan.com',
    'password' => Hash::make('secure-password'),
    'email_verified_at' => now(),
]);
```

Or use the Filament panel itself (if you have user management enabled).

---

**Last Updated**: October 19, 2026
