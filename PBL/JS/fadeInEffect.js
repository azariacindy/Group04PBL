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

document.addEventListener("DOMContentLoaded", () => {
    const fadeInElements = document.querySelectorAll(".fade-in");

    const handleScroll = () => {
        fadeInElements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const viewportHeight = window.innerHeight;

            if (elementPosition < viewportHeight - 100) {
                element.classList.add("visible");
            }
        });
    };

    window.addEventListener("scroll", handleScroll);

    // Initial check for elements already in view
    handleScroll();

    // Slider logic
    const slides = document.querySelectorAll(".slide");
    const prevButton = document.querySelector(".prev");
    const nextButton = document.querySelector(".next");
    let currentSlide = 0;

    const updateSlides = () => {
        slides.forEach((slide, index) => {
            slide.style.display = index === currentSlide ? "block" : "none";
        });
    };

    prevButton.addEventListener("click", () => {
        currentSlide = (currentSlide === 0) ? slides.length - 1 : currentSlide - 1;
        updateSlides();
    });

    nextButton.addEventListener("click", () => {
        currentSlide = (currentSlide === slides.length - 1) ? 0 : currentSlide + 1;
        updateSlides();
    });

    // Initialize slider
    updateSlides();
});