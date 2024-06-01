$(document).ready(function () {
  let currentIndex = 0;
  const items = $(".carousel-item");
  const itemCount = items.length;

  function showItem(index, instant = false) {
    const offset = -index * 100;
    if (instant) {
      items.css("transition", "none");
    } else {
      items.css("transition", "transform 0.5s ease");
    }
    items.css("transform", `translateX(${offset}%)`);
    if (instant) {
      // Force reflow to apply the instant transition
      items[0].offsetHeight; // This line forces a reflow
      items.css("transition", "transform 0.5s ease");
    }
  }

  $(".next").click(function () {
    if (currentIndex === itemCount - 1) {
      showItem(0, true);
      currentIndex = 0;
    } else {
      currentIndex++;
      showItem(currentIndex);
    }
  });

  $(".prev").click(function () {
    if (currentIndex === 0) {
      showItem(itemCount - 1, true);
      currentIndex = itemCount - 1;
    } else {
      currentIndex--;
      showItem(currentIndex);
    }
  });

  showItem(currentIndex);

  // New code to handle event switching
  $("#btn-evenement").click(function () {
    $(".Evenement_de_la_semaine").addClass("active");
    $(".Bulletin").removeClass("active");
  });

  $("#btn-bulletin").click(function () {
    $(".Bulletin").addClass("active");
    $(".Evenement_de_la_semaine").removeClass("active");
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const navItems = document.querySelectorAll(".NAVIGATION > ul > li");

  navItems.forEach((item) => {
    item.addEventListener("mouseenter", function () {
      const submenu = this.querySelector(".submenu");
      if (submenu) {
        submenu.style.display = "block";
      }
    });

    item.addEventListener("mouseleave", function () {
      const submenu = this.querySelector(".submenu");
      if (submenu) {
        submenu.style.display = "none";
      }
    });
  });

  document.addEventListener("click", function (event) {
    if (!event.target.closest(".NAVIGATION")) {
      navItems.forEach((item) => {
        const submenu = item.querySelector(".submenu");
        if (submenu) {
          submenu.style.display = "none";
        }
      });
    }
  });
});
