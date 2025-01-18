let slideIndex = 1;
const slides = document.querySelector('.slides');
const totalSlides = slides.children.length;

const firstClone = slides.children[0].cloneNode(true);
const lastClone = slides.children[totalSlides - 1].cloneNode(true);

slides.appendChild(firstClone); 
slides.insertBefore(lastClone, slides.children[0]);

slides.style.transform = `translateX(-${slideIndex * 100}%)`;

function showSlides() {
    slides.style.transition = "transform 1.3s ease-in-out";
    slideIndex++;
    slides.style.transform = `translateX(-${slideIndex * 100}%)`;

    if (slideIndex >= totalSlides + 1) {
        setTimeout(() => {
            slides.style.transition = "none"; 
            slideIndex = 1;
            slides.style.transform = `translateX(-${slideIndex * 100}%)`;
        }, 1300);
    }

    setTimeout(showSlides, 4000);
}

showSlides();