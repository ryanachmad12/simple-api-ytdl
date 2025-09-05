document.addEventListener('DOMContentLoaded', () => {
    const downloadForm = document.getElementById('download-form');
    const urlInput = document.getElementById('youtube-url');
    const processBtn = document.getElementById('process-btn');
    
    const resultContainer = document.getElementById('result-container');
    const statusText = document.getElementById('status-text');
    const progressBarInner = document.querySelector('.progress-bar-inner');
    const videoInfo = document.getElementById('video-info');
    const videoThumbnail = document.getElementById('video-thumbnail');
    const videoTitle = document.getElementById('video-title');
    const finalDownloadLink = document.getElementById('final-download-link');

    downloadForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // --- UI Reset ---
        processBtn.disabled = true;
        resultContainer.classList.remove('hidden');
        videoInfo.classList.add('hidden');
        finalDownloadLink.classList.add('hidden');
        statusText.textContent = 'Mendapatkan info video...';
        progressBarInner.style.transition = 'none';
        progressBarInner.style.width = '5%';
        
        const url = urlInput.value;
        const format = document.querySelector('input[name="format"]:checked').value;

        try {
            // --- TAHAP 1: Panggil getInfo.php (Cepat) ---
            const infoResponse = await fetch(`getInfo.php?url=${encodeURIComponent(url)}`);
            const infoResult = await infoResponse.json();

            if (!infoResult.success) {
                throw new Error(infoResult.data.message);
            }

            // --- TAHAP 2: Tampilkan Info & Mulai Proses Download ---
            const { title, thumbnail } = infoResult.data;
            videoTitle.textContent = title;
            videoThumbnail.src = thumbnail;
            videoInfo.classList.remove('hidden');

            statusText.textContent = 'Info diterima. Mempersiapkan file unduhan...';
            progressBarInner.style.transition = 'width 25s cubic-bezier(0.4, 0, 0.2, 1)';
            progressBarInner.style.width = '85%';

            // Panggil prepareDownload.php (Lambat)
            const prepareResponse = await fetch(`prepareDownload.php?url=${encodeURIComponent(url)}&format=${format}&title=${encodeURIComponent(title)}`);
            const prepareResult = await prepareResponse.json();

            if (!prepareResult.success) {
                throw new Error(prepareResult.data.message);
            }

            // --- TAHAP 3: Proses Selesai, Picu Download ---
            statusText.textContent = 'File siap diunduh!';
            progressBarInner.style.transition = 'width 0.5s ease-out';
            progressBarInner.style.width = '100%';

            finalDownloadLink.href = prepareResult.data.download_url;
            finalDownloadLink.download = `${title}.${format}`;
            finalDownloadLink.classList.remove('hidden');
            finalDownloadLink.click();

        } catch (error) {
            statusText.textContent = `Error: ${error.message}`;
            progressBarInner.style.width = '0%';
        } finally {
            processBtn.disabled = false;
        }
    });
});
