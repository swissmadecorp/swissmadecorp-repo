<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfect Alignment Gallery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Ensuring touches on mobile don't drag the screen */
        .no-tap-highlight {
            -webkit-tap-highlight-color: transparent;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-3xl w-full bg-white p-4 md:p-6 rounded-2xl shadow-xl z-0">

        <div class="relative w-full h-64 md:h-96 group mb-6 overflow-hidden rounded-xl bg-gray-100 border border-gray-100">

            <div id="mainCarouselTrack" class="flex h-full w-full transition-transform duration-500 ease-in-out cursor-zoom-in">
                </div>

            <button onclick="changeImage(-1, event)" class="absolute top-1/2 left-4 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 rounded-full w-10 h-10 flex items-center justify-center shadow-md transition-all duration-300 z-10 opacity-100 md:opacity-0 md:group-hover:opacity-100 md:hover:scale-110 no-tap-highlight">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button onclick="changeImage(1, event)" class="absolute top-1/2 right-4 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 rounded-full w-10 h-10 flex items-center justify-center shadow-md transition-all duration-300 z-10 opacity-100 md:opacity-0 md:group-hover:opacity-100 md:hover:scale-110 no-tap-highlight">
                <i class="fa-solid fa-chevron-right"></i>
            </button>

            <div class="absolute bottom-3 right-3 bg-white/90 backdrop-blur px-3 py-1.5 rounded-full shadow-sm text-xs font-medium text-gray-600 pointer-events-none flex items-center gap-2 z-10">
                <i class="fa-solid fa-expand"></i> Click to Expand
            </div>
        </div>

        <div class="relative px-10 md:px-12">

            <button onclick="scrollThumbnails(-1)" class="absolute left-0 top-0 bottom-0 w-8 md:w-10 bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-l-lg flex items-center justify-center transition-colors z-10 no-tap-highlight">
                <i class="fa-solid fa-chevron-left text-sm"></i>
            </button>

            <div id="thumbnailContainer" class="flex gap-3 overflow-x-auto scroll-smooth [&::-webkit-scrollbar]:hidden [-ms-overflow-style:'none'] [scrollbar-width:'none'] py-1">
                </div>

            <button onclick="scrollThumbnails(1)" class="absolute right-0 top-0 bottom-0 w-8 md:w-10 bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-r-lg flex items-center justify-center transition-colors z-10 no-tap-highlight">
                <i class="fa-solid fa-chevron-right text-sm"></i>
            </button>
        </div>
    </div>

    <div id="modal" class="fixed inset-0 z-50 invisible opacity-0 transition-all duration-300 ease-out">

        <div class="absolute inset-0 bg-black/80 backdrop-blur-md" onclick="closeModal()"></div>

        <button onclick="closeModal()" class="absolute top-6 right-6 z-[70] text-white/70 hover:text-white transition-colors p-2 group no-tap-highlight">
            <div class="flex flex-col items-center">
                <i class="fa-solid fa-xmark text-4xl group-hover:scale-110 transition-transform"></i>
                <span class="text-xs font-light mt-1">CLOSE</span>
            </div>
        </button>

        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">

            <div id="modalWrapper" class="pointer-events-auto relative w-full md:w-[80vw] h-[60vh] md:h-[80vh] overflow-hidden transform scale-95 transition-all duration-300 ease-out">

                <div id="modalCarouselTrack" class="flex h-full w-full transition-transform duration-500 ease-in-out items-center">
                    </div>

            </div>

            <button onclick="changeImage(-1, event)" class="pointer-events-auto absolute left-2 md:left-8 text-white/60 hover:text-white transition-transform hover:scale-110 p-4 z-[60] no-tap-highlight">
                <i class="fa-solid fa-chevron-left text-4xl md:text-6xl drop-shadow-xl"></i>
            </button>
            <button onclick="changeImage(1, event)" class="pointer-events-auto absolute right-2 md:right-8 text-white/60 hover:text-white transition-transform hover:scale-110 p-4 z-[60] no-tap-highlight">
                <i class="fa-solid fa-chevron-right text-4xl md:text-6xl drop-shadow-xl"></i>
            </button>
        </div>
    </div>

    <script>
        const images = [
            "https://images.unsplash.com/photo-1523170335258-f5ed11844a49?q=80&w=2080&auto=format&fit=crop",
            "https://images.unsplash.com/photo-1524592094714-0f0654e20314?q=80&w=1999&auto=format&fit=crop",
            "https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?q=80&w=1894&auto=format&fit=crop",
            "https://images.unsplash.com/photo-1542496658-e33a6d0d50f6?q=80&w=2070&auto=format&fit=crop",
            "https://images.unsplash.com/photo-1614164185128-e4ec99c436d7?q=80&w=1974&auto=format&fit=crop",
        ];

        let currentIndex = 0;

        // DOM Elements
        const mainCarouselTrack = document.getElementById('mainCarouselTrack');
        const modalCarouselTrack = document.getElementById('modalCarouselTrack');
        const thumbnailContainer = document.getElementById('thumbnailContainer');
        const modal = document.getElementById('modal');
        const modalWrapper = document.getElementById('modalWrapper');

        function init() {
            // 1. Render MAIN Carousel Slides
            // Fix: Using 'w-full' and 'flex-shrink-0' to enforce strict 100% width
            mainCarouselTrack.innerHTML = images.map(img => `
                <div class="w-full flex-shrink-0 h-full flex items-center justify-center bg-gray-50" onclick="openModal()">
                    <img src="${img}" class="h-full w-full object-contain pointer-events-none select-none">
                </div>
            `).join('');

            // 2. Render MODAL Carousel Slides
            // Fix: Using 'w-full' and 'flex-shrink-0' here too
            modalCarouselTrack.innerHTML = images.map(img => `
                <div class="w-full flex-shrink-0 h-full flex items-center justify-center p-2 md:p-4">
                    <img src="${img}" class="max-w-full max-h-full object-contain drop-shadow-2xl select-none">
                </div>
            `).join('');

            // 3. Render Thumbnails
            thumbnailContainer.innerHTML = images.map((img, index) => `
                <div id="thumb-${index}"
                     class="relative flex-shrink-0 w-20 h-20 md:w-24 md:h-24 cursor-pointer rounded-lg overflow-hidden border-2 transition-all duration-300 hover:opacity-100 ${index === 0 ? 'border-blue-600 opacity-100 ring-2 ring-blue-100' : 'border-transparent opacity-60'}"
                     onclick="setIndex(${index})">
                    <img src="${img}" class="w-full h-full object-cover">
                </div>
            `).join('');

            updateDisplay();
        }

        function updateDisplay() {
            // 1. Slide MAIN Carousel
            // Translate by -100% * index ensures we move exactly one viewport width
            mainCarouselTrack.style.transform = `translateX(-${currentIndex * 100}%)`;

            // 2. Slide MODAL Carousel
            modalCarouselTrack.style.transform = `translateX(-${currentIndex * 100}%)`;

            // 3. Update Thumbnails
            images.forEach((_, idx) => {
                const thumb = document.getElementById(`thumb-${idx}`);
                if (idx === currentIndex) {
                    thumb.classList.remove('border-transparent', 'opacity-60');
                    thumb.classList.add('border-blue-600', 'opacity-100', 'ring-2', 'ring-blue-100');
                    thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                } else {
                    thumb.classList.add('border-transparent', 'opacity-60');
                    thumb.classList.remove('border-blue-600', 'opacity-100', 'ring-2', 'ring-blue-100');
                }
            });
        }

        function setIndex(index) {
            if (index === currentIndex) return;
            currentIndex = index;
            updateDisplay();
        }

        function changeImage(direction, event) {
            // Prevent the click from bubbling up to the image (which would trigger openModal)
            if(event) event.stopPropagation();

            currentIndex += direction;
            if (currentIndex < 0) currentIndex = images.length - 1;
            if (currentIndex >= images.length) currentIndex = 0;
            updateDisplay();
        }

        function scrollThumbnails(direction) {
            thumbnailContainer.scrollBy({ left: direction * 150, behavior: 'smooth' });
        }

        function openModal() {
            modal.classList.remove('invisible', 'opacity-0');
            modal.classList.add('visible', 'opacity-100');

            setTimeout(() => {
                modalWrapper.classList.remove('scale-95');
                modalWrapper.classList.add('scale-100');
            }, 10);
        }

        function closeModal() {
            modalWrapper.classList.remove('scale-100');
            modalWrapper.classList.add('scale-95');

            modal.classList.remove('visible', 'opacity-100');
            modal.classList.add('invisible', 'opacity-0');
        }

        // Keyboard Support
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
            if (e.key === 'ArrowLeft') changeImage(-1);
            if (e.key === 'ArrowRight') changeImage(1);
        });

        init();
    </script>
</body>
</html>