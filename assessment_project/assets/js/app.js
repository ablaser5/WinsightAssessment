const preview = document.querySelectorAll('.preview').forEach(element => {
    element.addEventListener('click', (event) => {
        window.location.href = element.getAttribute("article-uri");
    });
});

document.querySelector('.menu-icon').addEventListener('click', function() {
    const menu = document.getElementById('menu');
    if (menu.style.display === 'none') {
        menu.style.display = 'block';
    } else {
        menu.style.display = 'none';
    }
});