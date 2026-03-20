const searchInput = document.getElementById("searchInput");
const cards = document.querySelectorAll(".card");
const contactForm = document.querySelector(".contact-form");

searchInput.addEventListener("input", (e) => {
  const value = e.target.value.toLowerCase();

  cards.forEach((card) => {
    const destination = card.dataset.name.toLowerCase();
    if (destination.includes(value)) {
      card.classList.remove("hidden");
    } else {
      card.classList.add("hidden");
    }
  });
});

contactForm.addEventListener("submit", (e) => {
  e.preventDefault();
  alert("Message envoyé avec succès !");
  contactForm.reset();
});
