// Select hamburger menu icon (mobile navigation button)
const menuIcon = document.querySelector(".display-mobile.fa-bars");

// Select navigation list inside navbar
const navList = document.querySelector("nav > div.container > ul");

// Toggle navigation visibility on click
menuIcon.addEventListener("click", () => {

  // Check current display state
  if (navList.style.display === "block") {
    navList.style.display = "none"; // Hide menu
  } else {
    navList.style.display = "block"; // Show menu
  }
});