window.addEventListener('DOMContentLoaded', () => {
    const flashFeedback = document.getElementById('flash-message');
    
    if (flashFeedback && flashFeedback.textContent.trim() !== '') {
        
        flashFeedback.focus();
        
        flashFeedback.style.outline = "none";
    }
});