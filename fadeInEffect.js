document.addEventListener("DOMContentLoaded", () => {
    const fadeInElements = document.querySelectorAll(".fade-in");

    let isScrolling = false;

    const handleScroll = () => {
        fadeInElements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const viewportHeight = window.innerHeight;

            if (elementPosition < viewportHeight - 100) {
                element.classList.add("visible");
            }
        });
        isScrolling = false;
    };

    const throttleScroll = () => {
        if (!isScrolling) {
            isScrolling = true;
            requestAnimationFrame(handleScroll);
        }
    };

    window.addEventListener("scroll", throttleScroll);

    // Initial check for elements already in view
    handleScroll();
});
