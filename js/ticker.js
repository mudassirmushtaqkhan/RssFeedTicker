


document.addEventListener("DOMContentLoaded", function() {
    const items = document.querySelectorAll("#rss-feed-ticker .ticker-item");
    const speed = parseInt(document.getElementById("rss-feed-ticker").dataset.speed) || 3000;
    let currentIndex = 0;

    function showItem(index) {
        items.forEach(item => item.classList.remove("active"));
        items[index].classList.add("active");
    }

    function nextItem() {
        currentIndex = (currentIndex + 1) % items.length;
        showItem(currentIndex);
    }

    function prevItem() {
        currentIndex = (currentIndex - 1 + items.length) % items.length;
        showItem(currentIndex);
    }

    document.querySelector(".ticker-nav.next").addEventListener("click", nextItem);
    document.querySelector(".ticker-nav.prev").addEventListener("click", prevItem);

    showItem(currentIndex);
    setInterval(nextItem, speed);
});

// setting display c

// marquuee effect 

