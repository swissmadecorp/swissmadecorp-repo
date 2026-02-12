<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Gallery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Custom scrollbar hiding for clean thumbnail look */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Smooth fade transition */
        .fade-enter {
            opacity: 0;
            transform: scale(0.95);
        }
        .fade-enter-active {
            opacity: 1;
            transform: scale(1);
            transition: opacity 300ms ease-out, transform 300ms ease-out;
        }
        .fade-exit {
            opacity: 1;
            transform: scale(1);
        }
        .fade-exit-active {
            opacity: 0;
            transform: scale(0.95);
            transition: opacity 200ms ease-in, transform 200ms ease-in;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">

    <div class="max-w-2xl w-full bg-white p-6 rounded-xl shadow-lg">

        <div class="relative w-full h-96 group mb-4">
            <img id="mainImage"
                 src=""
                 alt="Product Main"
                 class="w-full h-full object-contain cursor-zoom-in rounded-lg bg-gray-100 transition-transform duration-300"
                 onclick="openModal()">

            <button onclick="changeImage(-1)" class="absolute top-1/2 left-4 -translate-y-1/2 bg-black/30 hover:bg-black/50 text-white rounded-full p-3 opacity-0 group-hover:opacity-100 transition-opacity">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button onclick="changeImage(1)" class="absolute top-1/2 right-4 -translate-y-1/2 bg-black/30 hover:bg-black/50 text-white rounded-full p-3 opacity-0 group-hover:opacity-100 transition-opacity">
                <i class="fa-solid fa-chevron-right"></i>
            </button>

            <div class="absolute bottom-4 right-4 bg-white p-2 rounded-full shadow-md pointer-events-none">
                <i class="fa-solid fa-magnifying-glass text-gray-600"></i>
            </div>
        </div>

        <div class="relative px-8"> <button onclick="scrollThumbnails(-1)" class="absolute left-0 top-0 bottom-0 w-8 bg-gray-200 hover:bg-gray-300 flex items-center justify-center rounded-l-md z-10">
                <i class="fa-solid fa-arrow-left text-gray-600"></i>
            </button>

            <div id="thumbnailContainer" class="flex gap-2 overflow-x-auto no-scrollbar scroll-smooth">
                </div>

            <button onclick="scrollThumbnails(1)" class="absolute right-0 top-0 bottom-0 w-8 bg-gray-200 hover:bg-gray-300 flex items-center justify-center rounded-r-md z-10">
                <i class="fa-solid fa-arrow-right text-gray-600"></i>
            </button>
        </div>
    </div>

    <div id="modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-md" onclick="closeModal()"></div>

        <div class="relative w-full h-full flex items-center justify-center pointer-events-none">

            <div id="modalContent" class="pointer-events-auto relative bg-white p-2 rounded-lg shadow-2xl transition-all duration-300 transform scale-95 opacity-0 max-w-[80vw] max-h-[80vh] md:w-[50vw]">

                <button onclick="closeModal()" class="absolute -top-12 right-0 text-white hover:text-gray-300 text-2xl transition-colors">
                    <i class="fa-solid fa-xmark"></i> Close
                </button>

                <img id="expandedImage" src="" class="w-full h-auto max-h-[70vh] object-contain rounded">

                <button onclick="changeImage(-1)" class="absolute top-1/2 -left-16 -translate-y-1/2 bg-white/10 hover:bg-white/20 text-white rounded-full w-12 h-12 flex items-center justify-center border border-white/30 backdrop-blur-sm transition-all">
                    <i class="fa-solid fa-chevron-left text-xl"></i>
                </button>
                <button onclick="changeImage(1)" class="absolute top-1/2 -right-16 -translate-y-1/2 bg-white/10 hover:bg-white/20 text-white rounded-full w-12 h-12 flex items-center justify-center border border-white/30 backdrop-blur-sm transition-all">
                    <i class="fa-solid fa-chevron-right text-xl"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        // --- CONFIGURATION ---
        // Replace these URLs with your actual images
        const images = [
            "https://images.unsplash.com/photo-1523170335258-f5ed11844a49?q=80&w=2080&auto=format&fit=crop", // Watch Front
            "https://images.unsplash.com/photo-1524592094714-0f0654e20314?q=80&w=1999&auto=format&fit=crop", // Watch Side
            "https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?q=80&w=1894&auto=format&fit=crop", // Watch Strap
            "https://images.unsplash.com/photo-1542496658-e33a6d0d50f6?q=80&w=2070&auto=format&fit=crop", // Detail
            "https://images.unsplash.com/photo-1614164185128-e4ec99c436d7?q=80&w=1974&auto=format&fit=crop", // Back
        ];

        let currentIndex = 0;
        const mainImage = document.getElementById('mainImage');
        const expandedImage = document.getElementById('expandedImage');
        const thumbnailContainer = document.getElementById('thumbnailContainer');
        const modal = document.getElementById('modal');
        const modalContent = document.getElementById('modalContent');

        // --- INITIALIZATION ---
        function init() {
            renderThumbnails();
            updateDisplay();
        }

        // --- RENDER THUMBNAILS ---
        function renderThumbnails() {
            thumbnailContainer.innerHTML = images.map((img, index) => `
                <div class="flex-shrink-0 cursor-pointer border-2 rounded-md overflow-hidden h-20 w-20 transition-all duration-200 ${index === currentIndex ? 'border-blue-500 opacity-100' : 'border-transparent opacity-60 hover:opacity-100'}"
                     onclick="setIndex(${index})">
                    <img src="${img}" class="w-full h-full object-cover">
                </div>
            `).join('');
        }

        // --- CORE LOGIC ---
        function updateDisplay() {
            // Update Main Images
            mainImage.src = images[currentIndex];
            expandedImage.src = images[currentIndex];

            // Re-render thumbnails to update active border state
            renderThumbnails();

            // Scroll thumbnail into view
            const activeThumb = thumbnailContainer.children[currentIndex];
            if(activeThumb) {
                activeThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }
        }

        function setIndex(index) {
            currentIndex = index;
            updateDisplay();
        }

        function changeImage(direction) {
            currentIndex += direction;
            if (currentIndex < 0) currentIndex = images.length - 1;
            if (currentIndex >= images.length) currentIndex = 0;
            updateDisplay();
        }

        // --- SCROLL THUMBNAILS (Grey Bars) ---
        function scrollThumbnails(direction) {
            const scrollAmount = 100;
            thumbnailContainer.scrollBy({
                left: direction * scrollAmount,
                behavior: 'smooth'
            });
        }

        // --- MODAL FUNCTIONS ---
        function openModal() {
            modal.classList.remove('hidden');
            // Small delay to allow display:block to apply before adding opacity class for animation
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeModal() {
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');

            // Wait for animation to finish before hiding
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
            if (!modal.classList.contains('hidden')) {
                if (e.key === 'ArrowLeft') changeImage(-1);
                if (e.key === 'ArrowRight') changeImage(1);
            }
        });

        // Run Logic
        init();
    </script>
</body>
</html>