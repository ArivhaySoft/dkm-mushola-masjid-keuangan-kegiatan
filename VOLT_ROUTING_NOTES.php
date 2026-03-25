<?php
// FILE: resources/views/livewire/home.php (Volt component PHP class)
// The blade files above serve as combined Volt single-file components
// The following are the PHP-only component files needed for Volt routing

/*
  NOTE: In Laravel Livewire Volt, you have two options:
  1. Single-file components: the .blade.php file contains both <?php ... ?> (component class) and HTML
  2. Separate class files in resources/views/livewire/

  For this project, we use SINGLE-FILE Volt components where the blade file
  contains both the PHP class definition (using "new class extends Component { ... }; ?>")
  and the Blade template.

  The routing in routes/web.php uses Volt::route() which maps directly to
  the blade file paths under resources/views/livewire/
*/

// Volt component path mappings:
// Volt::route('/', 'home')                  → resources/views/livewire/home.blade.php
// Volt::route('/arus-kas', 'arus-kas.index') → resources/views/livewire/arus-kas/index.blade.php
// Volt::route('/laporan/periodik', 'laporan.periodik') → resources/views/livewire/laporan/periodik.blade.php
// Volt::route('/laporan/bulanan', 'laporan.bulanan')   → resources/views/livewire/laporan/bulanan.blade.php
// Volt::route('/laporan/tahunan', 'laporan.tahunan')   → resources/views/livewire/laporan/tahunan.blade.php
// Volt::route('/kegiatan', 'kegiatan.index')           → resources/views/livewire/kegiatan/index.blade.php
// Volt::route('/profile', 'profile')                   → resources/views/livewire/profile.blade.php
// Volt::route('/hak-akses', 'hak-akses')               → resources/views/livewire/hak-akses.blade.php
