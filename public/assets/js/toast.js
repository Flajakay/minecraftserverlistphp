/**
 * Toast Notification Controller
 * Manages custom autohide countdown, progress bar, and pause-on-hover logic.
 */
document.addEventListener('DOMContentLoaded', function () {
    const toastElement = document.getElementById('notificationToast');
    if (toastElement) {
        // Initialize the Bootstrap Toast with autohide: false to bypass default delays
        const toast = new bootstrap.Toast(toastElement, { autohide: false });
        toast.show();
        
        const progressBar = toastElement.querySelector('.toast-progress-bar');
        const totalDuration = 5000; // 5000ms = 5 seconds
        let timeRemaining = totalDuration;
        let timerId = null;
        let startTime = null;

        // Starts or resumes the progress animation and hide timeout
        function startTimer() {
            startTime = Date.now();
            
            // Animate progress bar width shrinking down to 0%
            if (progressBar) {
                progressBar.style.transition = `width ${timeRemaining}ms linear`;
                progressBar.style.width = '0%';
            }
            
            // Trigger the actual hide event exactly at the end of the duration
            timerId = setTimeout(function () {
                toast.hide();
            }, timeRemaining);
        }

        // Freezes the progress bar and clears the scheduled hide timer
        function pauseTimer() {
            clearTimeout(timerId);
            
            if (progressBar) {
                const elapsed = Date.now() - startTime;
                timeRemaining = Math.max(0, timeRemaining - elapsed);
                
                const currentWidthPercent = (timeRemaining / totalDuration) * 100;
                progressBar.style.transition = 'none';
                progressBar.style.width = `${currentWidthPercent}%`;
            }
        }

        // Trigger initial start after entry slide animation finishes (200ms)
        setTimeout(startTimer, 200);

        // Setup interactive pause on hover triggers
        toastElement.addEventListener('mouseenter', pauseTimer);
        toastElement.addEventListener('mouseleave', startTimer);
    }
});
