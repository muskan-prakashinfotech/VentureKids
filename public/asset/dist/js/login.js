
// text slider
var swiper = new Swiper(".textswiper", {
    direction: "vertical",
    loop: true,
    slidesPerView: "1",
    autoplay: {
        delay: 3000,
        disableOnInteraction: false
    },
    speed: 1000,
    pagination: {
        el: ".swiper.textswiper .swiper-pagination",
        clickable: true,
    },
});

// card slider
var swiper = new Swiper('.imgslider', {
    autoplay: {
        delay: 3000,
        disableOnInteraction: false
    },
    speed: 1000,
    spaceBetween: 20,
    effect: "coverflow",
    grabCursor: true,
    centeredSlides: true,
    slidesPerView: "auto",
    coverflowEffect: {
        rotate: 0,
        stretch: 0,
        depth: 100,
        modifier: 2,
        slideShadows: false
    },
    loop: true,
    pagination: {
        el: '.swiper.imgslider .swiper-pagination',
        clickable: true
    },
    breakpoints: {
        640: {
            slidesPerView: 1,
        },
        768: {
            slidesPerView: 2.8,
        },
        1024: {
            slidesPerView: 2.8,
        },
    },
});