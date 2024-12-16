function date() {
    const options = {
        weekday: "long",
        year: "numeric",
        month: "numeric",
        day: "numeric",
      };

    const date = new Date();
    const time = new Date();

    document.getElementById("date").innerHTML = date.toLocaleDateString("pl-PL", options);
    document.getElementById("time").innerHTML = time.toLocaleTimeString();
}

window.onload = date;
setInterval(date, 1000);
date();