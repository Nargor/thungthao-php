class Lightbox {
    constructor() {
        this.init();
    }

    init() {
        // Create lightbox container
        this.lightbox = document.createElement('div');
        this.lightbox.id = 'lightbox';
        this.lightbox.className = 'fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center';
        
        // Create close button
        this.closeBtn = document.createElement('button');
        this.closeBtn.className = 'absolute top-4 right-4 text-white hover:text-[#61dafb] z-50';
        this.closeBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        `;
        this.closeBtn.onclick = () => this.close();
        
        // Create content container
        this.content = document.createElement('div');
        this.content.className = 'max-w-[90vw] max-h-[90vh] relative';
        
        // Add elements to DOM
        this.lightbox.appendChild(this.closeBtn);
        this.lightbox.appendChild(this.content);
        document.body.appendChild(this.lightbox);
        
        // Close on background click
        this.lightbox.addEventListener('click', (e) => {
            if (e.target === this.lightbox) {
                this.close();
            }
        });

        // Initialize click handlers
        this.initImageHandlers();
        this.initVideoHandlers();
    }

    initImageHandlers() {
        document.querySelectorAll('.lightbox-image').forEach(img => {
            img.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.showImage(img.src);
            });
        });
    }

    initVideoHandlers() {
        document.querySelectorAll('.lightbox-video').forEach(video => {
            video.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const videoSrc = video.querySelector('source').src;
                this.showVideo(videoSrc);
            });
        });
    }

    showImage(src) {
        this.content.innerHTML = `
            <img src="${src}" class="max-w-full max-h-[90vh] object-contain" alt="Lightbox image">
        `;
        this.show();
    }

    showVideo(src) {
        this.content.innerHTML = `
            <video class="max-w-full max-h-[90vh]" controls autoplay>
                <source src="${src}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        `;
        this.show();
    }

    show() {
        this.lightbox.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    close() {
        this.lightbox.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        this.content.innerHTML = '';
    }
}

// Initialize lightbox
document.addEventListener('DOMContentLoaded', () => {
    window.lightbox = new Lightbox();
});
