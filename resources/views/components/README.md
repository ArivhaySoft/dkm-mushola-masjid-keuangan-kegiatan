{{--
  FILE: resources/views/components/flash-message.blade.php
  Already created above.

  USAGE NOTE:
  In Volt components, to show toast notification use:
    session()->flash('success', 'Pesan berhasil.');
    session()->flash('error',   'Terjadi kesalahan.');
    session()->flash('warning', 'Perhatian!');

  The flash-message component renders automatically in the layout.
  It auto-dismisses after 4.5 seconds.
--}}
