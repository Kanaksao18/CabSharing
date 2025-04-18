function animateCounter(element, start, end, duration) {
  let startTimestamp = null;
  const step = (timestamp) => {
    if (!startTimestamp) startTimestamp = timestamp;
    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
    const currentValue = Math.floor(progress * (end - start) + start);
    element.textContent = currentValue.toLocaleString() + "+";
    if (progress < 1) {
      window.requestAnimationFrame(step);
    }
  };
  window.requestAnimationFrame(step);
}

document.addEventListener("DOMContentLoaded", function () {
  const ridesCounter = document.getElementById("rides-counter");
  const usersCounter = document.getElementById("users-counter");
  const kilometersCounter = document.getElementById("kilometers-counter");

  if (ridesCounter) animateCounter(ridesCounter, 0, 10000, 2000);
  if (usersCounter) animateCounter(usersCounter, 0, 5000, 2000);
  if (kilometersCounter) animateCounter(kilometersCounter, 0, 50000, 2000);
});
