<div id="lightbox" class="hidden fixed inset-0 z-[60] bg-black/90 p-4 sm:p-6" onclick="closeLightbox()">
    <div class="mx-auto flex h-full max-w-6xl flex-col" onclick="event.stopPropagation()">
        <div class="mb-3 flex items-center justify-end gap-2">
            <button type="button" onclick="closeLightbox()" class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-white/10 text-white hover:bg-white/20 transition-colors" title="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex flex-1 min-h-0 items-center justify-center">
            <img id="lightbox-img" class="max-h-[72vh] max-w-full rounded-2xl object-contain shadow-2xl" src="" alt="Attachment preview" referrerPolicy="no-referrer">
        </div>
        <div class="mt-4 flex justify-center pb-2">
            <a id="lightbox-download" href="#" download class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-natural-primary text-xs font-bold rounded-xl border border-natural-border shadow-sm hover:bg-natural-subtle transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Download Image
            </a>
        </div>
    </div>
</div>
