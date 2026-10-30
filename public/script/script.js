(function () {
  var slides = [].slice.call(document.querySelectorAll(".slide"));
  if (slides.length < 2) return;
  var box = document.getElementById("slides"),
    dots = document.getElementById("dots"),
    pause = document.getElementById("pause");
  var i = 0,
    timer = null,
    paused = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  slides.forEach(function (_, n) {
    var b = document.createElement("button");
    b.type = "button";
    b.textContent = n + 1;
    b.setAttribute("aria-label", "Aller à la publication " + (n + 1));
    b.onclick = function () {
      show(n);
    };
    dots.appendChild(b);
  });
  function show(n) {
    i = (n + slides.length) % slides.length;
    slides.forEach(function (s, k) {
      s.hidden = k !== i;
    });
    [].forEach.call(dots.children, function (d, k) {
      d.setAttribute("aria-current", k === i);
    });
  }
  function run() {
    stop();
    if (!paused)
      timer = setInterval(function () {
        show(i + 1);
      }, 6000);
  }
  function stop() {
    clearInterval(timer);
  }
  function state() {
    pause.textContent = paused ? "Lecture" : "Pause";
    pause.setAttribute(
      "aria-label",
      paused ? "Relancer le diaporama" : "Mettre le diaporama en pause",
    );
    box.setAttribute("aria-live", paused ? "polite" : "off");
  }
  pause.onclick = function () {
    paused = !paused;
    state();
    run();
  };
  document.getElementById("prev").onclick = function () {
    show(i - 1);
  };
  document.getElementById("next").onclick = function () {
    show(i + 1);
  };
  var r = document.querySelector(".pubs");
  r.addEventListener("mouseenter", stop);
  r.addEventListener("mouseleave", run);
  r.addEventListener("focusin", stop);
  r.addEventListener("focusout", run);
  show(0);
  state();
  run();
})();
