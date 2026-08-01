const heroShape = document.querySelector('.hero-shape');
const reduceMotion = window.matchMedia(
    '(prefers-reduced-motion: reduce)'
);

if (heroShape && !reduceMotion.matches) {
    const shapes = [
        'polygon(0 0, 33.33% 0, 66.67% 0, 100% 0, 100% 33.33%, 100% 66.67%, 100% 100%, 66.67% 100%, 33.33% 100%, 0 100%, 0 66.67%, 0 33.33%)',
        'polygon(50% 0, 66.67% 16.67%, 83.33% 33.33%, 100% 50%, 83.33% 66.67%, 66.67% 83.33%, 50% 100%, 33.33% 83.33%, 16.67% 66.67%, 0 50%, 16.67% 33.33%, 33.33% 16.67%)',
        'polygon(50% 2%, 62% 29%, 91.6% 26%, 74% 50%, 91.6% 74%, 62% 71%, 50% 98%, 38% 71%, 8.4% 74%, 26% 50%, 8.4% 26%, 38% 29%)',
        'polygon(50% 2%, 74% 8.4%, 91.6% 26%, 98% 50%, 91.6% 74%, 74% 91.6%, 50% 98%, 26% 91.6%, 8.4% 74%, 2% 50%, 8.4% 26%, 26% 8.4%)',
        'polygon(50% 2%, 62.5% 26.5%, 75% 51%, 87.5% 75.5%, 100% 100%, 75% 100%, 50% 100%, 25% 100%, 0 100%, 12.5% 75.5%, 25% 51%, 37.5% 26.5%)',
        'polygon(25% 0, 41.67% 0, 58.33% 0, 75% 0, 83.33% 33.33%, 91.67% 66.67%, 100% 100%, 66.67% 100%, 33.33% 100%, 0 100%, 8.33% 66.67%, 16.67% 33.33%)',
        'polygon(25% 0, 50% 0, 75% 0, 100% 0, 91.67% 33.33%, 83.33% 66.67%, 75% 100%, 50% 100%, 25% 100%, 0 100%, 8.33% 66.67%, 16.67% 33.33%)'
    ];

    let currentShape = shapes[0];
    let shapeQueue = [];

    const refillQueue = () => {
        shapeQueue = shapes.filter((shape) => shape !== currentShape);

        for (let index = shapeQueue.length - 1; index > 0; index -= 1) {
            const randomIndex = Math.floor(Math.random() * (index + 1));
            [shapeQueue[index], shapeQueue[randomIndex]] = [
                shapeQueue[randomIndex],
                shapeQueue[index]
            ];
        }
    };

    const morphToNextShape = () => {
        if (shapeQueue.length === 0) {
            refillQueue();
        }

        const nextShape = shapeQueue.shift();
        const animation = heroShape.animate(
            [
                { clipPath: currentShape },
                { clipPath: nextShape }
            ],
            {
                duration: 4000,
                easing: 'cubic-bezier(0.45, 0, 0.55, 1)',
                fill: 'forwards'
            }
        );

        animation.addEventListener('finish', () => {
            heroShape.style.clipPath = nextShape;
            animation.cancel();
            currentShape = nextShape;
            morphToNextShape();
        }, { once: true });
    };

    heroShape.classList.add('is-randomized');
    heroShape.style.clipPath = currentShape;
    morphToNextShape();
}
