<!-- Header -->
<header class="bg-gray-800 text-white fixed top-0 left-0 right-0 z-10">
  <div class="px-4 py-2 flex flex-col sm:flex-row sm:justify-between">
    <!-- Top Bar -->
    <div class="flex items-center justify-between w-full">
        <!-- Phone Number and Live Support -->

        @if (!request()->is('/') && !request()->is('product-details*'))
        <button data-drawer-target="sidebar-multi-level-sidebar" data-drawer-toggle="sidebar-multi-level-sidebar" aria-controls="sidebar-multi-level-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-400 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
            <span class="sr-only">Open sidebar</span>
            <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
            </svg>
        </button>
        @endif
        <div class="flex items-center space-x-4">
            <div class="hidden sm:flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                <path fill-rule="evenodd" d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 0 1-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 0 0 6.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 0 1 1.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 0 1-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5Z" clip-rule="evenodd" />
            </svg>

          <span>212.840.8463</span>
        </div>
        <div class="hidden sm:flex items-center space-x-2">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
  <path d="M4.913 2.658c2.075-.27 4.19-.408 6.337-.408 2.147 0 4.262.139 6.337.408 1.922.25 3.291 1.861 3.405 3.727a4.403 4.403 0 0 0-1.032-.211 50.89 50.89 0 0 0-8.42 0c-2.358.196-4.04 2.19-4.04 4.434v4.286a4.47 4.47 0 0 0 2.433 3.984L7.28 21.53A.75.75 0 0 1 6 21v-4.03a48.527 48.527 0 0 1-1.087-.128C2.905 16.58 1.5 14.833 1.5 12.862V6.638c0-1.97 1.405-3.718 3.413-3.979Z" />
  <path d="M15.75 7.5c-1.376 0-2.739.057-4.086.169C10.124 7.797 9 9.103 9 10.609v4.285c0 1.507 1.128 2.814 2.67 2.94 1.243.102 2.5.157 3.768.165l2.782 2.781a.75.75 0 0 0 1.28-.53v-2.39l.33-.026c1.542-.125 2.67-1.433 2.67-2.94v-4.286c0-1.505-1.125-2.811-2.664-2.94A49.392 49.392 0 0 0 15.75 7.5Z" />
</svg>

          <span>Live Support</span>
        </div>
      </div>
      <!-- Logo (Positioned to the left on mobile) -->
      <div class="flex items-center justify-between w-full sm:w-auto sm:justify-start">
      <img id="header-image" class="w-16 transition-content w-24 mx-auto sm:mx-0 sm:w-auto sm:ml-auto lg:w-auto" aria-label="swiss made corp logo" src="/images/swissmade-logo-white.png" alt="Swiss Made Corp." />
      </div>
      <!-- Cart (visible on larger screens) -->
      <div class="flex hidden justify-end sm:flex space-x-2 w-32">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
  <path d="M2.25 2.25a.75.75 0 0 0 0 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a3.752 3.752 0 0 0-2.806 3.63c0 .414.336.75.75.75h15.75a.75.75 0 0 0 0-1.5H5.378A2.25 2.25 0 0 1 7.5 15h11.218a.75.75 0 0 0 .674-.421 60.358 60.358 0 0 0 2.96-7.228.75.75 0 0 0-.525-.965A60.864 60.864 0 0 0 5.68 4.509l-.232-.867A1.875 1.875 0 0 0 3.636 2.25H2.25ZM3.75 20.25a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0ZM16.5 20.25a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Z" />
</svg>

        <span>Cart</span>
      </div>
      <!-- Mobile Menu Button -->
      <button id="menu-toggle" class="sm:hidden flex items-center">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>
    </div>
  </div>

  <!-- Navbar (visible on larger screens) -->
  <nav class="bg-gray-700 text-white hidden sm:flex">
    <div class="px-4 py-2 flex w-full items-center">
      <!-- Nav Links (aligned to the left) -->
      <div class="flex-1 flex space-x-4">
        <a href="/test" class="{{ request()->is('test') ? 'text-red-500' : 'text-white' }} hover:text-gray-400">Home</a>
        <a href="/watch-products" class="{{ request()->is('watch-products') ? 'text-red-500' : 'text-white' }} hover:text-gray-400">Watches</a>
        <a href="/sell-your-watches" class="{{ request()->is('sell-your-watches') ? 'text-red-500' : 'text-white' }} hover:text-gray-400">Sell Watch</a>
        <a href="/contact-us" class="{{ request()->is('contact-us') ? 'text-red-500' : 'text-white' }} hover:text-gray-400">Contact Us</a>
        <a href="/about-us" class="{{ request()->is('about-us') ? 'text-red-500' : 'text-white' }} hover:text-gray-400">About Us</a>
      </div>
      @if (!request()->is('test'))
      <!-- Search Bar (aligned to the right) -->
      
        <div class="flex items-center border rounded-lg overflow-hidden">
            <input type="text" wire:ignore wire:model.lazy="search" placeholder="Search..." class="w-full p-2 md:w-[40rem] max-w-xs text-gray-700 outline-none">
            <button class="bg-gray-800 text-white p-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
        </div>
            <!-- <input type="text" wire:ignore wire:model.lazy="search" placeholder="Search..." class="w-full md:w-[40rem] max-w-xs p-2 rounded-lg text-gray-700 product-search"> -->

      @endif
    </div>
  </nav>

  <!-- Mobile Menu -->
  <div id="mobile-menu" class="hidden sm:hidden w-full mt-2 bg-gray-700 text-white">
    <div class="flex flex-col items-start px-4 py-2">
      <a href="/test"class="w-full text-left py-2 hover:bg-gray-600">Home</a>
      <a href="/watch-products" class="w-full text-left py-2 hover:bg-gray-600">Watches</a>
      <a href="/sell-your-watches" class="w-full text-left py-2 hover:bg-gray-600">Sell Watch</a>
      <a href="/contact-us" class="w-full text-left py-2 hover:bg-gray-600">Contact Us</a>
      <a href="/about-us" class="w-full text-left py-2 hover:bg-gray-600">About Us</a>
    </div>
  </div>


  <!-- Mobile Search Bar -->
  <div id="mobile-search" class="sm:hidden w-full px-4 py-2">
    <input type="text" wire:ignore wire:model.lazy="search" placeholder="Search..." class="w-full p-2 rounded-lg text-gray-700 product-search">
  </div>

</header>

<!-- JavaScript for Mobile Menu Toggle -->
<script>
    document.getElementById('menu-toggle').addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        const search = document.getElementById('mobile-search');
        menu.classList.toggle('hidden');
        search.classList.toggle('hidden');
  });

</script>
